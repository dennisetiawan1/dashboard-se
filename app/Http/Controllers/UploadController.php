<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSnapshot;
use App\Models\Upload;
use App\Support\SpreadsheetReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\ReferenceUpload;
use App\Models\PetugasReference;
use App\Models\UsahaUpload;

class UploadController extends Controller
{
    public function index(): View
    {
        $uploads = Upload::orderByDesc('upload_date')->get();

        $referenceUploads = ReferenceUpload::latest()->get();

        $referenceCount = PetugasReference::count();
        $usahaUploads = UsahaUpload::latest()->get();

        return view('uploads.index', compact(
            'uploads',
            'referenceUploads',
            'referenceCount',
            'usahaUploads'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'upload_date' => ['required', 'date'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:20480'],
        ], [
            'upload_date.required' => 'Tanggal data harus diisi.',
            'file.required' => 'File harus dipilih.',
        ]);

        $uploadDate = $request->input('upload_date');

        $file = $request->file('file');

        $storedPath = $file->store('assignment_uploads', 'public');

        $extension = $file->getClientOriginalExtension();
        $fullPath = $file->getRealPath();

        [$headers, $rows] = SpreadsheetReader::read($fullPath, $extension);

        if (empty($rows)) {
            return back()->withErrors(['file' => 'File tidak terbaca atau tidak ada data di dalamnya.']);
        }

        $map = $this->buildColumnMap($headers);

        // Ambil role dari file excel
        $uploadRole = null;

        foreach ($rows as $row) {
            $uploadRole = $this->val($row, $map, 'petugas_role');

            if (!empty($uploadRole)) {
                break;
            }
        }

        DB::transaction(function () use (
            $uploadDate,
            $rows,
            $map,
            $file,
            $uploadRole,
            $storedPath
        ) {

            $existing = Upload::where('upload_date', $uploadDate)
                ->where('petugas_role', $uploadRole)
                ->first();

            if ($existing) {
                $existing->delete();
            }

            $upload = Upload::create([
                'upload_date'       => $uploadDate,
                'petugas_role'      => $uploadRole,
                'original_filename' => $file->getClientOriginalName(),
                'file_path'         => $storedPath,
                'total_rows'        => count($rows),
            ]);

            $now = now();
            $batch = [];

            foreach ($rows as $row) {
                $batch[] = [
                    'upload_id' => $upload->id,
                    'upload_date' => $uploadDate,
                    'kabupaten_code' => $this->val($row, $map, 'kabupaten_code'),
                    'kabupaten_name' => $this->val($row, $map, 'kabupaten_name'),
                    'petugas_user_id' => $this->val($row, $map, 'petugas_user_id'),
                    'petugas_username' => $this->val($row, $map, 'petugas_username'),
                    'petugas_email' => $this->val($row, $map, 'petugas_email'),
                    'petugas_role' => $this->val($row, $map, 'petugas_role'),
                    'petugas_total_assignment' => $this->intVal($row, $map, 'petugas_total_assignment'),
                    'sls_code' => SpreadsheetReader::toCleanString($this->val($row, $map, 'sls_code')),
                    'sls_total_assignment' => $this->intVal($row, $map, 'sls_total_assignment'),
                    'status_open' => $this->intVal($row, $map, 'assignment_status_open'),
                    'status_draft' => $this->intVal($row, $map, 'assignment_status_draft'),
                    'status_submitted_pencacah' => $this->intVal($row, $map, 'assignment_status_submitted_by_pencacah'),
                    'status_approved_pengawas' => $this->intVal($row, $map, 'assignment_status_approved_by_pengawas'),
                    'status_rejected_pengawas' => $this->intVal($row, $map, 'assignment_status_rejected_by_pengawas'),
                    'status_edited_pengawas' => $this->intVal($row, $map, 'assignment_status_edited_by_pengawas'),
                    'status_revoked_pengawas' => $this->intVal($row, $map, 'assignment_status_revoked_by_pengawas'),
                    'status_submitted_respondent' => $this->intVal($row, $map, 'assignment_status_submitted_respondent'),
                    'status_completed_admin_kab' => $this->intVal($row, $map, 'assignment_status_completed_by_admin_kabupaten'),
                    'status_edited_admin_kab' => $this->intVal($row, $map, 'assignment_status_edited_by_admin_kabupaten'),
                    'status_rejected_admin_kab' => $this->intVal($row, $map, 'assignment_status_rejected_by_admin_kabupaten'),
                    'status_revoked_admin_kab' => $this->intVal($row, $map, 'assignment_status_revoked_by_admin_kabupaten'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($batch, 500) as $chunk) {
                AssignmentSnapshot::insert($chunk);
            }
        });

        return redirect()->route('uploads.index')
            ->with('success', 'Data tanggal ' . \Carbon\Carbon::parse($uploadDate)->translatedFormat('d F Y') . ' berhasil diupload (' . count($rows) . ' baris).');
    }

    public function destroy(Upload $upload): RedirectResponse
    {
        $upload->delete();

        return redirect()->route('uploads.index')->with('success', 'Data berhasil dihapus.');
    }

    public function download(Upload $upload)
    {
        return \Storage::disk('public')->download(
            $upload->file_path,
            $upload->original_filename
        );
    }

    private function buildColumnMap(array $headers): array
    {
        $targets = [
            'kabupaten_code',
            'kabupaten_name',
            'petugas_user_id',
            'petugas_username',
            'petugas_email',
            'petugas_role',
            'petugas_total_assignment',
            'sls_code',
            'sls_total_assignment',
            'assignment_status_open',
            'assignment_status_draft',
            'assignment_status_submitted_by_pencacah',
            'assignment_status_approved_by_pengawas',
            'assignment_status_rejected_by_pengawas',
            'assignment_status_edited_by_pengawas',
            'assignment_status_revoked_by_pengawas',
            'assignment_status_submitted_respondent',
            'assignment_status_completed_by_admin_kabupaten',
            'assignment_status_edited_by_admin_kabupaten',
            'assignment_status_rejected_by_admin_kabupaten',
            'assignment_status_revoked_by_admin_kabupaten',
        ];

        $map = [];
        foreach ($targets as $target) {
            foreach ($headers as $header) {
                if ($header === $target) {
                    $map[$target] = $header;
                    continue 2;
                }
            }
            // fallback: cari yang paling mirip (mengandung kata kunci utama)
            foreach ($headers as $header) {
                if (str_contains($header, $target) || str_contains($target, $header)) {
                    $map[$target] = $header;
                    continue 2;
                }
            }
        }

        return $map;
    }

    private function val(array $row, array $map, string $target): ?string
    {
        $key = $map[$target] ?? null;
        if ($key === null || ! array_key_exists($key, $row)) {
            return null;
        }

        $value = trim((string) $row[$key]);

        return $value === '' ? null : $value;
    }

    private function intVal(array $row, array $map, string $target): int
    {
        return SpreadsheetReader::toInt($this->val($row, $map, $target));
    }
}
