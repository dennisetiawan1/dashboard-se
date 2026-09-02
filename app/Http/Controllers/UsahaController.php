<?php

namespace App\Http\Controllers;

use App\Models\PetugasReference;
use App\Models\Usaha;
use App\Models\UsahaUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\DesaReference;
use App\Models\KecamatanWilkerStat;

class UsahaController extends Controller
{
    public function index(Request $request)
    {
            
        //  REFERENCE PETUGAS & DESA
        $referenceMap = PetugasReference::query()
            ->get([
                'petugas_username',
                'nama_petugas',
                'kode_kecamatan',
                'nama_kecamatan'
            ])
            ->keyBy('petugas_username');

        $desaReferenceMap = DesaReference::query()
            ->get([
                'id_wilayah',
                'nama_desa',
            ])
            ->keyBy('id_wilayah');

        // Ambil data wilker stat
       $wilkerStatMap = KecamatanWilkerStat::query()
            ->get([
                'kecamatan',
                'bku_wilkerstat',
                'st_2023'
            ])
            ->keyBy('kecamatan');

        $totalWilkerStat = KecamatanWilkerStat::sum('bku_wilkerstat');
        $totalST2023 = KecamatanWilkerStat::sum('st_2023');

        /* FILTER OPTIONS - KECAMATAN */
        $kecamatanOptions = PetugasReference::query()
            ->whereNotNull('nama_kecamatan')
            ->where('nama_kecamatan', '!=', '')
            ->distinct()
            ->orderBy('nama_kecamatan')
            ->pluck('nama_kecamatan');

        // Daftar tanggal upload yang tersedia, untuk dropdown filter tanggal
        $availableUploadDates = UsahaUpload::query()
            ->orderByDesc('upload_date')
            ->get()
            ->pluck('upload_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values();

        // AMBIL UPLOAD SESUAI TANGGAL DIPILIH (atau upload terbaru kalau tidak ada filter tanggal)
        if ($request->filled('tanggal')) {
            $latestUpload = UsahaUpload::query()
                ->where('upload_date', $request->tanggal)
                ->orderByDesc('id')
                ->first();
        } else {
            $latestUpload = UsahaUpload::query()
                ->orderByDesc('upload_date')
                ->orderByDesc('id')
                ->first();
        }

        // QUERY USAHA (HANYA UPLOAD TERPILIH)

        $query = Usaha::query();

        if ($latestUpload) {
            $query->where('upload_id', $latestUpload->id);
        }


        /* FILTER KABUPATEN        */

        if ($request->filled('kd_kab')) {
            $query->where('kd_kab', $request->kd_kab);
        }

        /* FILTER SLS        */

        if ($request->filled('nama_sls')) {
            $query->where('nama_sls', $request->nama_sls);
        }

        /* FILTER PPL        */

        if ($request->filled('ppl')) {
            $query->where('ppl', $request->ppl);
        }

        /* FILTER PML  */

        if ($request->filled('pml')) {
            $query->where('pml', $request->pml);
        }

        /* FILTER KECAMATAN */
        if ($request->filled('nama_kecamatan')) {
            $usernames = PetugasReference::query()
                ->where('nama_kecamatan', $request->nama_kecamatan)
                ->pluck('petugas_username')
                ->all();

            $query->whereIn('ppl', $usernames);
        }

        //  DATA USAHA
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
            ->map(function ($rows) use ($referenceMap, $desaReferenceMap) {

                $row = clone $rows->first();

                $row->jumlah_ub_prelist_awal =
                    $rows->sum('jumlah_ub_prelist_awal');

                $row->jumlah_um_prelist_awal =
                    $rows->sum('jumlah_um_prelist_awal');

                $row->jumlah_umk_prelist_awal =
                    $rows->sum('jumlah_umk_prelist_awal');

                $row->jumlah_usaha_ditemukan_bku =
                    $rows->sum('jumlah_usaha_ditemukan_bku');

                $row->jumlah_usaha_ditutup_bku =
                    $rows->sum('jumlah_usaha_ditutup_bku');

                $row->jumlah_usaha_ganda_bku =
                    $rows->sum('jumlah_usaha_ganda_bku');

                $row->jumlah_usaha_tidak_ditemukan_bku =
                    $rows->sum('jumlah_usaha_tidak_ditemukan_bku');

                $row->jumlah_usaha_baru_bku =
                    $rows->sum('jumlah_usaha_baru_bku');

                $row->jumlah_usaha_ditemukan_usaha_keluarga =
                    $rows->sum('jumlah_usaha_ditemukan_usaha_keluarga');

                $row->jumlah_usaha_tutup_usaha_keluarga =
                    $rows->sum('jumlah_usaha_tutup_usaha_keluarga');

                $row->jumlah_usaha_ganda_usaha_keluarga =
                    $rows->sum('jumlah_usaha_ganda_usaha_keluarga');

                $row->jumlah_usaha_tidak_ditemukan_usaha_keluarga =
                    $rows->sum('jumlah_usaha_tidak_ditemukan_usaha_keluarga');

                $row->jumlah_usaha_baru_usaha_keluarga =
                    $rows->sum('jumlah_usaha_baru_usaha_keluarga');


                $row->jumlah_keluarga_ditemukan =
                    $rows->sum('jumlah_keluarga_ditemukan');

                $row->jumlah_keluarga_meninggal =
                    $rows->sum('jumlah_keluarga_meninggal');

                $row->jumlah_keluarga_tidak_eligible =
                    $rows->sum('jumlah_keluarga_tidak_eligible');

                $row->jumlah_keluarga_tidak_ditemui =
                    $rows->sum('jumlah_keluarga_tidak_ditemui');

                $row->jumlah_keluarga_tidak_ditemukan =
                    $rows->sum('jumlah_keluarga_tidak_ditemukan');

                $row->jumlah_keluarga_baru =
                    $rows->sum('jumlah_keluarga_baru');

                $row->jumlah_prelist_usaha =
                    $rows->sum('jumlah_prelist_usaha');

                $row->jumlah_usaha_realisasi =
                    $rows->sum('jumlah_usaha_realisasi');

                $row->jumlah_prelist_keluarga =
                    $rows->sum('jumlah_prelist_keluarga');

                $row->jumlah_keluarga_realisasi =
                    $rows->sum('jumlah_keluarga_realisasi');

                $ref = $referenceMap->get($row->ppl);

                $row->nama_petugas    = $ref->nama_petugas ?? null;
                $row->email_petugas   = $ref->petugas_username ?? null;
                $row->kode_kecamatan  = $ref->kode_kecamatan ?? null;
                $row->nama_kecamatan  = $ref->nama_kecamatan ?? null;

                $desaRef = $desaReferenceMap->get($row->id_wilayah);

                $row->nama_desa = $desaRef->nama_desa ?? 'Tanpa Desa';

                return $row;
            })
            ->sortBy('nama_sls')
            ->values();

        /*        | SUMMARY AKUMULASI        | Karena $data mengambil seluruh record Usaha, maka sum() otomatis menjumlahkan semua upload.        */

        $summary = [
            /*  TOTAL USAHA & KELUARGA  */

            'prelist_usaha' =>
            $data->sum('jumlah_prelist_usaha'),

            'usaha_realisasi' =>
            $data->sum('jumlah_usaha_realisasi'),

            'prelist_keluarga' =>
            $data->sum('jumlah_prelist_keluarga'),

            'keluarga_realisasi' =>
            $data->sum('jumlah_keluarga_realisasi'),
            /*  BKU  */

            'usaha_ditemukan_bku' =>
            $data->sum('jumlah_usaha_ditemukan_bku'),

            'usaha_ditutup_bku' =>
            $data->sum('jumlah_usaha_ditutup_bku'),

            'usaha_ganda_bku' =>
            $data->sum('jumlah_usaha_ganda_bku'),

            'usaha_tidak_ditemukan_bku' =>
            $data->sum('jumlah_usaha_tidak_ditemukan_bku'),

            'usaha_baru_bku' =>
            $data->sum('jumlah_usaha_baru_bku'),

            /* USAHA KELUARGA    */

            'usaha_ditemukan_keluarga' =>
            $data->sum('jumlah_usaha_ditemukan_usaha_keluarga'),

            'usaha_tutup_keluarga' =>
            $data->sum('jumlah_usaha_tutup_usaha_keluarga'),

            'usaha_ganda_keluarga' =>
            $data->sum('jumlah_usaha_ganda_usaha_keluarga'),

            'usaha_tidak_ditemukan_keluarga' =>
            $data->sum('jumlah_usaha_tidak_ditemukan_usaha_keluarga'),

            'usaha_baru_keluarga' =>
            $data->sum('jumlah_usaha_baru_usaha_keluarga'),

            /* KELUARGA */

            'keluarga_ditemukan' =>
            $data->sum('jumlah_keluarga_ditemukan'),

            'keluarga_meninggal' =>
            $data->sum('jumlah_keluarga_meninggal'),

            'keluarga_tidak_eligible' =>
            $data->sum('jumlah_keluarga_tidak_eligible'),

            'keluarga_tidak_ditemui' =>
            $data->sum('jumlah_keluarga_tidak_ditemui'),

            'keluarga_tidak_ditemukan' =>
            $data->sum('jumlah_keluarga_tidak_ditemukan'),

            'keluarga_baru' =>
            $data->sum('jumlah_keluarga_baru'),
        ];


        /* FILTER OPTIONS - KABUPATEN       */

        $kabupatenOptions = Usaha::query()
            ->whereNotNull('kd_kab')
            ->where('kd_kab', '!=', '')
            ->distinct()
            ->orderBy('kd_kab')
            ->pluck('kd_kab');
        /* FILTER OPTIONS - SLS        */
        $slsOptions = Usaha::query()
            ->whereNotNull('nama_sls')
            ->where('nama_sls', '!=', '')
            ->distinct()
            ->orderBy('nama_sls')
            ->pluck('nama_sls');

        //    FILTER OPTIONS - PPL

        $pplOptions = Usaha::query()
            ->join('petugas_references', 'petugas_references.petugas_username', '=', 'usaha.ppl')
            ->whereNotNull('usaha.ppl')
            ->where('usaha.ppl', '!=', '')
            ->select(
                'petugas_references.petugas_username',
                'petugas_references.nama_petugas'
            )
            ->distinct()
            ->orderBy('petugas_references.nama_petugas')
            ->get();

        // FILTER OPTIONS - PML

        $pmlOptions = Usaha::query()
            ->whereNotNull('pml')
            ->where('pml', '!=', '')
            ->distinct()
            ->orderBy('pml')
            ->pluck('pml');

        $percentageSummary = [

            'bku' => [
                'value' => $summary['prelist_usaha'] > 0
                    ? (
                        (
                            $summary['usaha_ditemukan_bku'] +
                            $summary['usaha_baru_bku']
                        )
                        / $summary['prelist_usaha']
                    ) * 100
                    : 0,

                'numerator' =>
                $summary['usaha_ditemukan_bku'] +
                    $summary['usaha_baru_bku'],

                'denominator' =>
                $summary['prelist_usaha'],
            ],

            'usaha_keluarga' => [
                'value' => $summary['prelist_keluarga'] > 0
                    ? (
                        (
                            $summary['usaha_ditemukan_keluarga'] +
                            $summary['usaha_baru_keluarga']
                        )
                        / $summary['prelist_keluarga']
                    ) * 100
                    : 0,

                'numerator' =>
                $summary['usaha_ditemukan_keluarga'] +
                    $summary['usaha_baru_keluarga'],

                'denominator' =>
                $summary['prelist_keluarga'],
            ],

            'total_usaha' => [
                'value' => $summary['prelist_keluarga'] > 0
                    ? (
                        (
                            $summary['usaha_ditemukan_bku'] +
                            $summary['usaha_baru_bku'] +
                            $summary['usaha_ditemukan_keluarga'] +
                            $summary['usaha_baru_keluarga']
                        )
                        / $summary['prelist_keluarga']
                    ) * 100
                    : 0,

                'numerator' =>
                $summary['usaha_ditemukan_bku'] +
                    $summary['usaha_baru_bku'] +
                    $summary['usaha_ditemukan_keluarga'] +
                    $summary['usaha_baru_keluarga'],

                'denominator' =>
                $summary['prelist_keluarga'],
            ],

        ];
        //  TABEL 1 - PERBANDINGAN BERDASARKAN TANGGAL UPLOAD

        $uploads = UsahaUpload::query()
            ->orderBy('upload_date')
            ->get();

        $tanggalUploads = UsahaUpload::query()
            ->whereNotNull('upload_date')
            ->orderBy('upload_date')
            ->pluck('upload_date')
            ->map(function ($tanggal) {
                return \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
            })
            ->unique()
            ->values()
            ->all();

        //  DATA TABEL PER KECAMATAN / PETUGAS

        $progressData = Usaha::query()
            ->join('usaha_uploads', 'usaha.upload_id', '=', 'usaha_uploads.id')
            ->select(
                'usaha.ppl',
                'usaha_uploads.upload_date',
                DB::raw('SUM(usaha.jumlah_usaha_ditemukan_bku + usaha.jumlah_usaha_baru_bku) as bku'),
                DB::raw('SUM(usaha.jumlah_usaha_ditemukan_usaha_keluarga + usaha.jumlah_usaha_baru_usaha_keluarga) as usaha_keluarga'),
                DB::raw('SUM(usaha.jumlah_keluarga_ditemukan + usaha.jumlah_keluarga_baru) as keluarga')
            )
            ->groupBy('usaha.ppl', 'usaha_uploads.upload_date')
            ->get();

        //  KELOMPOKKAN DATA USAHA: KECAMATAN -> DESA -> PETUGAS

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

        $dataGrouped = $data
            ->groupBy(function ($row) {
                return $row->nama_kecamatan ?: 'Tanpa Kecamatan';
            })
            ->map(function ($rowsByKecamatan) use ($fields) {

                $kecamatanTotals = [];
                foreach ($fields as $field) {
                    $kecamatanTotals[$field] = $rowsByKecamatan->sum($field);
                }

                $desaGroups = $rowsByKecamatan
                    ->groupBy(function ($row) {
                        return $row->nama_desa ?: 'Tanpa Desa';
                    })
                    ->map(function ($rowsByDesa) use ($fields) {

                        $desaTotals = [];
                        foreach ($fields as $field) {
                            $desaTotals[$field] = $rowsByDesa->sum($field);
                        }

                        $petugas = $rowsByDesa
                            ->groupBy(function ($row) {
                                return $row->nama_petugas
                                    ?: ($row->ppl ?: 'Tanpa Petugas');
                            });

                        return [
                            'totals'  => $desaTotals,
                            'petugas' => $petugas,
                        ];
                    })
                    ->sortKeys();

                return [
                    'totals' => $kecamatanTotals,
                    'desa'   => $desaGroups,
                ];
            })
            ->sortKeys();


        $grandTotals = [];

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
            $grandTotals[$field] = $data->sum($field);
        }
        //  KELOMPOKKAN: KECAMATAN -> PETUGAS -> TANGGAL

        $progressTable = [];

        foreach ($progressData as $row) {

            $ref = $referenceMap->get($row->ppl);

            $namaKecamatan = $ref->nama_kecamatan ?? 'Tanpa Kecamatan';
            $namaPetugas   = $ref->nama_petugas ?? ($row->ppl ?: 'Tanpa Petugas');

            $tanggal = \Carbon\Carbon::parse($row->upload_date)->format('Y-m-d');

            $cell = [
                'bku' => (int) $row->bku,
                'usaha_keluarga' => (int) $row->usaha_keluarga,
                'keluarga' => (int) $row->keluarga,
            ];

            if (!isset($progressTable[$namaKecamatan])) {
                $progressTable[$namaKecamatan] = [
                    'totals' => [],
                    'petugas' => [],
                ];
            }

            $progressTable[$namaKecamatan]['petugas'][$namaPetugas][$tanggal] = $cell;

            if (!isset($progressTable[$namaKecamatan]['totals'][$tanggal])) {
                $progressTable[$namaKecamatan]['totals'][$tanggal] = [
                    'bku' => 0,
                    'usaha_keluarga' => 0,
                    'keluarga' => 0,
                ];
            }

            $progressTable[$namaKecamatan]['totals'][$tanggal]['bku'] += $cell['bku'];
            $progressTable[$namaKecamatan]['totals'][$tanggal]['usaha_keluarga'] += $cell['usaha_keluarga'];
            $progressTable[$namaKecamatan]['totals'][$tanggal]['keluarga'] += $cell['keluarga'];
        }

        ksort($progressTable);

        $progressGrandTotals = [];

        foreach ($tanggalUploads as $tanggal) {
            $tanggalKey = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

            $progressGrandTotals[$tanggalKey] = [
                'bku' => 0,
                'usaha_keluarga' => 0,
                'keluarga' => 0,
            ];

            foreach ($progressTable as $group) {
                $item = $group['totals'][$tanggalKey] ?? null;

                if ($item) {
                    $progressGrandTotals[$tanggalKey]['bku'] += $item['bku'];
                    $progressGrandTotals[$tanggalKey]['usaha_keluarga'] += $item['usaha_keluarga'];
                    $progressGrandTotals[$tanggalKey]['keluarga'] += $item['keluarga'];
                }
            }
        }
        // | HITUNG PERSENTASE PERKEMBANGAN
        // | Dibandingkan dengan tanggal upload sebelumnya

        $hitungProgress = function ($data) {

            $tanggalKeys = collect($data)
                ->keys()
                ->sort()
                ->values();

            foreach ($tanggalKeys as $index => $tanggal) {

                /* TANGGAL PERTAMA        */

                if ($index === 0) {

                    $data[$tanggal]['percentage'] = [
                        'bku' => null,
                        'usaha_keluarga' => null,
                        'keluarga' => null,
                    ];

                    $data[$tanggal]['trend'] = [
                        'bku' => 'none',
                        'usaha_keluarga' => 'none',
                        'keluarga' => 'none',
                    ];

                    continue;
                }

                // | TANGGAL SEBELUMNYA

                $tanggalSebelumnya = $tanggalKeys[$index - 1];

                $sekarang = $data[$tanggal];

                $sebelumnya = $data[$tanggalSebelumnya];

                //  FUNCTION HITUNG PERSENTASE        

                $hitungPersen = function ($sekarang, $sebelumnya) {
                    if ($sebelumnya == 0) {
                        return null;
                    }

                    if ($sebelumnya == 0 && $sekarang > 0) {
                        return 100;
                    }

                    if ($sebelumnya > 0 && $sekarang == 0) {
                        return -100;
                    }

                    return (($sekarang - $sebelumnya) / $sebelumnya) * 100;
                };

                // | HITUNG BKU

                $persenBku = $hitungPersen(
                    $sekarang['bku'],
                    $sebelumnya['bku']
                );


                // | HITUNG USAHA KELUARGA

                $persenUsahaKeluarga = $hitungPersen(
                    $sekarang['usaha_keluarga'],
                    $sebelumnya['usaha_keluarga']
                );

                // | HITUNG KELUARGA
                $persenKeluarga = $hitungPersen(
                    $sekarang['keluarga'],
                    $sebelumnya['keluarga']
                );


                // |SIMPAN PERSENTASE

                $data[$tanggal]['percentage'] = [

                    'bku' => $persenBku,

                    'usaha_keluarga' => $persenUsahaKeluarga,

                    'keluarga' => $persenKeluarga,

                ];

                // | SIMPAN TREND

                $data[$tanggal]['trend'] = [

                    'bku' => $persenBku > 0
                        ? 'up'
                        : ($persenBku < 0 ? 'down' : 'same'),

                    'usaha_keluarga' => $persenUsahaKeluarga > 0
                        ? 'up'
                        : ($persenUsahaKeluarga < 0 ? 'down' : 'same'),

                    'keluarga' => $persenKeluarga > 0
                        ? 'up'
                        : ($persenKeluarga < 0 ? 'down' : 'same'),

                ];
            }


            return $data;
        };

        //  TERAPKAN PERSENTASE KE TOTAL KECAMATAN

        foreach ($progressTable as $namaKecamatan => &$kecamatanData) {

            // | TOTAL KECAMATAN

            $kecamatanData['totals'] = $hitungProgress(
                $kecamatanData['totals']
            );


            //  PER PETUGAS

            foreach (
                $kecamatanData['petugas']
                as $namaPetugas => &$petugasData
            ) {

                $petugasData = $hitungProgress(
                    $petugasData
                );
            }

            unset($petugasData);
        }

        unset($kecamatanData);
        //  UBAH JADI KUMULATIF (AKUMULASI SAMPAI TANGGAL TERSEBUT)

        $sortedTanggal = collect($tanggalUploads)
            ->map(fn($t) => \Carbon\Carbon::parse($t)->format('Y-m-d'))
            ->values()
            ->all();

        $tanggalUploads = collect($sortedTanggal)
            ->slice(-3)
            ->values()
            ->all();

        // ----- Cari upload sebelumnya (untuk perbandingan naik/turun) -----
        $previousUpload = UsahaUpload::query()
            ->when($latestUpload, fn($q) => $q->where('upload_date', '<', $latestUpload->upload_date))
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();

        $previousSums = $this->sumUsahaFieldsForUpload(optional($previousUpload)->id, $fields, $request);

        $summaryComparison = [
            'usaha_ditemukan_bku' => $grandTotals['jumlah_usaha_ditemukan_bku'] - $previousSums['jumlah_usaha_ditemukan_bku'],
            'usaha_ditutup_bku' => $grandTotals['jumlah_usaha_ditutup_bku'] - $previousSums['jumlah_usaha_ditutup_bku'],
            'usaha_ganda_bku' => $grandTotals['jumlah_usaha_ganda_bku'] - $previousSums['jumlah_usaha_ganda_bku'],
            'usaha_tidak_ditemukan_bku' => $grandTotals['jumlah_usaha_tidak_ditemukan_bku'] - $previousSums['jumlah_usaha_tidak_ditemukan_bku'],
            'usaha_baru_bku' => $grandTotals['jumlah_usaha_baru_bku'] - $previousSums['jumlah_usaha_baru_bku'],

            'usaha_ditemukan_keluarga' => $grandTotals['jumlah_usaha_ditemukan_usaha_keluarga'] - $previousSums['jumlah_usaha_ditemukan_usaha_keluarga'],
            'usaha_tutup_keluarga' => $grandTotals['jumlah_usaha_tutup_usaha_keluarga'] - $previousSums['jumlah_usaha_tutup_usaha_keluarga'],
            'usaha_ganda_keluarga' => $grandTotals['jumlah_usaha_ganda_usaha_keluarga'] - $previousSums['jumlah_usaha_ganda_usaha_keluarga'],
            'usaha_tidak_ditemukan_keluarga' => $grandTotals['jumlah_usaha_tidak_ditemukan_usaha_keluarga'] - $previousSums['jumlah_usaha_tidak_ditemukan_usaha_keluarga'],
            'usaha_baru_keluarga' => $grandTotals['jumlah_usaha_baru_usaha_keluarga'] - $previousSums['jumlah_usaha_baru_usaha_keluarga'],

            'keluarga_ditemukan' => $grandTotals['jumlah_keluarga_ditemukan'] - $previousSums['jumlah_keluarga_ditemukan'],
            'keluarga_meninggal' => $grandTotals['jumlah_keluarga_meninggal'] - $previousSums['jumlah_keluarga_meninggal'],
            'keluarga_tidak_eligible' => $grandTotals['jumlah_keluarga_tidak_eligible'] - $previousSums['jumlah_keluarga_tidak_eligible'],
            'keluarga_tidak_ditemui' => $grandTotals['jumlah_keluarga_tidak_ditemui'] - $previousSums['jumlah_keluarga_tidak_ditemui'],
            'keluarga_tidak_ditemukan' => $grandTotals['jumlah_keluarga_tidak_ditemukan'] - $previousSums['jumlah_keluarga_tidak_ditemukan'],
            'keluarga_baru' => $grandTotals['jumlah_keluarga_baru'] - $previousSums['jumlah_keluarga_baru'],

        ];

        // ----- Hitung persentase versi upload sebelumnya (untuk delta) -----
        $prevBkuNumerator = $previousSums['jumlah_usaha_ditemukan_bku'] + $previousSums['jumlah_usaha_baru_bku'];
        $prevBkuDenominator = $previousSums['jumlah_prelist_usaha'];
        $prevBkuValue = $prevBkuDenominator > 0 ? ($prevBkuNumerator / $prevBkuDenominator) * 100 : 0;

        $prevUsahaKeluargaNumerator = $previousSums['jumlah_usaha_ditemukan_usaha_keluarga'] + $previousSums['jumlah_usaha_baru_usaha_keluarga'];
        $prevUsahaKeluargaDenominator = $previousSums['jumlah_prelist_keluarga'];
        $prevUsahaKeluargaValue = $prevUsahaKeluargaDenominator > 0 ? ($prevUsahaKeluargaNumerator / $prevUsahaKeluargaDenominator) * 100 : 0;

        $prevTotalUsahaNumerator = $previousSums['jumlah_usaha_ditemukan_bku'] + $previousSums['jumlah_usaha_baru_bku']
            + $previousSums['jumlah_usaha_ditemukan_usaha_keluarga'] + $previousSums['jumlah_usaha_baru_usaha_keluarga'];
        $prevTotalUsahaDenominator = $previousSums['jumlah_prelist_keluarga'];
        $prevTotalUsahaValue = $prevTotalUsahaDenominator > 0 ? ($prevTotalUsahaNumerator / $prevTotalUsahaDenominator) * 100 : 0;

        $percentageComparison = [
            'bku' => $percentageSummary['bku']['value'] - $prevBkuValue,
            'usaha_keluarga' => $percentageSummary['usaha_keluarga']['value'] - $prevUsahaKeluargaValue,
            'total_usaha' => $percentageSummary['total_usaha']['value'] - $prevTotalUsahaValue,
        ];

        return view('usaha.index', [
            'data' => $data,
            'summary' => $summary,
            'percentageSummary' => $percentageSummary,

            'kabupatenOptions' => $kabupatenOptions,
            'slsOptions' => $slsOptions,
            'pplOptions' => $pplOptions,
            'pmlOptions' => $pmlOptions,

            'progressTable' => $progressTable,
            'tanggalUploads' => $tanggalUploads,
            'availableUploadDates' => $availableUploadDates,
            'dataGrouped' => $dataGrouped,
            'grandTotals' => $grandTotals,
            'progressGrandTotals' => $progressGrandTotals,
            'summaryComparison' => $summaryComparison,
            'percentageComparison' => $percentageComparison,
            'kecamatanOptions' => $kecamatanOptions,

            'wilkerStatMap' => $wilkerStatMap,
            'totalWilkerStat' => $totalWilkerStat,
            'totalST2023' => $totalST2023,
        ]);
    }
    private function sumUsahaFieldsForUpload(?int $uploadId, array $fields, Request $request): array
    {
        if (!$uploadId) {
            return array_fill_keys($fields, 0);
        }

        $query = Usaha::query()->where('upload_id', $uploadId);

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
        if ($request->filled('nama_kecamatan')) {
            $usernames = PetugasReference::query()
                ->where('nama_kecamatan', $request->nama_kecamatan)
                ->pluck('petugas_username')
                ->all();
            $query->whereIn('ppl', $usernames);
        }

        // Dedup dulu per kombinasi unik lewat SQL subquery, baru SUM
        $selectFields = implode(', ', array_map(fn($f) => "MAX($f) as $f", $fields));

        $deduped = DB::table(DB::raw("({$query->select(array_merge(
            ['id_wilayah', 'kd_kab', 'nama_sls', 'ppl', 'pml'],$fields
        ))->toSql()}) as dedup"))
            ->mergeBindings($query->getQuery())
            ->groupBy('id_wilayah', 'kd_kab', 'nama_sls', 'ppl', 'pml')
            ->selectRaw($selectFields)
            ->get();

        $sums = array_fill_keys($fields, 0);
        foreach ($deduped as $row) {
            foreach ($fields as $field) {
                $sums[$field] += $row->{$field};
            }
        }

        return $sums;
    }
}
