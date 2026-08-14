@extends('layouts.app')

@section('title', 'Dashboard Usaha')

@section('content')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('usahaColumns', {
                id_wilayah: true,
                kd_kab: true,
                nama_sls: true,
                ub_prelist: true,
                um_prelist: true,
                umk_prelist: true,
                usaha_ditemukan_bku: true,
                usaha_ditutup_bku: true,
                usaha_ganda_bku: true,
                usaha_tidak_ditemukan_bku: true,
                usaha_baru_bku: true,
                usaha_ditemukan_keluarga: true,
                usaha_tutup_keluarga: true,
                usaha_ganda_keluarga: true,
                usaha_tidak_ditemukan_keluarga: true,
                usaha_baru_keluarga: true,
                keluarga_ditemukan: true,
                keluarga_meninggal: true,
                keluarga_tidak_eligible: true,
                keluarga_tidak_ditemui: true,
                keluarga_tidak_ditemukan: true,
                keluarga_baru: true,
                prelist_usaha: true,
                usaha_realisasi: true,
                prelist_keluarga: true,
                keluarga_realisasi: true,
                ppl: true,
                pml: true,
                last_update: true,

                draft: {},

                keys() {
                    return Object.keys(this).filter(k => typeof this[k] === 'boolean');
                },
                visibleCount() {
                    return this.keys().filter(k => this[k]).length;
                },

                initDraft() {
                    this.keys().forEach(k => this.draft[k] = this[k]);
                },
                draftShowAll() {
                    this.keys().forEach(k => this.draft[k] = true);
                },
                draftHideAll() {
                    this.keys().forEach(k => this.draft[k] = false);
                },
                applyDraft() {
                    this.keys().forEach(k => this[k] = this.draft[k]);
                }
            });

            Alpine.store('usahaColumns').initDraft();
        });
    </script>

    <div class="space-y-6">

        {{-- =========================================================
         SWITCH DASHBOARD
    ========================================================== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-2">

            <div class="flex gap-2">

                <a href="{{ route('dashboard') }}"
                    class="flex-1 text-center rounded-xl px-5 py-3 text-sm font-semibold
                       bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    Assignment
                </a>

                <a href="{{ route('usaha') }}"
                    class="flex-1 text-center rounded-xl px-5 py-3 text-sm font-semibold
                       bg-sky-600 text-white shadow-sm">
                    Usaha
                </a>

            </div>

        </div>


        {{-- =========================================================
         HEADER
    ========================================================== --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Dashboard Usaha
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Ringkasan data usaha dan keluarga berdasarkan data yang tersedia.
            </p>
        </div>

        {{-- =========================================================
     KPI PERSENTASE
========================================================== --}}

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- =====================================================
         BKU
    ====================================================== --}}
            <div
                class="relative overflow-hidden rounded-2xl
                bg-gradient-to-r from-sky-500 to-cyan-600
                p-6 shadow-md">

                <div class="absolute -right-12 -bottom-12
                    w-44 h-44 rounded-full bg-white/10">
                </div>

                <div class="relative z-10">

                    <p class="text-sky-100 text-xs font-semibold
                      uppercase tracking-widest">
                        Persentase BKU
                    </p>

                    <div class="mt-2 text-4xl font-extrabold text-white">
                        {{ number_format($percentageSummary['bku']['value'], 2) }}%
                    </div>

                    {{-- <p class="mt-3 text-sm text-sky-100">
                        BKU ditemukan + BKU baru dari jumlah prelist usaha.
                    </p> --}}

                    <div class="mt-2 text-sm font-semibold text-white">
                        {{ number_format($percentageSummary['bku']['numerator']) }}
                        BKU dari
                        {{ number_format($percentageSummary['bku']['denominator']) }}
                        prelist usaha
                    </div>

                </div>

            </div>


            {{-- =====================================================
         USAHA KELUARGA
    ====================================================== --}}
            <div
                class="relative overflow-hidden rounded-2xl
                bg-gradient-to-r from-emerald-500 to-green-700
                p-6 shadow-md">

                <div class="absolute -right-12 -bottom-12
                    w-44 h-44 rounded-full bg-white/10">
                </div>

                <div class="relative z-10">

                    <p class="text-emerald-100 text-xs font-semibold
                      uppercase tracking-widest">
                        Persentase Usaha Keluarga
                    </p>

                    <div class="mt-2 text-4xl font-extrabold text-white">
                        {{ number_format($percentageSummary['usaha_keluarga']['value'], 2) }}%
                    </div>

                    {{-- <p class="mt-3 text-sm text-emerald-100">
                        Usaha keluarga ditemukan + usaha keluarga baru
                        dari jumlah prelist keluarga.
                    </p> --}}

                    <div class="mt-2 text-sm font-semibold text-white">
                        {{ number_format($percentageSummary['usaha_keluarga']['numerator']) }}
                        Usaha Keluarga dari
                        {{ number_format($percentageSummary['usaha_keluarga']['denominator']) }}
                        prelist keluarga
                    </div>

                </div>

            </div>


            {{-- =====================================================
         TOTAL USAHA
    ====================================================== --}}
            <div
                class="relative overflow-hidden rounded-2xl
                bg-gradient-to-r from-violet-500 to-purple-700
                p-6 shadow-md">

                <div class="absolute -right-12 -bottom-12
                    w-44 h-44 rounded-full bg-white/10">
                </div>

                <div class="relative z-10">

                    <p class="text-violet-100 text-xs font-semibold
                      uppercase tracking-widest">
                        Persentase Total Usaha
                    </p>

                    <div class="mt-2 text-4xl font-extrabold text-white">
                        {{ number_format($percentageSummary['total_usaha']['value'], 2) }}%
                    </div>

                    {{-- <p class="mt-3 text-sm text-violet-100">
                        BKU + usaha keluarga ditemukan dan baru
                        dari jumlah prelist keluarga.
                    </p> --}}

                    <div class="mt-2 text-sm font-semibold text-white">
                        {{ number_format($percentageSummary['total_usaha']['numerator']) }}
                        BKU dan usaha keluarga dari
                        {{ number_format($percentageSummary['total_usaha']['denominator']) }}
                        prelist keluarga
                    </div>

                </div>

            </div>

        </div>

        {{-- =========================================================
         STATUS USAHA BKU
    ========================================================== --}}
        <div>

            <div class="mb-4">

                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide">
                    Status Usaha BKU
                </h2>

            </div>


            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-5">


                {{-- Ditemukan --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />

                        </svg>

                    </div>

                    <div class="text-xs font-extrabold uppercase tracking-wide text-emerald-600">
                        Ditemukan
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['usaha_ditemukan_bku']) }}
                    </div>

                </div>


                {{-- Ditutup --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />

                        </svg>

                    </div>

                    <div class="text-xs font-extrabold uppercase tracking-wide text-red-600">
                        Ditutup
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['usaha_ditutup_bku']) }}
                    </div>

                </div>


                {{-- Ganda --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8m-8 4h8m-8 4h5" />

                        </svg>

                    </div>

                    <div class="text-xs font-extrabold uppercase tracking-wide text-amber-600">
                        Ganda
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['usaha_ganda_bku']) }}
                    </div>

                </div>


                {{-- Tidak Ditemukan --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />

                        </svg>

                    </div>

                    <div class="text-xs font-extrabold uppercase tracking-wide text-slate-600">
                        Tidak Ditemukan
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['usaha_tidak_ditemukan_bku']) }}
                    </div>

                </div>


                {{-- Baru --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />

                        </svg>

                    </div>

                    <div class="text-xs font-extrabold uppercase tracking-wide text-blue-600">
                        Usaha Baru
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['usaha_baru_bku']) }}
                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
         STATUS KELUARGA
    ========================================================== --}}
        <div>

            <div class="mb-4">

                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide">
                    Status Keluarga
                </h2>

            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-emerald-600">
                        Ditemukan
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_ditemukan']) }}
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-red-600">
                        Meninggal
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_meninggal']) }}
                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-amber-600">
                        Tidak Eligible
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_tidak_eligible']) }}
                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-orange-600">
                        Tidak Ditemui
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_tidak_ditemui']) }}
                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-slate-600">
                        Tidak Ditemukan
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_tidak_ditemukan']) }}
                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-blue-600">
                        Keluarga Baru
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_baru']) }}
                    </div>

                </div>

            </div>

        </div>
        {{-- =========================================================
        TABEL 1 - PERKEMBANGAN DATA BERDASARKAN TANGGAL
        ========================================================== --}}

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

            <div class="px-5 py-4 border-b border-slate-200">

                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                    Perkembangan Data Usaha
                </h3>

                <p class="mt-1 text-xs text-slate-400">
                    Perbandingan jumlah BKU, Usaha Keluarga, dan Keluarga berdasarkan tanggal upload. Klik baris kecamatan
                    untuk melihat rincian per petugas.
                </p>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-slate-100">

                        {{-- BARIS 1: NAMA TANGGAL --}}
                        <tr>

                            <th rowspan="2"
                                class="sticky left-0 z-10 bg-slate-100 px-5 py-4 text-left
                               text-[11px] uppercase tracking-wider text-slate-500
                               whitespace-nowrap border-r border-slate-200 align-bottom">
                                Kecamatan / Petugas
                            </th>

                            @foreach ($tanggalUploads as $tanggal)
                                <th colspan="3"
                                    class="px-6 py-3 text-center text-[11px]
                                   uppercase tracking-wider text-slate-500
                                   whitespace-nowrap border-l border-slate-200">
                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                                </th>
                            @endforeach

                        </tr>

                        {{-- BARIS 2: SUB-KOLOM BKU / USAHA KELUARGA / KELUARGA --}}
                        <tr>

                            @foreach ($tanggalUploads as $tanggal)
                                <th
                                    class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-slate-400 border-l border-slate-200 whitespace-nowrap">
                                    BKU
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">
                                    Usaha Keluarga
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">
                                    Keluarga
                                </th>
                            @endforeach

                        </tr>

                    </thead>


                    @forelse ($progressTable as $kecamatan => $group)

                        <tbody x-data="{ open: false }" class="divide-y divide-slate-100">

                            {{-- BARIS KECAMATAN (KLIK UNTUK EXPAND) --}}
                            <tr @click="open = !open" class="hover:bg-slate-50 cursor-pointer transition-colors">

                                {{-- NAMA KECAMATAN --}}
                                <td
                                    class="sticky left-0 z-10 bg-white px-5 py-4
                                   font-semibold text-slate-700 whitespace-nowrap
                                   border-r border-slate-200">
                                    <span x-text="open ? '▾' : '▸'" class="mr-2 text-slate-400"></span>
                                    {{ $kecamatan }}
                                </td>

                                {{-- TOTAL PER TANGGAL: 3 KOLOM --}}
                                @foreach ($tanggalUploads as $tanggal)
                                    @php
                                        $tanggalKey = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

                                        $item = $group['totals'][$tanggalKey] ?? null;
                                    @endphp

                                    <td
                                        class="px-3 py-4 text-center whitespace-nowrap font-semibold text-slate-800 border-l border-slate-100">
                                        {{ $item ? number_format($item['bku']) : '-' }}
                                    </td>
                                    <td class="px-3 py-4 text-center whitespace-nowrap text-slate-600">
                                        {{ $item ? number_format($item['usaha_keluarga']) : '-' }}
                                    </td>
                                    <td class="px-3 py-4 text-center whitespace-nowrap text-slate-600">
                                        {{ $item ? number_format($item['keluarga']) : '-' }}
                                    </td>
                                @endforeach

                            </tr>

                            {{-- BARIS PETUGAS (MUNCUL SAAT KECAMATAN DI-KLIK) --}}
                            @foreach ($group['petugas'] as $namaPetugas => $tanggalData)
                                <tr x-show="open" x-cloak class="bg-slate-50/60">

                                    {{-- NAMA PETUGAS --}}
                                    <td
                                        class="sticky left-0 z-10 bg-slate-50/60 pl-10 pr-5 py-3
                                       text-slate-600 whitespace-nowrap
                                       border-r border-slate-200">
                                        {{ $namaPetugas }}
                                    </td>

                                    {{-- DATA PER TANGGAL: 3 KOLOM --}}
                                    @foreach ($tanggalUploads as $tanggal)
                                        @php
                                            $tanggalKey = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

                                            $item = $tanggalData[$tanggalKey] ?? null;
                                        @endphp

                                        <td
                                            class="px-3 py-3 text-center whitespace-nowrap text-sm font-semibold text-slate-700 border-l border-slate-100">
                                            {{ $item ? number_format($item['bku']) : '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center whitespace-nowrap text-sm text-slate-500">
                                            {{ $item ? number_format($item['usaha_keluarga']) : '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center whitespace-nowrap text-sm text-slate-500">
                                            {{ $item ? number_format($item['keluarga']) : '-' }}
                                        </td>
                                    @endforeach

                                </tr>
                            @endforeach

                        </tbody>

                    @empty

                        <tbody>
                            <tr>

                                <td colspan="{{ count($tanggalUploads) * 3 + 1 }}"
                                    class="px-5 py-10 text-center text-slate-400">
                                    Belum ada data perkembangan Usaha.
                                </td>

                            </tr>
                        </tbody>

                    @endforelse

                </table>

            </div>

        </div>

        {{-- =========================================================
        FILTER KOLOM TABEL USAHA
        ========================================================== --}}

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            <div x-data="{ showFilter: false }">

                <button @click="showFilter = !showFilter; if (showFilter) $store.usahaColumns.initDraft()"
                    class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-slate-50 transition">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700">
                            Tampilkan / Sembunyikan Kolom
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            <span x-text="$store.usahaColumns.visibleCount()"></span> dari
                            <span x-text="$store.usahaColumns.keys().length"></span> kolom aktif
                        </p>
                    </div>

                    <span x-text="showFilter ? '▾' : '▸'" class="text-slate-400 text-lg"></span>

                </button>

                <div x-show="showFilter" x-cloak class="px-6 pb-5 border-t border-slate-100 pt-4">

                    <div class="flex gap-2 mb-4">
                        <button @click="$store.usahaColumns.draftShowAll()"
                            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 transition">
                            Tampilkan Semua
                        </button>
                        <button @click="$store.usahaColumns.draftHideAll()"
                            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                            Sembunyikan Semua
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-x-4 gap-y-2 text-sm text-slate-600">

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.id_wilayah"
                                class="rounded border-slate-300 text-sky-600">
                            ID Wilayah
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.kd_kab"
                                class="rounded border-slate-300 text-sky-600">
                            Kode Kabupaten
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.nama_sls"
                                class="rounded border-slate-300 text-sky-600">
                            Nama SLS
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.ub_prelist"
                                class="rounded border-slate-300 text-sky-600">
                            UB Prelist Awal
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.um_prelist"
                                class="rounded border-slate-300 text-sky-600">
                            UM Prelist Awal
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.umk_prelist"
                                class="rounded border-slate-300 text-sky-600">
                            UMK Prelist Awal
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_ditemukan_bku"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Ditemukan (BKU)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_ditutup_bku"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Ditutup (BKU)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_ganda_bku"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Ganda (BKU)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_tidak_ditemukan_bku"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Tidak Ditemukan (BKU)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_baru_bku"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Baru (BKU)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_ditemukan_keluarga"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Ditemukan (Keluarga)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_tutup_keluarga"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Tutup (Keluarga)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_ganda_keluarga"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Ganda (Keluarga)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_tidak_ditemukan_keluarga"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Tidak Ditemukan (Keluarga)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_baru_keluarga"
                                class="rounded border-slate-300 text-sky-600">
                            Usaha Baru (Keluarga)
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.keluarga_ditemukan"
                                class="rounded border-slate-300 text-sky-600">
                            Keluarga Ditemukan
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.keluarga_meninggal"
                                class="rounded border-slate-300 text-sky-600">
                            Keluarga Meninggal
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.keluarga_tidak_eligible"
                                class="rounded border-slate-300 text-sky-600">
                            Keluarga Tidak Eligible
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.keluarga_tidak_ditemui"
                                class="rounded border-slate-300 text-sky-600">
                            Keluarga Tidak Dapat Ditemui
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.keluarga_tidak_ditemukan"
                                class="rounded border-slate-300 text-sky-600">
                            Keluarga Tidak Ditemukan
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.keluarga_baru"
                                class="rounded border-slate-300 text-sky-600">
                            Keluarga Baru
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.prelist_usaha"
                                class="rounded border-slate-300 text-sky-600">
                            Jumlah Prelist Usaha
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.usaha_realisasi"
                                class="rounded border-slate-300 text-sky-600">
                            Jumlah Usaha Realisasi
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.prelist_keluarga"
                                class="rounded border-slate-300 text-sky-600">
                            Jumlah Prelist Keluarga
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.keluarga_realisasi"
                                class="rounded border-slate-300 text-sky-600">
                            Jumlah Keluarga Realisasi
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.ppl"
                                class="rounded border-slate-300 text-sky-600">
                            PPL
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.pml"
                                class="rounded border-slate-300 text-sky-600">
                            PML
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="$store.usahaColumns.draft.last_update"
                                class="rounded border-slate-300 text-sky-600">
                            Last Update
                        </label>

                    </div>

                    <div class="flex justify-end mt-5 pt-4 border-t border-slate-100">
                        <button @click="$store.usahaColumns.applyDraft(); showFilter = false"
                            class="text-xs font-semibold px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700 transition">
                            Terapkan Filter
                        </button>
                    </div>

                </div>

            </div>

        </div>

        {{-- =========================================================
            TABEL USAHA
        ========================================================== --}}

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

            <div class="px-5 py-4 border-b border-slate-200">

                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                    Data Usaha
                </h3>

            </div>

            <div class="overflow-x-auto">

                <table x-data="{}" class="w-full text-sm">

                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">

                        <tr>

                            <th class="text-left px-5 py-4 whitespace-nowrap">#</th>

                            <th x-show="$store.usahaColumns.id_wilayah" x-cloak
                                class="text-left px-5 py-4 whitespace-nowrap">ID Wilayah</th>
                            <th x-show="$store.usahaColumns.kd_kab" x-cloak class="text-left px-5 py-4 whitespace-nowrap">
                                Kode Kabupaten</th>
                            <th x-show="$store.usahaColumns.nama_sls" x-cloak
                                class="text-left px-5 py-4 whitespace-nowrap">Nama SLS</th>
                            <th x-show="$store.usahaColumns.ub_prelist" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">UB Prelist Awal</th>
                            <th x-show="$store.usahaColumns.um_prelist" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">UM Prelist Awal</th>
                            <th x-show="$store.usahaColumns.umk_prelist" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">UMK Prelist Awal</th>
                            <th x-show="$store.usahaColumns.usaha_ditemukan_bku" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Ditemukan (BKU)</th>
                            <th x-show="$store.usahaColumns.usaha_ditutup_bku" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Ditutup (BKU)</th>
                            <th x-show="$store.usahaColumns.usaha_ganda_bku" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Ganda (BKU)</th>
                            <th x-show="$store.usahaColumns.usaha_tidak_ditemukan_bku" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Tidak Ditemukan (BKU)</th>
                            <th x-show="$store.usahaColumns.usaha_baru_bku" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Baru (BKU)</th>
                            <th x-show="$store.usahaColumns.usaha_ditemukan_keluarga" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Ditemukan (Usaha Keluarga)</th>
                            <th x-show="$store.usahaColumns.usaha_tutup_keluarga" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Tutup (Usaha Keluarga)</th>
                            <th x-show="$store.usahaColumns.usaha_ganda_keluarga" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Ganda (Usaha Keluarga)</th>
                            <th x-show="$store.usahaColumns.usaha_tidak_ditemukan_keluarga" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Tidak Ditemukan (Usaha Keluarga)</th>
                            <th x-show="$store.usahaColumns.usaha_baru_keluarga" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Usaha Baru (Usaha Keluarga)</th>
                            <th x-show="$store.usahaColumns.keluarga_ditemukan" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Keluarga Ditemukan</th>
                            <th x-show="$store.usahaColumns.keluarga_meninggal" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Keluarga Meninggal</th>
                            <th x-show="$store.usahaColumns.keluarga_tidak_eligible" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Keluarga Tidak Eligible</th>
                            <th x-show="$store.usahaColumns.keluarga_tidak_ditemui" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Keluarga Tidak Dapat Ditemui</th>
                            <th x-show="$store.usahaColumns.keluarga_tidak_ditemukan" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Keluarga Tidak Ditemukan</th>
                            <th x-show="$store.usahaColumns.keluarga_baru" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Keluarga Baru</th>
                            <th x-show="$store.usahaColumns.prelist_usaha" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Jumlah Prelist Usaha</th>
                            <th x-show="$store.usahaColumns.usaha_realisasi" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Jumlah Usaha Realisasi</th>
                            <th x-show="$store.usahaColumns.prelist_keluarga" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Jumlah Prelist Keluarga</th>
                            <th x-show="$store.usahaColumns.keluarga_realisasi" x-cloak
                                class="text-right px-5 py-4 whitespace-nowrap">Jumlah Keluarga Realisasi</th>
                            <th x-show="$store.usahaColumns.ppl" x-cloak class="text-left px-5 py-4 whitespace-nowrap">PPL
                            </th>
                            <th x-show="$store.usahaColumns.pml" x-cloak class="text-left px-5 py-4 whitespace-nowrap">PML
                            </th>
                            <th x-show="$store.usahaColumns.last_update" x-cloak
                                class="text-left px-5 py-4 whitespace-nowrap">Last Update</th>

                        </tr>

                    </thead>

                    @forelse ($dataGrouped as $kecamatan => $petugasGroups)

                        <tbody x-data="{ open: false }" class="divide-y divide-slate-100">

                            {{-- BARIS KECAMATAN --}}
                            <tr @click="open = !open"
                                class="bg-slate-100 hover:bg-slate-200 cursor-pointer transition-colors">
                                <td :colspan="1 + $store.usahaColumns.visibleCount()"
                                    class="px-5 py-3 font-bold text-slate-700 whitespace-nowrap
                       sticky left-0 z-10 bg-slate-100">
                                    <span x-text="open ? '▾' : '▸'" class="mr-2 text-slate-400"></span>
                                    {{ $kecamatan }}
                                </td>
                            </tr>

                            @foreach ($petugasGroups as $namaPetugas => $rows)
                                {{-- BARIS PETUGAS + EMAIL --}}
                                <tr x-show="open" x-cloak class="bg-slate-50">
                                    <td :colspan="1 + $store.usahaColumns.visibleCount()"
                                        class="px-5 py-2 pl-10 whitespace-nowrap
                           sticky left-0 z-10 bg-slate-50">
                                        <div class="font-semibold text-slate-600 text-sm">
                                            {{ $namaPetugas }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ $rows->first()->email_petugas ?: '-' }}
                                        </div>
                                    </td>
                                </tr>

                                {{-- BARIS DATA SLS --}}
                                @foreach ($rows as $i => $row)
                                    <tr x-show="open" x-cloak class="hover:bg-slate-50 transition-colors">

                                        <td class="px-5 py-3 text-slate-400">{{ $i + 1 }}</td>

                                        <td x-show="$store.usahaColumns.id_wilayah" x-cloak
                                            class="px-5 py-3 text-slate-600">{{ $row->id_wilayah ?: '-' }}</td>
                                        <td x-show="$store.usahaColumns.kd_kab" x-cloak
                                            class="px-5 py-3 text-slate-600 divide-x divide-slate-100">
                                            {{ $row->kd_kab ?: '-' }}</td>
                                        <td x-show="$store.usahaColumns.nama_sls" x-cloak class="px-5 py-3">
                                            <div class="font-semibold text-slate-700">{{ $row->nama_sls ?: '-' }}</div>
                                        </td>
                                        <td x-show="$store.usahaColumns.ub_prelist" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_ub_prelist_awal ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.um_prelist" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_um_prelist_awal ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.umk_prelist" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_umk_prelist_awal ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_ditemukan_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-emerald-600">
                                            {{ number_format($row->jumlah_usaha_ditemukan_bku ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_ditutup_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-red-600">
                                            {{ number_format($row->jumlah_usaha_ditutup_bku ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_ganda_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-amber-600">
                                            {{ number_format($row->jumlah_usaha_ganda_bku ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-slate-600">
                                            {{ number_format($row->jumlah_usaha_tidak_ditemukan_bku ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_baru_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-blue-600">
                                            {{ number_format($row->jumlah_usaha_baru_bku ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_ditemukan_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_ditemukan_usaha_keluarga ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_tutup_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_tutup_usaha_keluarga ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_ganda_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_ganda_usaha_keluarga ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_tidak_ditemukan_usaha_keluarga ?? 0) }}
                                        </td>
                                        <td x-show="$store.usahaColumns.usaha_baru_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_baru_usaha_keluarga ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.keluarga_ditemukan" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-emerald-600">
                                            {{ number_format($row->jumlah_keluarga_ditemukan ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.keluarga_meninggal" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-red-600">
                                            {{ number_format($row->jumlah_keluarga_meninggal ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.keluarga_tidak_eligible" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-amber-600">
                                            {{ number_format($row->jumlah_keluarga_tidak_eligible ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.keluarga_tidak_ditemui" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-orange-600">
                                            {{ number_format($row->jumlah_keluarga_tidak_ditemui ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.keluarga_tidak_ditemukan" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-slate-600">
                                            {{ number_format($row->jumlah_keluarga_tidak_ditemukan ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.keluarga_baru" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-blue-600">
                                            {{ number_format($row->jumlah_keluarga_baru ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.prelist_usaha" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_prelist_usaha ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.usaha_realisasi" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-sky-600">
                                            {{ number_format($row->jumlah_usaha_realisasi ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.prelist_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_prelist_keluarga ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.keluarga_realisasi" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-emerald-600">
                                            {{ number_format($row->jumlah_keluarga_realisasi ?? 0) }}</td>
                                        <td x-show="$store.usahaColumns.ppl" x-cloak class="px-5 py-3 text-slate-600">
                                            {{ $row->ppl ?: '-' }}</td>
                                        <td x-show="$store.usahaColumns.pml" x-cloak class="px-5 py-3 text-slate-600">
                                            {{ $row->pml ?: '-' }}</td>
                                        <td x-show="$store.usahaColumns.last_update" x-cloak
                                            class="px-5 py-3 text-slate-600 whitespace-nowrap">
                                            {{ $row->last_update ?: '-' }}</td>

                                    </tr>
                                @endforeach
                            @endforeach

                        </tbody>

                    @empty

                        <tbody>
                            <tr>
                                <td colspan="30" class="px-5 py-10 text-center text-slate-400">
                                    Belum ada data Usaha.
                                </td>
                            </tr>
                        </tbody>

                    @endforelse

                </table>

            </div>

        </div>

    </div>

@endsection
