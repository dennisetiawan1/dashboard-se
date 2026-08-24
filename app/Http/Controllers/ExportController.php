<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSnapshot;
use App\Models\PetugasReference;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Usaha;
use App\Models\UsahaUpload;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        $scope = $request->input('scope', 'current'); // current | all

        $filters = [
            'petugas_username' => $request->input('petugas_username'),
            'petugas_role' => $request->input('petugas_role'),
            'sls_code' => $request->input('sls_code'),
            'nama_kecamatan' => $request->input('nama_kecamatan'),
        ];

        if ($filters['nama_kecamatan']) {
            $filters['_usernames_in_kecamatan'] = PetugasReference::query()
                ->where('nama_kecamatan', $filters['nama_kecamatan'])
                ->pluck('petugas_username')
                ->all();
        }

        $selectedDate = $request->input('tanggal');
        $roleForTotals = $filters['petugas_role'] ?: 'pencacah';

        $query = AssignmentSnapshot::query()
            ->selectRaw('
                upload_date,
                petugas_username,
                MAX(kabupaten_code) as kabupaten_code,
                MAX(kabupaten_name) as kabupaten_name,

                SUM(sls_total_assignment) as total_assignment,
                SUM(status_open) as status_open,
                SUM(status_draft) as status_draft,
                SUM(status_submitted_pencacah) as status_submitted_pencacah,
                SUM(status_approved_pengawas) as status_approved_pengawas,
                SUM(status_rejected_pengawas) as status_rejected_pengawas,
                SUM(status_edited_pengawas) as status_edited_pengawas,
                SUM(status_revoked_pengawas) as status_revoked_pengawas,
                SUM(status_submitted_respondent) as status_submitted_respondent,
                SUM(status_completed_admin_kab) as status_completed_admin_kab,
                SUM(status_edited_admin_kab) as status_edited_admin_kab,
                SUM(status_rejected_admin_kab) as status_rejected_admin_kab,
                SUM(status_revoked_admin_kab) as status_revoked_admin_kab
            ')
            ->groupBy('upload_date', 'petugas_username')
            // Diurutkan per PETUGAS dulu, baru per tanggal -> histori 1 petugas
            // berderet rapi (bukan tercampur per tanggal).
            ->orderBy('petugas_username')
            ->orderBy('upload_date');

        if ($scope === 'current' && !$selectedDate) {
            $selectedDate = \App\Models\Upload::query()
                ->where('petugas_role', $roleForTotals)
                ->max('upload_date');
        }

        if ($scope === 'current' && $selectedDate) {
            $query->where('upload_date', $selectedDate);
        }

        $query
            ->when($filters['petugas_username'], fn($q, $v) => $q->where('petugas_username', $v))
            ->when($filters['petugas_role'], fn($q, $v) => $q->where('petugas_role', $v))
            ->when($filters['sls_code'], fn($q, $v) => $q->where('sls_code', 'like', '%' . $v . '%'))
            ->when($filters['nama_kecamatan'], function ($q) use ($filters) {
                $q->whereIn('petugas_username', $filters['_usernames_in_kecamatan'] ?? []);
            });

        $rows = $query->get();

        $referenceMap = PetugasReference::query()
            ->get(['petugas_username', 'nama_petugas', 'nama_kecamatan'])
            ->keyBy('petugas_username');

        $exportRows = $rows->map(function ($row) use ($referenceMap) {
            $ref = $referenceMap->get($row->petugas_username);

            $total = (int) $row->total_assignment;
            $open = (int) $row->status_open;
            $draft = (int) $row->status_draft;
            $submitted = (int) $row->status_submitted_pencacah;
            $approved = (int) $row->status_approved_pengawas;
            $rejected = (int) $row->status_rejected_pengawas;

            $progress = $total > 0
                ? round(($approved / $total) * 100, 1)
                : 0;

            $nonOpen = $total - $open;

            $pctNonOpen = $total > 0
                ? round(($nonOpen / $total) * 100)
                : 0;

            $nonOpenDraft = $total - $open - $draft;

            $pctSubmitPlus = $total > 0 ? round(($nonOpenDraft / $total) * 100) : 0;

            $approvedProgress = $total - $open - $draft - $submitted;

            $pctApproved = $total > 0
                ? round(($approvedProgress / $total) * 100)
                : 0;

            return [
                'tanggal_raw' => Carbon::parse($row->upload_date)->format('Y-m-d'),
                'tanggal' => Carbon::parse($row->upload_date)->format('d-m-Y'),
                'username' => $row->petugas_username,
                'nama' => $ref->nama_petugas ?? '-',
                'kecamatan' => $ref->nama_kecamatan ?? '-',

                'total_assignment' => $total,
                'open' => $open,
                'draft' => $draft,
                'submitted' => $submitted,
                'approved' => $approved,
                'rejected' => $rejected,

                'progress' => $progress,

                'pct_non_open' => $pctNonOpen,
                'pct_submit_plus' => $pctSubmitPlus,
                'pct_approved' => $pctApproved,
            ];
        });

        $role = strtolower($filters['petugas_role'] ?? '');

        if ($role === 'pencacah') {
            $prefix = 'pencacah';
        } elseif ($role === 'pengawas') {
            $prefix = 'pengawas';
        } else {
            $prefix = 'semua';
        }

        $filenameLabel = $scope === 'all'
            ? 'semua-tanggal'
            : ($selectedDate ? Carbon::parse($selectedDate)->format('Y-m-d') : 'data');

        $filenameBase = $prefix . '-progress-assignment-' . $filenameLabel;

        return $this->exportXlsx(
            $exportRows,
            $filenameBase,
            $scope,
            $selectedDate,
            $filters
        );
    }


    private function exportXlsx($rows, string $filenameBase, string $scope, ?string $selectedDate = null, array $filters = [])
    {
        $filename = $filenameBase . '.xlsx';

        $spreadsheet = new Spreadsheet();

        if ($selectedDate) {
            $this->addKecamatanRecapSheet($spreadsheet, $selectedDate, $filters, useActiveSheet: true);
        }

        if ($scope === 'all') {
            // Sheet ke-2: pivot per petugas, kolom = tanggal -> paling mudah dibaca untuk perbandingan
            $this->addPivotPerPetugasSheet($spreadsheet, $rows);
            // Sheet ke-3: rekap total semua petugas per tanggal (gambaran umum keseluruhan)
            $this->addSummaryPerTanggalSheet($spreadsheet, $rows);
        }

        $tempPath = storage_path('app/' . uniqid('export_') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Sheet pivot: 1 baris = 1 petugas, kolom-kolom berikutnya berulang per tanggal
     * (Total, Approved, Progress%) -> supaya kenaikan/penurunan progress tiap
     * petugas dari hari ke hari langsung kelihatan tanpa harus scroll-cari manual.
     */
    private function addPivotPerPetugasSheet(Spreadsheet $spreadsheet, $rows): void
    {
        $dates = $rows->pluck('tanggal_raw')->unique()->sort()->values();

        $petugas = $rows->groupBy('username')->map(function ($group) {
            $first = $group->first();

            return [
                'username' => $first['username'],
                'nama' => $first['nama'],
                'kecamatan' => $first['kecamatan'],
                'byDate' => $group->keyBy('tanggal_raw'),
            ];
        })->sortBy(fn($p) => $p['nama'] !== '-' ? $p['nama'] : $p['username'])->values();

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Rekap per Petugas');

        // Baris 1: header tanggal (digabung 3 kolom per tanggal), Baris 2: sub-header metrik
        $sheet->setCellValue('A1', 'Username');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Kecamatan');
        $sheet->setCellValue('A2', '');
        $sheet->setCellValue('B2', '');
        $sheet->setCellValue('C2', '');
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');

        $col = 4; // mulai dari kolom D
        $dateColMap = [];

        foreach ($dates as $date) {
            $label = Carbon::parse($date)->translatedFormat('d M Y');
            $startColLetter = $this->colLetter($col);
            $endColLetter = $this->colLetter($col + 2);

            $sheet->setCellValue($startColLetter . '1', $label);
            $sheet->mergeCells("{$startColLetter}1:{$endColLetter}1");

            $sheet->setCellValue($this->colLetter($col) . '2', 'Total');
            $sheet->setCellValue($this->colLetter($col + 1) . '2', 'Approved');
            $sheet->setCellValue($this->colLetter($col + 2) . '2', 'Progress %');

            $dateColMap[$date] = $col;
            $col += 3;
        }

        $lastCol = $this->colLetter($col - 1);

        $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A1:{$lastCol}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('7C3AED');
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $rowIndex = 3;
        foreach ($petugas as $p) {
            $sheet->setCellValue('A' . $rowIndex, $p['username']);
            $sheet->setCellValue('B' . $rowIndex, $p['nama']);
            $sheet->setCellValue('C' . $rowIndex, $p['kecamatan']);

            foreach ($dateColMap as $date => $startCol) {
                $entry = $p['byDate']->get($date);
                $sheet->setCellValue($this->colLetter($startCol) . $rowIndex, $entry['total_assignment'] ?? '');
                $sheet->setCellValue($this->colLetter($startCol + 1) . $rowIndex, $entry['approved'] ?? '');
                $sheet->setCellValue($this->colLetter($startCol + 2) . $rowIndex, $entry['progress'] ?? '');
            }

            $rowIndex++;
        }

        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $sheet->setAutoFilter('A2:' . $lastCol . ($rowIndex - 1));
        $sheet->freezePane('D3');
    }

    /**
     * Sheet rekap total seluruh petugas per tanggal (gambaran umum, bukan per orang).
     */
    private function addSummaryPerTanggalSheet(Spreadsheet $spreadsheet, $rows): void
    {
        $byDate = $rows->groupBy('tanggal_raw')->sortKeys()->map(function ($group, $date) {
            return [
                'tanggal' => Carbon::parse($date)->translatedFormat('d M Y'),
                'total' => $group->sum('total_assignment'),
                'open' => $group->sum('open'),
                'draft' => $group->sum('draft'),
                'submitted' => $group->sum('submitted'),
                'approved' => $group->sum('approved'),
            ];
        });

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Rekap per Tanggal');

        $headers = ['Tanggal', 'Total Assignment', 'Open', 'Draft', 'Submitted by Pencacah', 'Approved by Pengawas'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0D9488');

        $rowIndex = 2;
        foreach ($byDate as $agg) {
            $sheet->fromArray([
                $agg['tanggal'],
                $agg['total'],
                $agg['open'],
                $agg['draft'],
                $agg['submitted'],
                $agg['approved'],
            ], null, 'A' . $rowIndex);
            $rowIndex++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Sheet rekap per kecamatan, format pivot manual ala Excel:
     * - Setiap kecamatan punya baris subtotal (bold), diikuti baris tiap petugas di kecamatan itu.
     * - Kolom ASSIGNMENT/NON OPEN/SUBMIT+/%-nya pakai formula Excel asli (bukan angka statis),
     *   supaya kalau dicek manual di Excel tetap konsisten dan bisa di-trace.
     * - Baris terakhir: grand total seluruh kabupaten/kota.
     * Format ini meniru struktur "Sheet3" yang dicontohkan pengguna.
     */
    private function addKecamatanRecapSheet(Spreadsheet $spreadsheet, string $selectedDate, array $filters, bool $useActiveSheet = false): void
    {
            // Role yang dipakai buat filter — konsisten dengan index()
        $roleForRecap = $filters['petugas_role'] ?: 'pencacah';

        // Ambil upload_id TERBARU untuk tanggal & role ini (hindari sum ganda kalau ada >1 upload di hari yang sama)
        $latestUploadId = \App\Models\Upload::query()
            ->where('petugas_role', $roleForRecap)
            ->where('upload_date', $selectedDate)
            ->max('id');

        $query = AssignmentSnapshot::query()
            ->where('upload_id', $latestUploadId)   // <-- ganti dari where('upload_date', $selectedDate)
            ->when($filters['petugas_role'] ?? null, function ($q, $v) {
                $q->where('petugas_role', $v);
            })
            ->selectRaw('
                petugas_username,
                MAX(kabupaten_code) as kabupaten_code,
                MAX(kabupaten_name) as kabupaten_name,
                SUM(sls_total_assignment) as total_assignment,
                SUM(status_open) as status_open,
                SUM(status_draft) as status_draft,
                SUM(status_submitted_pencacah) as status_submitted_pencacah,
                SUM(status_approved_pengawas) as status_approved_pengawas,
                SUM(status_rejected_pengawas) as status_rejected_pengawas,

                SUM(status_edited_pengawas) as status_edited_pengawas,
                SUM(status_revoked_pengawas) as status_revoked_pengawas,
                SUM(status_submitted_respondent) as status_submitted_respondent,
                SUM(status_completed_admin_kab) as status_completed_admin_kab,
                SUM(status_edited_admin_kab) as status_edited_admin_kab,
                SUM(status_rejected_admin_kab) as status_rejected_admin_kab,
                SUM(status_revoked_admin_kab) as status_revoked_admin_kab
            ')
            ->groupBy('petugas_username')
            ->when($filters['petugas_username'] ?? null, fn($q, $v) => $q->where('petugas_username', $v))
            ->when($filters['sls_code'] ?? null, fn($q, $v) => $q->where('sls_code', 'like', '%' . $v . '%'))
            ->when($filters['nama_kecamatan'] ?? null, function ($q) use ($filters) {
                $q->whereIn('petugas_username', $filters['_usernames_in_kecamatan'] ?? []);
            });

        $petugasRows = $query->get();

        if ($petugasRows->isEmpty()) {
            return;
        }

        $referenceMap = PetugasReference::query()
            ->get(['petugas_username', 'nama_petugas', 'kode_kecamatan', 'nama_kecamatan'])
            ->keyBy('petugas_username');

        $kabupatenCode = null;
        $kabupatenName = null;

        // Kelompokkan petugas per kecamatan, berdasarkan data referensi (kode_kecamatan + nama_kecamatan)
        $groups = $petugasRows->groupBy(function ($row) use ($referenceMap, &$kabupatenCode, &$kabupatenName) {
            $kabupatenCode ??= $row->kabupaten_code;
            $kabupatenName ??= $row->kabupaten_name;

            $ref = $referenceMap->get($row->petugas_username);

            return $ref->kode_kecamatan ?? 'ZZZ_TANPA_KECAMATAN';
        })->sortBy(fn($group, $kode) => $kode)->values();

        $sheet = $useActiveSheet ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        $sheet->setTitle('Rekap Kecamatan');

        $headers = ['Row Labels', 'ASSIGNMENT', 'OPEN', 'DRAFT', 'SUBMIT', 'APPROVE', 'REJECT', 'NON OPEN', 'SUBMIT+', '% NON OPEN', '% SELAIN OPEN & DRAF', '% APPROVED'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $rowIndex = 2;
        $grand = [
            'assignment' => 0,
            'open' => 0,
            'draft' => 0,
            'submit' => 0,
            'approve' => 0,
            'reject' => 0,
            'edited_pengawas' => 0,
            'revoked_pengawas' => 0,
            'submitted_respondent' => 0,
            'completed_admin' => 0,
            'edited_admin' => 0,
            'rejected_admin' => 0,
            'revoked_admin' => 0,
        ];

        // Kelompokkan ulang per kode_kecamatan supaya bisa diakses nama & sort yang benar
        $petugasByKecamatan = $petugasRows->groupBy(function ($row) use ($referenceMap) {
            $ref = $referenceMap->get($row->petugas_username);

            return $ref->kode_kecamatan ?? 'ZZZ_TANPA_KECAMATAN';
        });

        $sortedKodeKecamatan = $petugasByKecamatan->keys()->sort()->values();

        foreach ($sortedKodeKecamatan as $kodeKecamatan) {
            $petugasInGroup = $petugasByKecamatan->get($kodeKecamatan);

            $firstRef = $referenceMap->get($petugasInGroup->first()->petugas_username);
            $namaKecamatan = $firstRef->nama_kecamatan ?? null;

            // Label kecamatan format "[010] MUARA KUANG" -> ambil 3 digit terakhir kode kecamatan
            $kodeSingkat = $kodeKecamatan === 'ZZZ_TANPA_KECAMATAN'
                ? '???'
                : substr($kodeKecamatan, -3);
            $labelKecamatan = '[' . $kodeSingkat . '] ' . mb_strtoupper($namaKecamatan ?? 'TANPA KECAMATAN');

            // Urutkan petugas dalam kecamatan secara alfabetis berdasarkan nama (fallback username)
            $petugasSorted = $petugasInGroup->sortBy(function ($row) use ($referenceMap) {
                $ref = $referenceMap->get($row->petugas_username);

                return $ref->nama_petugas ?? $row->petugas_username;
            })->values();

            $kecTotalAssignment = (int) $petugasSorted->sum('total_assignment');
            $kecOpen = (int) $petugasSorted->sum('status_open');
            $kecDraft = (int) $petugasSorted->sum('status_draft');
            $kecSubmit = (int) $petugasSorted->sum('status_submitted_pencacah');
            $kecApprove = (int) $petugasSorted->sum('status_approved_pengawas');
            $kecReject = (int) $petugasSorted->sum('status_rejected_pengawas');
            $kecEditedPengawas = (int) $petugasSorted->sum('status_edited_pengawas');
            $kecRevokedPengawas = (int) $petugasSorted->sum('status_revoked_pengawas');
            $kecSubmittedRespondent = (int) $petugasSorted->sum('status_submitted_respondent');
            $kecCompletedAdmin = (int) $petugasSorted->sum('status_completed_admin_kab');
            $kecEditedAdmin = (int) $petugasSorted->sum('status_edited_admin_kab');
            $kecRejectedAdmin = (int) $petugasSorted->sum('status_rejected_admin_kab');
            $kecRevokedAdmin = (int) $petugasSorted->sum('status_revoked_admin_kab');

            $this->writeKecamatanRecapRow(
                $sheet,
                $rowIndex,
                $labelKecamatan,
                $kecTotalAssignment,
                $kecOpen,
                $kecDraft,
                $kecSubmit,
                $kecApprove,
                $kecReject,
                $kecEditedPengawas,
                $kecRevokedPengawas,
                $kecSubmittedRespondent,
                $kecCompletedAdmin,
                $kecEditedAdmin,
                $kecRejectedAdmin,
                $kecRevokedAdmin,
                true
            );
            $rowIndex++;

            foreach ($petugasSorted as $p) {
                $ref = $referenceMap->get($p->petugas_username);
                $namaPetugas = $ref->nama_petugas ?? $p->petugas_username;

                $this->writeKecamatanRecapRow(
                    $sheet,
                    $rowIndex,
                    $namaPetugas,
                    (int) $p->total_assignment,
                    (int) $p->status_open,
                    (int) $p->status_draft,
                    (int) $p->status_submitted_pencacah,
                    (int) $p->status_approved_pengawas,
                    (int) $p->status_rejected_pengawas,
                    (int) $p->status_edited_pengawas,
                    (int) $p->status_revoked_pengawas,
                    (int) $p->status_submitted_respondent,
                    (int) $p->status_completed_admin_kab,
                    (int) $p->status_edited_admin_kab,
                    (int) $p->status_rejected_admin_kab,
                    (int) $p->status_revoked_admin_kab,
                    false
                );
                $rowIndex++;
            }

            $grand['assignment'] += $kecTotalAssignment;
            $grand['open'] += $kecOpen;
            $grand['draft'] += $kecDraft;
            $grand['submit'] += $kecSubmit;
            $grand['approve'] += $kecApprove;
            $grand['reject'] += $kecReject;
            $grand['edited_pengawas'] += $kecEditedPengawas;
            $grand['revoked_pengawas'] += $kecRevokedPengawas;
            $grand['submitted_respondent'] += $kecSubmittedRespondent;
            $grand['completed_admin'] += $kecCompletedAdmin;
            $grand['edited_admin'] += $kecEditedAdmin;
            $grand['rejected_admin'] += $kecRejectedAdmin;
            $grand['revoked_admin'] += $kecRevokedAdmin;
        }

        // Baris grand total (kabupaten/kota)
        $labelGrand = 'TOTAL';
        $this->writeKecamatanRecapRow(
            $sheet,
            $rowIndex,
            $labelGrand,
            $grand['assignment'],
            $grand['open'],
            $grand['draft'],
            $grand['submit'],
            $grand['approve'],
            $grand['reject'],
            $grand['edited_pengawas'],
            $grand['revoked_pengawas'],
            $grand['submitted_respondent'],
            $grand['completed_admin'],
            $grand['edited_admin'],
            $grand['rejected_admin'],
            $grand['revoked_admin'],
            true
        );
        $lastDataRow = $rowIndex;

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setAutoFilter('A1:L' . $lastDataRow);
        $sheet->freezePane('A2');
    }
    private function writeKecamatanRecapRow(
        $sheet,
        int $row,
        string $label,
        int $assignment,
        int $open,
        int $draft,
        int $submit,
        int $approve,
        int $reject,
        int $editedPengawas,
        int $revokedPengawas,
        int $submittedRespondent,
        int $completedAdmin,
        int $editedAdmin,
        int $rejectedAdmin,
        int $revokedAdmin,
        bool $bold
    ): void {
        $sheet->setCellValue("A{$row}", $label);
        $sheet->setCellValue("B{$row}", $assignment);
        $sheet->setCellValue("C{$row}", $open);
        $sheet->setCellValue("D{$row}", $draft);
        $sheet->setCellValue("E{$row}", $submit);
        $sheet->setCellValue("F{$row}", $approve);
        $sheet->setCellValue("G{$row}", $reject);
        $sheet->setCellValue("H{$row}", "=B{$row}-C{$row}");
        $sheet->setCellValue("I{$row}", "=B{$row}-C{$row}-D{$row}");
        $sheet->setCellValue("J{$row}", "=H{$row}/B{$row}");
        $sheet->setCellValue("K{$row}", "=I{$row}/B{$row}");
        $sheet->setCellValue("L{$row}", "=(B{$row}-C{$row}-D{$row}-E{$row})/B{$row}");

        $sheet->getStyle("J{$row}:L{$row}")
            ->getNumberFormat()
            ->setFormatCode('0%');
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');

        if ($bold) {
            $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        }
    }

    private function colLetter(int $colNumber): string
    {
        $letter = '';
        while ($colNumber > 0) {
            $mod = ($colNumber - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colNumber = intdiv($colNumber - $mod, 26);
        }

        return $letter;
    }
    public function exportUsaha()
    {
        $data = Usaha::query()
            ->get()
            ->groupBy(function ($row) {
                return implode('|', [
                    $row->id_wilayah,
                    $row->kd_kab,
                    $row->nama_sls,
                    $row->ppl,
                    $row->pml,
                ]);
            })
            ->map(function ($rows) {

                $row = clone $rows->first();

                $fields = [
                    'jumlah_ub_prelist_awal',
                    'jumlah_um_prelist_awal',
                    'jumlah_umk_prelist_awal',

                    'jumlah_usaha_ditemukan_bku',
                    'jumlah_usaha_ditutup_bku',
                    'jumlah_usaha_ganda_bku',
                    'jumlah_usaha_tidak_ditemukan_bku',
                    'jumlah_usaha_baru_bku',

                    'jumlah_usaha_ditemukan_usaha_keluarga',
                    'jumlah_usaha_tutup_usaha_keluarga',
                    'jumlah_usaha_ganda_usaha_keluarga',
                    'jumlah_usaha_tidak_ditemukan_usaha_keluarga',
                    'jumlah_usaha_baru_usaha_keluarga',

                    'jumlah_keluarga_ditemukan',
                    'jumlah_keluarga_meninggal',
                    'jumlah_keluarga_tidak_eligible',
                    'jumlah_keluarga_tidak_ditemui',
                    'jumlah_keluarga_tidak_ditemukan',
                    'jumlah_keluarga_baru',

                    'jumlah_prelist_usaha',
                    'jumlah_usaha_realisasi',
                    'jumlah_prelist_keluarga',
                    'jumlah_keluarga_realisasi',
                ];

                foreach ($fields as $field) {
                    $row->{$field} = $rows->sum($field);
                }

                return $row;
            })
            ->sortBy('nama_sls')
            ->values();

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Usaha');

        $headers = [
            'No',
            'ID Wilayah',
            'Kode Kabupaten',
            'Kecamatan',
            'Nama SLS',
            'Petugas',
            'Email Petugas',
            'PPL',
            'PML',

            'UB Prelist Awal',
            'UM Prelist Awal',
            'UMK Prelist Awal',

            'Usaha Ditemukan BKU',
            'Usaha Ditutup BKU',
            'Usaha Ganda BKU',
            'Usaha Tidak Ditemukan BKU',
            'Usaha Baru BKU',

            'Usaha Ditemukan Usaha Keluarga',
            'Usaha Tutup Usaha Keluarga',
            'Usaha Ganda Usaha Keluarga',
            'Usaha Tidak Ditemukan Usaha Keluarga',
            'Usaha Baru Usaha Keluarga',

            'Keluarga Ditemukan',
            'Keluarga Meninggal',
            'Keluarga Tidak Eligible',
            'Keluarga Tidak Ditemui',
            'Keluarga Tidak Ditemukan',
            'Keluarga Baru',

            'Prelist Usaha',
            'Usaha Realisasi',
            'Prelist Keluarga',
            'Keluarga Realisasi',
        ];

        foreach ($headers as $column => $header) {
            $cell = $this->colLetter($column + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        $rowNumber = 2;

        foreach ($data as $index => $row) {

            $values = [
                $index + 1,
                $row->id_wilayah,
                $row->kd_kab,
                $row->nama_kecamatan,
                $row->nama_sls,

                // Tidak ada tabel pegawai
                '',
                '',

                $row->ppl,
                $row->pml,

                $row->jumlah_ub_prelist_awal,
                $row->jumlah_um_prelist_awal,
                $row->jumlah_umk_prelist_awal,

                $row->jumlah_usaha_ditemukan_bku,
                $row->jumlah_usaha_ditutup_bku,
                $row->jumlah_usaha_ganda_bku,
                $row->jumlah_usaha_tidak_ditemukan_bku,
                $row->jumlah_usaha_baru_bku,

                $row->jumlah_usaha_ditemukan_usaha_keluarga,
                $row->jumlah_usaha_tutup_usaha_keluarga,
                $row->jumlah_usaha_ganda_usaha_keluarga,
                $row->jumlah_usaha_tidak_ditemukan_usaha_keluarga,
                $row->jumlah_usaha_baru_usaha_keluarga,

                $row->jumlah_keluarga_ditemukan,
                $row->jumlah_keluarga_meninggal,
                $row->jumlah_keluarga_tidak_eligible,
                $row->jumlah_keluarga_tidak_ditemui,
                $row->jumlah_keluarga_tidak_ditemukan,
                $row->jumlah_keluarga_baru,

                $row->jumlah_prelist_usaha,
                $row->jumlah_usaha_realisasi,
                $row->jumlah_prelist_keluarga,
                $row->jumlah_keluarga_realisasi,
            ];

            foreach ($values as $column => $value) {
                $cell = $this->colLetter($column + 1) . $rowNumber;
                $sheet->setCellValue($cell, $value ?? '');
            }

            $rowNumber++;
        }

        $lastColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$lastColumn}1")
            ->getFont()
            ->setBold(true);

        for ($column = 1; $column <= count($headers); $column++) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}" . ($rowNumber - 1));

        $filename = 'data-usaha-' . now()->format('Y-m-d-His') . '.xlsx';

        $tempPath = storage_path(
            'app/' . uniqid('export_usaha_') . '.xlsx'
        );

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()
            ->download($tempPath, $filename)
            ->deleteFileAfterSend(true);
    }

    public function exportUsahaGrouped(Request $request)
    {
        $referenceMap = PetugasReference::query()
            ->get([
                'petugas_username',
                'nama_petugas',
                'kode_kecamatan',
                'nama_kecamatan',
            ])
            ->keyBy('petugas_username');
            

        $latestUpload = UsahaUpload::query()
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();

        $query = Usaha::query();

        if ($latestUpload) {
            $query->where('upload_id', $latestUpload->id);
        }

        if ($request->filled('kd_kab')) {
            $query->where('kd_kab', $request->kd_kab);
        }

        if ($request->filled('nama_sls')) {
            $query->where('nama_sls', $request->nama_sls);
        }

        if ($request->filled('ppl')) {
            $query->where('ppl', $request->ppl);
        }

        if ($request->filled('pml')) {
            $query->where('pml', $request->pml);
        }

        $fields = [
            'jumlah_ub_prelist_awal',
            'jumlah_um_prelist_awal',
            'jumlah_umk_prelist_awal',

            'jumlah_usaha_ditemukan_bku',
            'jumlah_usaha_ditutup_bku',
            'jumlah_usaha_ganda_bku',
            'jumlah_usaha_tidak_ditemukan_bku',
            'jumlah_usaha_baru_bku',

            'jumlah_usaha_ditemukan_usaha_keluarga',
            'jumlah_usaha_tutup_usaha_keluarga',
            'jumlah_usaha_ganda_usaha_keluarga',
            'jumlah_usaha_tidak_ditemukan_usaha_keluarga',
            'jumlah_usaha_baru_usaha_keluarga',

            'jumlah_keluarga_ditemukan',
            'jumlah_keluarga_meninggal',
            'jumlah_keluarga_tidak_eligible',
            'jumlah_keluarga_tidak_ditemui',
            'jumlah_keluarga_tidak_ditemukan',
            'jumlah_keluarga_baru',

            'jumlah_prelist_usaha',
            'jumlah_usaha_realisasi',
            'jumlah_prelist_keluarga',
            'jumlah_keluarga_realisasi',
        ];
        if ($request->filled('nama_kecamatan')) {
            $usernames = PetugasReference::query()
                ->where('nama_kecamatan', $request->nama_kecamatan)
                ->pluck('petugas_username')
                ->all();

            $query->whereIn('ppl', $usernames);
        }

        $data = $query
            ->get()
            ->groupBy(function ($row) {
                return implode('|', [
                    $row->id_wilayah,
                    $row->kd_kab,
                    $row->nama_sls,
                    $row->ppl,
                    $row->pml,
                ]);
            })
            ->map(function ($rows) use ($referenceMap, $fields) {

                $row = clone $rows->first();

                foreach ($fields as $field) {
                    $row->{$field} = $rows->sum($field);
                }

                $ref = $referenceMap->get($row->ppl);

                $row->nama_petugas = $ref->nama_petugas ?? null;
                $row->email_petugas = $ref->petugas_username ?? null;
                $row->kode_kecamatan = $ref->kode_kecamatan ?? null;
                $row->nama_kecamatan = $ref->nama_kecamatan ?? null;

                return $row;
            })
            ->sortBy('nama_sls')
            ->values();

        $dataGrouped = $data
            ->groupBy(function ($row) {
                return $row->nama_kecamatan ?: 'Tanpa Kecamatan';
            })
            ->map(function ($rowsByKecamatan) use ($fields) {

                $totals = [];

                foreach ($fields as $field) {
                    $totals[$field] = $rowsByKecamatan->sum($field);
                }

                $petugas = $rowsByKecamatan
                    ->groupBy(function ($row) {
                        return $row->nama_petugas
                            ?: ($row->ppl ?: 'Tanpa Petugas');
                    })
                    ->map(function ($rowsByPetugas) use ($fields) {

                        $petugasTotals = [];

                        foreach ($fields as $field) {
                            $petugasTotals[$field] = $rowsByPetugas->sum($field);
                        }

                        return [
                            'rows' => $rowsByPetugas,
                            'totals' => $petugasTotals,
                        ];
                    });

                return [
                    'totals' => $totals,
                    'petugas' => $petugas,
                ];
            })
            ->sortKeys();

        $grandTotals = [];

        foreach ($fields as $field) {
            $grandTotals[$field] = $data->sum($field);
        }

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Usaha');

        $headers = [
            '#',
            'ID Wilayah',
            'Kode Kabupaten',
            'Nama SLS',

            'UB Prelist Awal',
            'UM Prelist Awal',
            'UMK Prelist Awal',

            'Usaha Ditemukan (BKU)',
            'Usaha Ditutup (BKU)',
            'Usaha Ganda (BKU)',
            'Usaha Tidak Ditemukan (BKU)',
            'Usaha Baru (BKU)',

            'Usaha Ditemukan (Usaha Keluarga)',
            'Usaha Tutup (Usaha Keluarga)',
            'Usaha Ganda (Usaha Keluarga)',
            'Usaha Tidak Ditemukan (Usaha Keluarga)',
            'Usaha Baru (Usaha Keluarga)',

            'Keluarga Ditemukan',
            'Keluarga Meninggal',
            'Keluarga Tidak Eligible',
            'Keluarga Tidak Dapat Ditemui',
            'Keluarga Tidak Ditemukan',
            'Keluarga Baru',

            'Jumlah Prelist Usaha',
            'Jumlah Usaha Realisasi',
            'Jumlah Prelist Keluarga',
            'Jumlah Keluarga Realisasi',

            'PPL',
            'PML',
            'Last Update',
        ];

        foreach ($headers as $index => $header) {
            $column = $this->colLetter($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $lastCol = $this->colLetter(count($headers));

        $sheet->getStyle("A1:{$lastCol}1")
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setRGB('FFFFFF');

        $sheet->getStyle("A1:{$lastCol}1")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('0D9488');

        $sheet->getStyle("A1:{$lastCol}1")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $rowIndex = 2;

        $dataRowStart = $rowIndex;

        foreach ($dataGrouped as $namaKecamatan => $kecamatan) {

            $kecamatanRows = $kecamatan['petugas']->flatMap(function ($petugas) {
                return $petugas['rows'];
            });

            $firstRow = $kecamatanRows->first();

            $sheet->setCellValue("A{$rowIndex}", $namaKecamatan);
            $sheet->setCellValue(
                "B{$rowIndex}",
                $firstRow->id_wilayah ?? ''
            );
            $sheet->setCellValue(
                "C{$rowIndex}",
                $firstRow->kd_kab ?? ''
            );

            $col = 5;

            foreach ($fields as $field) {
                $sheet->setCellValue(
                    $this->colLetter($col) . $rowIndex,
                    $kecamatan['totals'][$field] ?? 0
                );

                $col++;
            }

            $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('E2E8F0');

            $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")
                ->getFont()
                ->setBold(true);

            $rowIndex++;

            foreach ($kecamatan['petugas'] as $namaPetugas => $petugas) {

                $petugasRows = $petugas['rows'];

                $firstPetugasRow = $petugasRows->first();

                $sheet->setCellValue(
                    "A{$rowIndex}",
                    $namaPetugas
                );

                $sheet->setCellValue(
                    "B{$rowIndex}",
                    $firstPetugasRow->ppl ?? ''
                );

                $col = 5;

                foreach ($fields as $field) {
                    $sheet->setCellValue(
                        $this->colLetter($col) . $rowIndex,
                        $petugas['totals'][$field] ?? 0
                    );

                    $col++;
                }

                $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F1F5F9');

                $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")
                    ->getFont()
                    ->setBold(true);

                $rowIndex++;

                foreach ($petugasRows->values() as $index => $row) {

                    $sheet->setCellValue(
                        "A{$rowIndex}",
                        $index + 1
                    );

                    $sheet->setCellValue(
                        "B{$rowIndex}",
                        $row->id_wilayah ?? '-'
                    );

                    $sheet->setCellValue(
                        "C{$rowIndex}",
                        $row->kd_kab ?? '-'
                    );

                    $sheet->setCellValue(
                        "D{$rowIndex}",
                        $row->nama_sls ?? '-'
                    );

                    $col = 5;

                    foreach ($fields as $field) {
                        $sheet->setCellValue(
                            $this->colLetter($col) . $rowIndex,
                            $row->{$field} ?? 0
                        );

                        $col++;
                    }

                    $sheet->setCellValue(
                        $this->colLetter(count($headers) - 2) . $rowIndex,
                        $row->ppl ?? '-'
                    );

                    $sheet->setCellValue(
                        $this->colLetter(count($headers) - 1) . $rowIndex,
                        $row->pml ?? '-'
                    );

                    $sheet->setCellValue(
                        $this->colLetter(count($headers)) . $rowIndex,
                        $row->last_update ?? '-'
                    );

                    $rowIndex++;
                }
            }
        }

        $sheet->setCellValue(
            "A{$rowIndex}",
            'TOTAL KESELURUHAN'
        );

        $col = 5;

        foreach ($fields as $field) {
            $sheet->setCellValue(
                $this->colLetter($col) . $rowIndex,
                $grandTotals[$field] ?? 0
            );

            $col++;
        }

        $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('CBD5E1');

        $sheet->getStyle("A{$rowIndex}:{$lastCol}{$rowIndex}")
            ->getFont()
            ->setBold(true);

        $lastDataRow = $rowIndex;

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet
                ->getColumnDimension($this->colLetter($i))
                ->setAutoSize(true);
        }

        $sheet->getStyle("A1:{$lastCol}{$lastDataRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("E2:{$lastCol}{$lastDataRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->freezePane('A2');

        $filename = 'data-usaha-' . now()->format('Y-m-d-His') . '.xlsx';

        $tempPath = storage_path(
            'app/' . uniqid('export_usaha_') . '.xlsx'
        );

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()
            ->download($tempPath, $filename)
            ->deleteFileAfterSend(true);
    }
}
