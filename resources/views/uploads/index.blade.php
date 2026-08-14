@extends('layouts.app')

@section('title', 'Upload Data - MAPS')
@section('header_title', 'Upload Data Assignment')
@section('header_subtitle', 'Upload Data Assignment Harian')

@section('content')

    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <div class="space-y-6">

        {{-- ================= PILIHAN JENIS DATA ================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-2">
            <div class="grid grid-cols-2 gap-2">

                <a href="{{ url()->current() }}"
                    class="flex items-center justify-center h-11 rounded-xl text-sm font-semibold transition
               {{ request('jenis', 'assignment') === 'assignment'
                   ? 'bg-sky-600 text-white shadow-sm'
                   : 'text-slate-500 hover:bg-slate-100' }}">
                    Assignment
                </a>

                <a href="{{ url()->current() }}?jenis=usaha"
                    class="flex items-center justify-center h-11 rounded-xl text-sm font-semibold transition
               {{ request('jenis') === 'usaha' ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' }}">
                    Usaha
                </a>

            </div>
        </div>

        @if (request('jenis', 'assignment') === 'usaha')

            {{-- ================= UPLOAD DATA USAHA ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">

                <h2 class="font-semibold text-slate-800 mb-1">
                    Upload Data Usaha
                </h2>

                <p class="text-xs text-slate-400 mb-5">
                    Upload data Usaha untuk digunakan pada Dashboard Usaha.
                </p>

                <form action="{{ route('usaha.import.store') }}" method="POST" enctype="multipart/form-data"
                    class="w-full">

                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

                        {{-- TANGGAL --}}
                        <div class="flex flex-col w-full">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                Tanggal Data
                            </label>
                            <div
                                class="h-11 w-full border border-slate-300 rounded-xl bg-white flex items-center px-3
                    focus-within:border-sky-500 focus-within:ring-2
                    focus-within:ring-sky-500/40 transition">
                                <input type="date" name="upload_date"
                                    value="{{ old('upload_date', now()->format('Y-m-d')) }}"
                                    class="w-full text-sm bg-transparent outline-none border-none p-0 m-0">
                            </div>
                        </div>

                        {{-- FILE --}}
                        <div class="flex flex-col w-full">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                File Excel / CSV
                            </label>

                            <div
                                class="h-11 w-full border border-dashed border-slate-300
                    rounded-xl bg-slate-50/60 flex items-center px-3
                    focus-within:border-sky-500 focus-within:ring-2
                    focus-within:ring-sky-500/40 transition">

                                <input type="file" name="file" required accept=".xlsx,.xls,.csv,.txt"
                                    class="w-full text-xs text-slate-500 outline-none border-none
                          p-0 m-0 cursor-pointer
                          file:mr-3 file:py-1.5 file:px-3 file:rounded-lg
                          file:border-0 file:text-xs file:font-semibold
                          file:bg-sky-100 file:text-sky-700
                          hover:file:bg-sky-200 file:cursor-pointer transition">
                            </div>
                        </div>

                        <div class="w-full">
                            <button type="submit"
                                class="h-11 w-full bg-sky-600 hover:bg-sky-700 text-white
                       text-sm font-semibold rounded-xl flex items-center
                       justify-center gap-2 shadow-sm hover:shadow transition">

                                Upload Usaha
                            </button>
                        </div>

                    </div>

                    <div class="mt-2">
                        <p class="text-[11px] text-slate-400">
                            Format: .xlsx, .xls, .csv, atau .txt (maks 20MB)
                        </p>
                    </div>

                </form>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">

                        <div>
                            <h2 class="font-semibold text-slate-800">
                                Riwayat Upload Usaha
                            </h2>

                            <p class="text-xs text-slate-400 mt-1">
                                Daftar file Usaha yang pernah diupload
                            </p>
                        </div>

                        <span class="text-xs text-slate-400">
                            {{ $usahaUploads->count() }} file
                        </span>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="min-w-full text-sm">

                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>

                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">
                                        Nama File
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">
                                        Tanggal Upload
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">
                                        Jumlah Data
                                    </th>

                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">
                                        Aksi
                                    </th>

                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">

                                @forelse($usahaUploads as $upload)
                                    <tr class="hover:bg-slate-50">

                                        <td class="px-6 py-4 text-slate-700 font-medium">
                                            {{ $upload->original_filename }}
                                        </td>

                                        <td class="px-6 py-4 text-slate-500">
                                            {{ $upload->upload_date?->format('d-m-Y') }}
                                        </td>

                                        <td class="px-6 py-4 text-slate-500">
                                            {{ number_format($upload->total_rows) }} baris
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-3">

                                                @if ($upload->file_path)
                                                    <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank"
                                                        class="text-sky-600 hover:text-sky-700 text-xs font-semibold">

                                                        Download
                                                    </a>
                                                @endif

                                                <form action="{{ route('usaha.upload.destroy', $upload) }}" method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus riwayat upload ini?');">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-700 text-xs font-semibold">

                                                        Hapus
                                                    </button>

                                                </form>

                                            </div>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-400">

                                            Belum ada riwayat upload Usaha.

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            {{-- ================= DASHBOARD USAHA ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">

                    <div>
                        <h2 class="font-semibold text-slate-800">
                            Data Usaha
                        </h2>

                        <p class="text-xs text-slate-400 mt-1">
                            Lihat data yang sudah diupload.
                        </p>
                    </div>

                    <a href="{{ route('usaha') }}"
                        class="text-sky-600 hover:text-sky-700 hover:underline
                          text-xs font-semibold">
                        Buka Dashboard Usaha →
                    </a>

                </div>

                <div class="px-6 py-10 text-center text-slate-400 text-sm">
                    Data Usaha akan ditampilkan pada Dashboard Usaha.
                </div>

            </div>
        @else
            {{-- ================= UPLOAD DATA PROGRESS HARIAN ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">

                <h2 class="font-semibold text-slate-800 mb-1">Upload File Baru (Progress Harian)</h2>
                <p class="text-xs text-slate-400 mb-5">Data harian yang baru akan otomatis dibandingkan dengan upload
                    sebelumnya.</p>

                <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data" class="w-full">
                    @csrf

                    {{-- Grid Utama Form --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

                        {{-- TANGGAL --}}
                        <div class="flex flex-col w-full">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tanggal
                                Data</label>
                            <div
                                class="h-11 w-full border border-slate-300 rounded-xl bg-white flex items-center px-3 focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/40 transition">
                                <input type="date" name="upload_date"
                                    value="{{ old('upload_date', now()->format('Y-m-d')) }}"
                                    class="w-full text-sm bg-transparent outline-none border-none p-0 m-0">
                            </div>
                        </div>

                        {{-- FILE --}}
                        <div class="flex flex-col w-full">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">File Excel /
                                CSV</label>
                            <div
                                class="h-11 w-full border border-dashed border-slate-300 rounded-xl bg-slate-50/60 flex items-center px-3 focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/40 transition">
                                <input type="file" name="file"
                                    class="w-full text-xs text-slate-500 outline-none border-none p-0 m-0 cursor-pointer
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                      file:text-xs file:font-semibold file:bg-sky-100 file:text-sky-700
                                      hover:file:bg-sky-200 file:cursor-pointer transition">
                            </div>
                        </div>

                        {{-- BUTTON --}}
                        <div class="w-full">
                            <button type="submit"
                                class="h-11 w-full bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 shadow-sm hover:shadow transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3" />
                                </svg>
                                Upload Sekarang
                            </button>
                        </div>

                    </div>

                    {{-- Teks Bantuan --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                        <div class="md:col-start-2">
                            <p class="text-[11px] text-slate-400">
                                Format: .xlsx, .xls, atau .csv (maks 20MB)
                            </p>
                        </div>
                    </div>
                </form>

                <div
                    class="mt-5 flex gap-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs leading-relaxed rounded-xl px-4 py-3.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p>
                        <strong>Catatan:</strong> Jika tanggal yang dipilih sudah pernah diupload sebelumnya, data lama
                        untuk tanggal
                        tersebut akan <strong>digantikan</strong> dengan file baru ini (bukan ditambah dobel). Data
                        tanggal-tanggal lain
                        tidak akan terpengaruh dan tetap tersimpan.
                    </p>
                </div>
            </div>

            {{-- ================= UPLOAD DATA REFERENSI PETUGAS (NAMA & KECAMATAN) ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="font-semibold text-slate-800 mb-1">Upload Data Referensi Petugas</h2>
                <p class="text-xs text-slate-400 leading-relaxed mb-5 max-w-3xl">
                    File berisi <strong class="text-slate-500">petugas_username</strong>, <strong
                        class="text-slate-500">Nama Petugas</strong>, <strong class="text-slate-500">Kode Kec</strong>,
                    dan <strong class="text-slate-500">Kecamatan</strong>. Data ini bersifat master/referensi (bukan
                    harian) — setiap upload akan
                    menggantikan seluruh data referensi sebelumnya. Data ini dipakai untuk menampilkan kolom "Nama" dan
                    "Kecamatan" di Dashboard, dan <strong class="text-slate-500">cukup diupload sekali saja</strong>
                    (upload ulang hanya kalau ada
                    petugas baru / pindah kecamatan). Saat ini tersimpan <strong
                        class="text-slate-600">{{ number_format($referenceCount) }} petugas</strong> di referensi.
                </p>

                <form action="{{ route('references.store') }}" method="POST" enctype="multipart/form-data"
                    class="grid md:grid-cols-3 gap-4 items-end">
                    @csrf

                    <div class="flex flex-col w-full md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">File
                            Referensi (Excel/CSV)</label>
                        <div
                            class="h-11 w-full border border-dashed border-slate-300 rounded-xl bg-slate-50/60 flex items-center px-3 focus-within:border-slate-400 focus-within:ring-2 focus-within:ring-slate-400/30 transition">
                            <input type="file" name="reference_file" required accept=".xlsx,.xls,.csv,.txt"
                                class="w-full text-xs text-slate-500 outline-none border-none p-0 m-0 cursor-pointer
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700
                                  hover:file:bg-slate-300 file:cursor-pointer transition">
                        </div>
                    </div>

                    <div class="w-full">
                        <button type="submit"
                            class="h-11 w-full border border-slate-300 hover:bg-slate-50 hover:border-slate-400 text-slate-700 text-sm font-semibold rounded-xl flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Update Referensi
                        </button>
                    </div>
                </form>
            </div>

            {{-- ================= RIWAYAT UPLOAD ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-800">Riwayat Upload</h2>
                    <span class="text-xs text-slate-400">{{ $uploads->count() }} file tersimpan</span>
                </div>

                <div class="table-scroll">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Tanggal Data</th>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Role</th>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Nama File</th>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Jumlah Baris</th>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Diupload Pada</th>
                                <th class="text-right px-6 py-4 font-bold whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($uploads as $upload)
                                <tr class="hover:bg-slate-50 transition-colors">

                                    {{-- Tanggal --}}
                                    <td class="px-6 py-3.5 font-medium text-slate-700 whitespace-nowrap">
                                        {{ $upload->upload_date->translatedFormat('d F Y') }}
                                    </td>

                                    {{-- Role --}}
                                    <td class="px-6 py-3.5">
                                        @if ($upload->petugas_role == 'Pengawas')
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                                                Pengawas
                                            </span>
                                        @elseif($upload->petugas_role == 'Pencacah')
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                                Pencacah
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                                -
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Nama File --}}
                                    <td class="px-6 py-3.5 text-slate-500">
                                        {{ $upload->original_filename }}
                                    </td>

                                    {{-- Jumlah Baris --}}
                                    <td class="px-6 py-3.5 text-slate-500">
                                        {{ number_format($upload->total_rows) }}
                                    </td>

                                    {{-- Diupload Pada --}}
                                    <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap">
                                        {{ $upload->created_at->translatedFormat('d M Y H:i') }}
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-6 py-3.5 text-right whitespace-nowrap">

                                        @if ($upload->file_path)
                                            <a href="{{ route('uploads.download', $upload) }}"
                                                class="text-sky-600 hover:text-sky-700 hover:underline text-xs font-semibold mr-4">
                                                Lihat File
                                            </a>
                                        @endif

                                        <a href="{{ route('dashboard', ['tanggal' => $upload->upload_date->format('Y-m-d')]) }}"
                                            class="text-sky-600 hover:text-sky-700 hover:underline text-xs font-semibold mr-4">
                                            Lihat di Dashboard
                                        </a>

                                        <form action="{{ route('uploads.destroy', $upload) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="text-red-500 hover:text-red-700 hover:underline text-xs font-semibold">
                                                Hapus
                                            </button>
                                        </form>

                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                        Belum ada data yang diupload.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= RIWAYAT UPLOAD REFERENSI ================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-800">Riwayat Upload Referensi</h2>
                    <span class="text-xs text-slate-400">{{ $referenceUploads->count() }} file tersimpan</span>
                </div>

                <div class="table-scroll">
                    <table class="w-full text-sm">

                        <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Role</th>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Nama File</th>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Jumlah Baris</th>
                                <th class="text-left px-6 py-4 font-bold whitespace-nowrap">Diupload Pada</th>
                                <th class="text-right px-6 py-4 font-bold whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse($referenceUploads as $item)
                                <tr class="hover:bg-slate-50 transition-colors">

                                    {{-- Role --}}
                                    <td class="px-6 py-3.5">
                                        @if ($item->petugas_role == 'Pengawas')
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                                                Pengawas
                                            </span>
                                        @elseif($item->petugas_role == 'Pencacah')
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                                Pencacah
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                                -
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Nama File --}}
                                    <td class="px-6 py-3.5 text-slate-500">
                                        {{ $item->original_filename }}
                                    </td>

                                    {{-- Jumlah Baris --}}
                                    <td class="px-6 py-3.5 text-slate-500">
                                        {{ number_format($item->total_rows) }}
                                    </td>

                                    {{-- Diupload Pada --}}
                                    <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap">
                                        {{ $item->created_at->translatedFormat('d M Y H:i') }}
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-6 py-3.5 text-right whitespace-nowrap">

                                        {{-- Lihat File --}}
                                        @if ($item->file_path)
                                            <a href="{{ route('reference-upload.download', $item) }}"
                                                class="text-sky-600 hover:text-sky-700 hover:underline text-xs font-semibold mr-4">
                                                Lihat File
                                            </a>
                                        @endif

                                        {{-- Hapus --}}
                                        <form action="{{ route('reference-upload.destroy', $item) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus riwayat upload referensi ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="text-red-500 hover:text-red-700 hover:underline text-xs font-semibold">
                                                Hapus
                                            </button>
                                        </form>

                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                        Belum ada riwayat upload referensi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>

        @endif

    @endsection
