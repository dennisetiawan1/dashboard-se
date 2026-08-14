<?php

namespace App\Http\Controllers;

use App\Models\Usaha;
use App\Models\UsahaUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UsahaImportController extends Controller
{
    /**
     * Halaman upload CSV
     */
    public function create()
    {
        return view('usaha.import');
    }

    /**
     * Import CSV
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:20480',
            ],
        ]);

        $file = $request->file('file');

        /*
        |--------------------------------------------------------------------------
        | SIMPAN FILE FISIK
        |--------------------------------------------------------------------------
        */

        $storedPath = $file->store('usaha', 'public');

        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with(
                'error',
                'File CSV tidak dapat dibaca.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER CSV
        |--------------------------------------------------------------------------
        */

        $headers = fgetcsv($handle, 0, ',');

        if (!$headers) {
            fclose($handle);

            return back()->with(
                'error',
                'Header CSV tidak ditemukan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BERSIHKAN HEADER
        |--------------------------------------------------------------------------
        */

        $headers = array_map(function ($header) {

            $header = trim($header);

            // Hilangkan BOM UTF-8
            $header = preg_replace(
                '/^\xEF\xBB\xBF/',
                '',
                $header
            );

            return $header;
        }, $headers);

        /*
        |--------------------------------------------------------------------------
        | HEADER YANG WAJIB ADA
        |--------------------------------------------------------------------------
        */

        $requiredHeaders = [
            'id_wilayah',
            'kd_kab',
            'nama_sls',

            'Jumlah UB Prelist Awal',
            'Jumlah UM Prelist Awal',
            'Jumlah UMK Prelist Awal',

            'Jumlah Usaha Ditemukan (BKU)',
            'Jumlah Usaha Ditutup (BKU)',
            'Jumlah Usaha Ganda (BKU)',
            'Jumlah Usaha Tidak Ditemukan (BKU)',
            'Jumlah Usaha Baru (BKU)',

            'Jumlah Usaha Ditemukan (Usaha Keluarga)',
            'Jumlah Usaha Tutup (Usaha Keluarga)',
            'Jumlah Usaha Ganda (Usaha Keluarga)',
            'Jumlah Usaha Tidak Ditemukan (Usaha Keluarga)',
            'Jumlah Usaha Baru (Usaha Keluarga)',

            'Jumlah Keluarga Ditemukan',
            'Jumlah Keluarga Meninggal',
            'Jumlah Keluarga Tidak Eligible',
            'Jumlah Keluarga Tidak Dapat Ditemui Sampai Akhir Pendataan',
            'Jumlah Keluarga Tidak Ditemukan',
            'Jumlah Keluarga Baru',

            'jumlah_prelist_usaha',
            'jumlah_usaha_realisasi',
            'jumlah_prelist_keluarga',
            'jumlah_keluarga_realisasi',

            'PPL',
            'PML',
            'last_update',
        ];

        /*
        |--------------------------------------------------------------------------
        | CEK HEADER
        |--------------------------------------------------------------------------
        */

        $missingHeaders = array_diff(
            $requiredHeaders,
            $headers
        );

        if (!empty($missingHeaders)) {

            fclose($handle);

            return back()->with(
                'error',
                'Format CSV tidak sesuai. Kolom yang tidak ditemukan: '
                    . implode(', ', $missingHeaders)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | BUAT RIWAYAT UPLOAD TERLEBIH DAHULU
            |--------------------------------------------------------------------------
            |
            | Ini penting karena ID dari record ini akan digunakan
            | sebagai upload_id pada tabel usaha.
            |
            */

            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
                'upload_date' => 'required|date',
            ]);

            // cari upload lama dengan tanggal yang sama (biar ga dobel)
            $oldUpload = UsahaUpload::where('upload_date', $request->upload_date)->first();

            if ($oldUpload) {
                // hapus file lama dari storage kalau ada
                if ($oldUpload->file_path && Storage::disk('public')->exists($oldUpload->file_path)) {
                    Storage::disk('public')->delete($oldUpload->file_path);
                }
                $oldUpload->delete();
            }

            $usahaUpload = UsahaUpload::create([
                'upload_date' => $request->upload_date,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'total_rows' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | COUNTER
            |--------------------------------------------------------------------------
            */

            $inserted = 0;

            /*
            |--------------------------------------------------------------------------
            | BACA SEMUA BARIS CSV
            |--------------------------------------------------------------------------
            */

            while (($row = fgetcsv($handle, 0, ',')) !== false) {

                /*
                |--------------------------------------------------------------------------
                | LEWATI BARIS KOSONG
                |--------------------------------------------------------------------------
                */

                if (
                    count(
                        array_filter(
                            $row,
                            fn($value) =>
                            trim((string) $value) !== ''
                        )
                    ) === 0
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | SESUAIKAN JUMLAH KOLOM
                |--------------------------------------------------------------------------
                */

                $row = array_pad(
                    $row,
                    count($headers),
                    null
                );

                $row = array_slice(
                    $row,
                    0,
                    count($headers)
                );

                /*
                |--------------------------------------------------------------------------
                | GABUNG HEADER + DATA
                |--------------------------------------------------------------------------
                */

                $csv = array_combine(
                    $headers,
                    $row
                );

                /*
                |--------------------------------------------------------------------------
                | MAPPING CSV → DATABASE
                |--------------------------------------------------------------------------
                */

                $data = [

                    /*
                    |--------------------------------------------------------------------------
                    | WILAYAH
                    |--------------------------------------------------------------------------
                    */

                    'id_wilayah' =>
                    $this->number(
                        $csv['id_wilayah']
                    ),

                    'kd_kab' =>
                    $this->number(
                        $csv['kd_kab']
                    ),

                    'nama_sls' =>
                    $this->text(
                        $csv['nama_sls']
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | PRELIST AWAL
                    |--------------------------------------------------------------------------
                    */

                    'jumlah_ub_prelist_awal' =>
                    $this->number(
                        $csv['Jumlah UB Prelist Awal']
                    ),

                    'jumlah_um_prelist_awal' =>
                    $this->number(
                        $csv['Jumlah UM Prelist Awal']
                    ),

                    'jumlah_umk_prelist_awal' =>
                    $this->number(
                        $csv['Jumlah UMK Prelist Awal']
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | USAHA BKU
                    |--------------------------------------------------------------------------
                    */

                    'jumlah_usaha_ditemukan_bku' =>
                    $this->number(
                        $csv['Jumlah Usaha Ditemukan (BKU)']
                    ),

                    'jumlah_usaha_ditutup_bku' =>
                    $this->number(
                        $csv['Jumlah Usaha Ditutup (BKU)']
                    ),

                    'jumlah_usaha_ganda_bku' =>
                    $this->number(
                        $csv['Jumlah Usaha Ganda (BKU)']
                    ),

                    'jumlah_usaha_tidak_ditemukan_bku' =>
                    $this->number(
                        $csv['Jumlah Usaha Tidak Ditemukan (BKU)']
                    ),

                    'jumlah_usaha_baru_bku' =>
                    $this->number(
                        $csv['Jumlah Usaha Baru (BKU)']
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | USAHA KELUARGA
                    |--------------------------------------------------------------------------
                    */

                    'jumlah_usaha_ditemukan_usaha_keluarga' =>
                    $this->number(
                        $csv['Jumlah Usaha Ditemukan (Usaha Keluarga)']
                    ),

                    'jumlah_usaha_tutup_usaha_keluarga' =>
                    $this->number(
                        $csv['Jumlah Usaha Tutup (Usaha Keluarga)']
                    ),

                    'jumlah_usaha_ganda_usaha_keluarga' =>
                    $this->number(
                        $csv['Jumlah Usaha Ganda (Usaha Keluarga)']
                    ),

                    'jumlah_usaha_tidak_ditemukan_usaha_keluarga' =>
                    $this->number(
                        $csv['Jumlah Usaha Tidak Ditemukan (Usaha Keluarga)']
                    ),

                    'jumlah_usaha_baru_usaha_keluarga' =>
                    $this->number(
                        $csv['Jumlah Usaha Baru (Usaha Keluarga)']
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | KELUARGA
                    |--------------------------------------------------------------------------
                    */

                    'jumlah_keluarga_ditemukan' =>
                    $this->number(
                        $csv['Jumlah Keluarga Ditemukan']
                    ),

                    'jumlah_keluarga_meninggal' =>
                    $this->number(
                        $csv['Jumlah Keluarga Meninggal']
                    ),

                    'jumlah_keluarga_tidak_eligible' =>
                    $this->number(
                        $csv['Jumlah Keluarga Tidak Eligible']
                    ),

                    'jumlah_keluarga_tidak_ditemui' =>
                    $this->number(
                        $csv['Jumlah Keluarga Tidak Dapat Ditemui Sampai Akhir Pendataan']
                    ),

                    'jumlah_keluarga_tidak_ditemukan' =>
                    $this->number(
                        $csv['Jumlah Keluarga Tidak Ditemukan']
                    ),

                    'jumlah_keluarga_baru' =>
                    $this->number(
                        $csv['Jumlah Keluarga Baru']
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL
                    |--------------------------------------------------------------------------
                    */

                    'jumlah_prelist_usaha' =>
                    $this->number(
                        $csv['jumlah_prelist_usaha']
                    ),

                    'jumlah_usaha_realisasi' =>
                    $this->number(
                        $csv['jumlah_usaha_realisasi']
                    ),

                    'jumlah_prelist_keluarga' =>
                    $this->number(
                        $csv['jumlah_prelist_keluarga']
                    ),

                    'jumlah_keluarga_realisasi' =>
                    $this->number(
                        $csv['jumlah_keluarga_realisasi']
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | PETUGAS
                    |--------------------------------------------------------------------------
                    */

                    'ppl' =>
                    $this->text(
                        $csv['PPL']
                    ),

                    'pml' =>
                    $this->text(
                        $csv['PML']
                    ),

                    'last_update' =>
                    $this->text(
                        $csv['last_update']
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | RELASI UPLOAD
                    |--------------------------------------------------------------------------
                    */

                    'upload_id' => $usahaUpload->id,
                ];

                /*
                |--------------------------------------------------------------------------
                | INSERT DATA USAHA
                |--------------------------------------------------------------------------
                */

                Usaha::create($data);

                $inserted++;
            }

            /*
            |--------------------------------------------------------------------------
            | TUTUP FILE
            |--------------------------------------------------------------------------
            */

            fclose($handle);

            /*
            |--------------------------------------------------------------------------
            | UPDATE TOTAL ROWS
            |--------------------------------------------------------------------------
            */

            $usahaUpload->update([
                'total_rows' => $inserted,
            ]);

            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | REDIRECT
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('usaha')
                ->with(
                    'success',
                    "Berhasil mengimport {$inserted} baris data Usaha."
                );
        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | TUTUP HANDLE JIKA MASIH TERBUKA
            |--------------------------------------------------------------------------
            */

            if (is_resource($handle)) {
                fclose($handle);
            }

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK
            |--------------------------------------------------------------------------
            */

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE YANG SUDAH DISIMPAN
            |--------------------------------------------------------------------------
            */

            if (!empty($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }

            return back()->with(
                'error',
                'Import gagal: ' . $e->getMessage()
            );
        }
    }

    /**
     * Hapus riwayat upload beserta semua data Usaha
     * yang berasal dari upload tersebut.
     */
    public function destroy(UsahaUpload $usahaUpload)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | HAPUS DATA USAHA
            |--------------------------------------------------------------------------
            */

            Usaha::where(
                'upload_id',
                $usahaUpload->id
            )->delete();

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE CSV
            |--------------------------------------------------------------------------
            */

            if ($usahaUpload->file_path) {

                Storage::disk('public')->delete(
                    $usahaUpload->file_path
                );
            }

            /*
            |--------------------------------------------------------------------------
            | HAPUS RIWAYAT UPLOAD
            |--------------------------------------------------------------------------
            */

            $usahaUpload->delete();

            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            DB::commit();

            return back()->with(
                'success',
                'Riwayat upload dan seluruh data Usaha berhasil dihapus.'
            );
        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Gagal menghapus upload: ' . $e->getMessage()
            );
        }
    }

    /**
     * Helper angka
     */
    private function number($value)
    {
        if ($value === null) {
            return 0;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $value = str_replace(',', '.', $value);

        return is_numeric($value)
            ? (int) round((float) $value)
            : 0;
    }

    /**
     * Helper text
     */
    private function text($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}
