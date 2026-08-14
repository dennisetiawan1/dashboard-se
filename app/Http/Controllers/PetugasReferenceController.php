<?php

namespace App\Http\Controllers;

use App\Models\PetugasReference;
use App\Models\ReferenceUpload;      
use App\Support\SpreadsheetReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PetugasReferenceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reference_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:20480'],
        ], [], [
            'reference_file' => 'File referensi petugas',
        ]);

        $file = $request->file('reference_file');
        $filename = time().'_'.$file->getClientOriginalName();

            $path = $file->storeAs(
                'reference_uploads',
                $filename,
                'public'
            );
        $extension = $file->getClientOriginalExtension();
        [$headers, $rows] = SpreadsheetReader::read($file->getRealPath(), $extension);

        if (empty($rows)) {
            return back()->withErrors(['reference_file' => 'File referensi tidak terbaca atau kosong.']);
        }

        $map = $this->buildColumnMap($headers);

        $role = null;

        foreach ($rows as $row) {
            $role = $this->val($row, $map, 'petugas_role');

            if (!empty($role)) {
                break;
            }
        }

        if (! isset($map['petugas_username'])) {
            return back()->withErrors([
                'reference_file' => 'Kolom petugas_username tidak ditemukan di file. '
                    .'Kolom yang terbaca dari file Anda: '.implode(', ', $headers),
            ]);
        }

        $count = 0;

       DB::transaction(function () use ($rows, $map, &$count, $role, $file, $path) {

    $now = now();
    $batch = [];

    foreach ($rows as $row) {
        $username = $this->val($row, $map, 'petugas_username');

        if (!$username) {
            continue;
        }

        $batch[] = [
            'petugas_username' => $username,
            'nama_petugas'     => $this->val($row, $map, 'nama_petugas'),
            'kode_kecamatan'   => $this->val($row, $map, 'kode_kec'),
            'nama_kecamatan'   => $this->val($row, $map, 'nama_kecamatan'),
            'petugas_role'     => $role,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];

        $count++;
    }

            foreach (array_chunk($batch, 500) as $chunk) {
                PetugasReference::upsert(
                $chunk,
                ['petugas_username'],
                [
                    'nama_petugas',
                    'kode_kecamatan',
                    'nama_kecamatan',
                    'petugas_role',
                    'updated_at',
                ]
            );
        }
            ReferenceUpload::create([
                'petugas_role'      => $role,
                'original_filename' => $file->getClientOriginalName(),
                'file_path'         => $path,
                'total_rows'        => $count,
            ]);
        });

        return redirect()->route('uploads.index')
            ->with('success', "Data referensi petugas berhasil diupdate ({$count} petugas).");
    }

    private function buildColumnMap(array $headers): array
    {
        $targets = [
            'petugas_username' => ['petugas_username', 'username'],
            'nama_petugas'     => ['nama_petugas', 'nama'],
            'kode_kec'         => ['kode_kec', 'kode_kecamatan'],
            'nama_kecamatan'   => ['nama_kecamatan', 'kecamatan'],
            'petugas_role'     => ['petugas_role', 'role'],
        ];

        $map = [];
        foreach ($targets as $target => $aliases) {
            foreach ($headers as $header) {
                foreach ($aliases as $alias) {
                    if ($header === $alias || str_contains($header, $alias)) {
                        $map[$target] = $header;
                        continue 3;
                    }
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
}