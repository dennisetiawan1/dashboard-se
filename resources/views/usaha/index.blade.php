@extends('layouts.app')

@section('title', 'Dashboard Usaha')

@section('content')
    <script>
        window.initialUsahaColumnsRaw = @json(request('columns'));
    document.addEventListener('alpine:init', () => {
        const defaultColumns = {
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
        };

        let initialColumns = { ...defaultColumns };

        // Kalau URL bawa parameter "columns" (hasil submit filter sebelumnya), pakai itu
        if (window.initialUsahaColumnsRaw) {
            try {
                const activeList = JSON.parse(window.initialUsahaColumnsRaw);
                if (Array.isArray(activeList)) {
                    Object.keys(defaultColumns).forEach(k => {
                        initialColumns[k] = activeList.includes(k);
                    });
                }
            } catch (e) {
                // biarkan default kalau parsing gagal
            }
        }

        Alpine.store('usahaColumns', {
            ...initialColumns,

            draft: {},

            keys() {
                return Object.keys(defaultColumns);
            },
            visibleCount() {
                return this.keys().filter(k => this[k]).length;
            },
            visibleColumns() {
                return this.keys().filter(k => this[k]);
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

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[action="{{ route('usaha') }}"]');
            const exportColumns = document.getElementById('exportColumns');

            if (!form || !exportColumns) return;

            form.addEventListener('submit', function (e) {
                if (e.submitter?.formAction.includes('{{ route('export.usaha.grouped') }}')) {
                    exportColumns.value = JSON.stringify(
                        Alpine.store('usahaColumns').visibleColumns()
                    );
                }
            });
        });

        (function () {
            const url = new URL(window.location.href);
            const hasFilters = [...url.searchParams.keys()].length > 0;

            if (!hasFilters) return;

            const navEntries = performance.getEntriesByType('navigation');
            const isReload = navEntries.length > 0 && navEntries[0].type === 'reload';

            if (isReload) {
                window.location.replace(url.pathname);
            }
        })();
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

     {{-- KPI PERSENTASE --}}

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- ================= PERSENTASE BKU ================= --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-500 to-cyan-600 p-6 shadow-md">
            <div class="absolute -right-12 -bottom-12 w-44 h-44 rounded-full bg-white/10"></div>
            <div class="absolute right-8 top-8 w-20 h-20 rounded-full border border-white/10"></div>

            <div class="relative z-10">

                <div class="flex items-center justify-between">
                    <p class="text-sky-100 text-xs font-semibold uppercase tracking-widest">
                        Persentase BKU
                    </p>

                    @php $delta = $percentageComparison['bku'] ?? 0; @endphp
                    <div class="flex items-center gap-1 pl-2 pr-2.5 py-1 rounded-full bg-white shadow-sm
                        {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            @if ($delta > 0)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                            @elseif ($delta < 0)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            @endif
                        </svg>
                        <span class="text-xs font-bold">{{ number_format(abs($delta), 2) }}%</span>
                    </div>
                </div>

                <div class="mt-3 text-4xl font-extrabold text-white leading-none">
                    {{ number_format($percentageSummary['bku']['value'], 2) }}%
                </div>

                <div class="mt-5 pt-4 border-t border-white/20 text-sm text-sky-100">
                    <strong class="text-white">{{ number_format($percentageSummary['bku']['numerator']) }}</strong>
                    BKU ditemukan &amp; baru dari
                    <strong class="text-white">{{ number_format($percentageSummary['bku']['denominator']) }}</strong>
                    prelist usaha
                </div>

            </div>
        </div>

        {{-- ================= PERSENTASE USAHA KELUARGA ================= --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-500 to-green-700 p-6 shadow-md">
            <div class="absolute -right-12 -bottom-12 w-44 h-44 rounded-full bg-white/10"></div>
            <div class="absolute right-8 top-8 w-20 h-20 rounded-full border border-white/10"></div>

            <div class="relative z-10">

                <div class="flex items-center justify-between">
                    <p class="text-emerald-100 text-xs font-semibold uppercase tracking-widest">
                        Persentase Usaha Keluarga
                    </p>

                    @php $delta = $percentageComparison['usaha_keluarga'] ?? 0; @endphp
                    <div class="flex items-center gap-1 pl-2 pr-2.5 py-1 rounded-full bg-white shadow-sm
                        {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            @if ($delta > 0)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                            @elseif ($delta < 0)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            @endif
                        </svg>
                        <span class="text-xs font-bold">{{ number_format(abs($delta), 2) }}%</span>
                    </div>
                </div>

                <div class="mt-3 text-4xl font-extrabold text-white leading-none">
                    {{ number_format($percentageSummary['usaha_keluarga']['value'], 2) }}%
                </div>

                <div class="mt-5 pt-4 border-t border-white/20 text-sm text-emerald-100">
                    <strong class="text-white">{{ number_format($percentageSummary['usaha_keluarga']['numerator']) }}</strong>
                    Usaha Keluarga ditemukan &amp; baru dari
                    <strong class="text-white">{{ number_format($percentageSummary['usaha_keluarga']['denominator']) }}</strong>
                    prelist keluarga
                </div>

            </div>
        </div>

        {{-- ================= PERSENTASE TOTAL USAHA ================= --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-500 to-purple-700 p-6 shadow-md">
            <div class="absolute -right-12 -bottom-12 w-44 h-44 rounded-full bg-white/10"></div>
            <div class="absolute right-8 top-8 w-20 h-20 rounded-full border border-white/10"></div>

            <div class="relative z-10">

                <div class="flex items-center justify-between">
                    <p class="text-violet-100 text-xs font-semibold uppercase tracking-widest">
                        Persentase Total Usaha
                    </p>

                    @php $delta = $percentageComparison['total_usaha'] ?? 0; @endphp
                    <div class="flex items-center gap-1 pl-2 pr-2.5 py-1 rounded-full bg-white shadow-sm
                        {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            @if ($delta > 0)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                            @elseif ($delta < 0)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            @endif
                        </svg>
                        <span class="text-xs font-bold">{{ number_format(abs($delta), 2) }}%</span>
                    </div>
                </div>

                <div class="mt-3 text-4xl font-extrabold text-white leading-none">
                    {{ number_format($percentageSummary['total_usaha']['value'], 2) }}%
                </div>

                <div class="mt-5 pt-4 border-t border-white/20 text-sm text-violet-100">
                    <strong class="text-white">{{ number_format($percentageSummary['total_usaha']['numerator']) }}</strong>
                    BKU dan usaha keluarga dari
                    <strong class="text-white">{{ number_format($percentageSummary['total_usaha']['denominator']) }}</strong>
                    prelist keluarga
                </div>

            </div>
        </div>

    </div>

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

                    @php $delta = $summaryComparison['usaha_ditemukan_bku'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
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
                        tutup
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['usaha_ditutup_bku']) }}
                    </div>
                    @php $delta = $summaryComparison['usaha_ditutup_bku'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
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
                    @php $delta = $summaryComparison['usaha_ganda_bku'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
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
                    @php $delta = $summaryComparison['usaha_tidak_ditemukan_bku'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
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
                    @php $delta = $summaryComparison['usaha_baru_bku'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>

            </div>

        </div>

        <div>
            <div class="mb-4">

                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide">
                    Status Usaha Keluarga
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
                        {{ number_format($summary['usaha_ditemukan_keluarga']) }}
                    </div>
                    @php $delta = $summaryComparison['usaha_ditemukan_keluarga'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>

                {{-- Tutup --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />

                        </svg>

                    </div>

                    <div class="text-xs font-extrabold uppercase tracking-wide text-red-600">
                        Tutup
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['usaha_tutup_keluarga']) }}
                    </div>
                    @php $delta = $summaryComparison['usaha_tutup_keluarga'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
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
                        {{ number_format($summary['usaha_ganda_keluarga']) }}
                    </div>
                    @php $delta = $summaryComparison['usaha_ganda_keluarga'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
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
                        {{ number_format($summary['usaha_tidak_ditemukan_keluarga']) }}
                    </div>
                    @php $delta = $summaryComparison['usaha_tidak_ditemukan_keluarga'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
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
                        {{ number_format($summary['usaha_baru_keluarga']) }}
                    </div>
                    @php $delta = $summaryComparison['usaha_baru_keluarga'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>

            </div>

        </div>

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
                    @php $delta = $summaryComparison['keluarga_ditemukan'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-red-600">
                        Meninggal
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_meninggal']) }}
                    </div>
                    @php $delta = $summaryComparison['keluarga_meninggal'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-amber-600">
                        Tidak Eligible
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_tidak_eligible']) }}
                    </div>
                    @php $delta = $summaryComparison['keluarga_tidak_eligible'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-orange-600">
                        Tidak Ditemui
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_tidak_ditemui']) }}
                    </div>
                    @php $delta = $summaryComparison['keluarga_tidak_ditemui'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-slate-600">
                        Tidak Ditemukan
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_tidak_ditemukan']) }}
                    </div>
                    @php $delta = $summaryComparison['keluarga_tidak_ditemukan'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>


                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                    <div class="text-xs font-extrabold uppercase text-blue-600">
                        Keluarga Baru
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['keluarga_baru']) }}
                    </div>
                    @php $delta = $summaryComparison['keluarga_baru'] ?? 0; @endphp
                    <div class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-600' : ($delta < 0 ? 'text-red-600' : 'text-slate-400') }}">
                        {{ $delta > 0 ? '↑ +' : ($delta < 0 ? '↓ ' : '– ') }}{{ number_format($delta) }} vs kemarin
                    </div>

                </div>

            </div>

        </div>

       {{-- TABEL 3 - PERBANDINGAN BKU (ENHANCED) --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mt-6">
            {{-- Header tabel --}}
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Perbandingan Pencapaian
                </h3>
                <p class="mt-1 text-xs text-slate-900">
                    Perbandingan pencapaian BKU dan Usaha Keluarga dengan muatan Wilker Stat dan ST 2023.
                </p>
            </div>

            {{-- SUMMARY CARDS --}}
            <div class="px-5 py-4 bg-slate-50 grid grid-cols-4 gap-3">
                @php
                    $tanggalTerbaru = end($tanggalUploads);
                    $tanggalTerbaruKey = \Carbon\Carbon::parse($tanggalTerbaru)->format('Y-m-d');
                    $totalTerbaru = $progressGrandTotals[$tanggalTerbaruKey]['bku'] ?? 0;
                    $totalUsahaKeluarga = $progressGrandTotals[$tanggalTerbaruKey]['usaha_keluarga'] ?? 0;
                    $persenWilker = $totalWilkerStat > 0 ? round(($totalTerbaru / $totalWilkerStat) * 100, 1) : 0;
                    $persenUsahaKeluarga = $totalST2023 > 0 ? round(($totalUsahaKeluarga / $totalST2023) * 100, 1) : 0;
                @endphp

                <div class="bg-white rounded-lg border border-slate-200 p-3">
                    <p class="text-xs text-slate-600 font-semibold">BKU Ditemukan</p>
                    <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($totalTerbaru) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Tanggal: {{ \Carbon\Carbon::parse($tanggalTerbaru)->translatedFormat('d F Y') }}</p>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 p-3">
                    <p class="text-xs text-slate-600 font-semibold">Usaha Keluarga</p>
                    <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($totalUsahaKeluarga) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Tanggal: {{ \Carbon\Carbon::parse($tanggalTerbaru)->translatedFormat('d F Y') }}</p>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 p-3">
                    <p class="text-xs text-slate-600 font-semibold">vs Muatan Wilkerstat</p>
                    <p class="text-xl font-bold {{ $persenWilker >= 100 ? 'text-green-600' : 'text-amber-600' }} mt-1">{{ $persenWilker }}%</p>
                    <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2">
                        <div class="h-1.5 rounded-full {{ $persenWilker >= 100 ? 'bg-green-500' : 'bg-amber-500' }}" style="width: min({{ $persenWilker }}%, 100%)"></div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 p-3">
                    <p class="text-xs text-slate-600 font-semibold">Usaha Keluarga vs ST 2023</p>
                    <p class="text-xl font-bold {{ $persenUsahaKeluarga >= 100 ? 'text-green-600' : 'text-blue-600' }} mt-1">{{ $persenUsahaKeluarga }}%</p>
                    <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2">
                        <div class="h-1.5 rounded-full {{ $persenUsahaKeluarga >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: min({{ $persenUsahaKeluarga }}%, 100%)"></div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th class="sticky left-0 z-10 px-5 py-4 text-left text-[11px] uppercase tracking-wider text-slate-900 whitespace-nowrap border-r border-slate-200 bg-slate-50">
                                Kecamatan
                            </th>
                            <th class="px-5 py-4 text-center text-[11px] uppercase tracking-wider text-slate-900 whitespace-nowrap">
                                Ditemukan dan Baru (BKU)
                            </th>
                            <th class="px-5 py-4 text-center text-[11px] uppercase tracking-wider text-slate-900 whitespace-nowrap">
                                Muatan Wilkerstat
                            </th>
                            <th class="px-5 py-4 text-center text-[11px] uppercase tracking-wider text-slate-900 whitespace-nowrap">
                                Usaha Keluarga
                            </th>
                            <th class="px-5 py-4 text-center text-[11px] uppercase tracking-wider text-slate-900 whitespace-nowrap">
                                ST 2023
                            </th>
                            {{-- <th class="px-5 py-4 text-center text-[11px] uppercase tracking-wider text-slate-900 whitespace-nowrap">
                                Status
                            </th> --}}
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @php
                            $tanggalTerbaru = end($tanggalUploads);
                            $tanggalTerbaruKey = \Carbon\Carbon::parse($tanggalTerbaru)->format('Y-m-d');
                        @endphp

                        @forelse ($progressTable as $kecamatan => $group)
                            @php
                                $bkuTerbaru = $group['totals'][$tanggalTerbaruKey]['bku'] ?? 0;
                                $usahaKeluargaTerbaru = $group['totals'][$tanggalTerbaruKey]['usaha_keluarga'] ?? 0;
                                $target = $wilkerStatMap[$kecamatan] ?? null;
                                $wilkerStat = $target?->bku_wilkerstat ?? 0;
                                $st2023 = $target?->st_2023 ?? 0;
                                
                                // Status BKU (dibanding Wilker & ST 2023)
                                $statusBKU_Wilker = $bkuTerbaru >= $wilkerStat;
                                $statusBKU_ST = $bkuTerbaru >= $st2023;
                                
                                // Status Usaha Keluarga (dibanding ST 2023)
                                $statusUsaha_ST = $usahaKeluargaTerbaru >= $st2023;
                                
                                // Tentukan status keseluruhan
                                if ($statusBKU_ST && $statusUsaha_ST) {
                                    $status = 'Tercapai';
                                    $statusClass = 'bg-green-100 text-green-700';
                                } elseif ($statusBKU_Wilker || ($usahaKeluargaTerbaru > 0)) {
                                    $status = 'Sebagian';
                                    $statusClass = 'bg-amber-100 text-amber-700';
                                } else {
                                    $status = 'Kurang';
                                    $statusClass = 'bg-red-100 text-red-700';
                                }
                            @endphp
                            <tr class="">
                                <td class="sticky left-0 z-10 px-5 py-4 font-semibold text-slate-700 whitespace-nowrap border-r border-slate-200">
                                    {{ $kecamatan }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="font-bold text-slate-900">{{ number_format($bkuTerbaru) }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="font-semibold text-slate-600">{{ number_format($wilkerStat) }}</span>
                                    <div class="mt-2 w-32 mx-auto bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-amber-500 h-1.5 rounded-full" style="width: min({{ $wilkerStat > 0 ? ($bkuTerbaru / $wilkerStat) * 100 : 0 }}%, 100%)"></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="font-bold text-slate-900">{{ number_format($usahaKeluargaTerbaru) }}</span>
                                </td>
                                
                                <td class="px-5 py-4 text-center">
                                    <span class="font-semibold text-slate-600">{{ number_format($st2023) }}</span>
                                    <div class="mt-2 w-32 mx-auto bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: min({{ $st2023 > 0 ? ($usahaKeluargaTerbaru / $st2023) * 100 : 0 }}%, 100%)"></div>
                                    </div>
                                </td>
                                {{-- <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-1 {{ $statusClass }} rounded text-xs font-semibold">
                                        @if ($status === 'Tercapai')
                                            ✓ Tercapai
                                        @elseif ($status === 'Sebagian')
                                            ⚠ Sebagian
                                        @else
                                            ✗ Kurang
                                        @endif
                                    </span>
                                </td> --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                    Belum ada data perbandingan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="bg-gradient-to-r from-slate-100 to-slate-50 border-t-2 border-slate-300">
                            <td class="sticky left-0 z-10 bg-slate-100 px-5 py-4 font-bold text-slate-900 whitespace-nowrap border-r border-slate-200">
                                TOTAL
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-slate-900">
                                {{ number_format($progressGrandTotals[$tanggalTerbaruKey]['bku'] ?? 0) }}
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-slate-900">
                                {{ number_format($totalWilkerStat) }}
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-slate-900">
                                {{ number_format($progressGrandTotals[$tanggalTerbaruKey]['usaha_keluarga'] ?? 0) }}
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-slate-900">
                                {{ number_format($totalST2023) }}
                            </td>
                            {{-- <td class="px-5 py-4 text-center">
                                @php
                                    $totalBKU = $progressGrandTotals[$tanggalTerbaruKey]['bku'] ?? 0;
                                    $totalUsahaKeluargaGrand = $progressGrandTotals[$tanggalTerbaruKey]['usaha_keluarga'] ?? 0;
                                    
                                    $totalStatusBKU_ST = $totalBKU >= $totalST2023;
                                    $totalStatusUsaha_ST = $totalUsahaKeluargaGrand >= $totalST2023;
                                    
                                    if ($totalStatusBKU_ST && $totalStatusUsaha_ST) {
                                        $totalStatus = 'Tercapai';
                                        $totalStatusClass = 'bg-green-100 text-green-700';
                                    } elseif ($totalBKU >= $totalWilkerStat || $totalUsahaKeluargaGrand > 0) {
                                        $totalStatus = 'Sebagian';
                                        $totalStatusClass = 'bg-amber-100 text-amber-700';
                                    } else {
                                        $totalStatus = 'Kurang';
                                        $totalStatusClass = 'bg-red-100 text-red-700';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 {{ $totalStatusClass }} rounded text-xs font-bold">
                                    @if ($totalStatus === 'Tercapai')
                                        ✓ Tercapai
                                    @elseif ($totalStatus === 'Sebagian')
                                        ⚠ Sebagian
                                    @else
                                        ✗ Kurang
                                    @endif
                                </span>
                            </td> --}}
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- KETERANGAN --}}
            {{-- <div class="px-5 py-3 bg-slate-50 border-t border-slate-100">
                <p class="text-xs text-slate-600">
                    <span class="inline-block mr-3">✓ <strong>Tercapai:</strong> BKU & Usaha Keluarga keduanya mencapai ST 2023</span>
                    <span class="inline-block mr-3">⚠ <strong>Sebagian:</strong> Salah satu dari BKU atau Usaha Keluarga mencapai target</span>
                    <span class="inline-block">✗ <strong>Kurang:</strong> Keduanya belum mencapai target</span>
                </p>
            </div> --}}
        </div>

        {{-- TABEL 1 - PERKEMBANGAN DATA BERDASARKAN TANGGAL --}}

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        {{-- Header tabel --}}
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Perbandingan Pencapaian
                </h3>
                <p class="mt-1 text-xs text-slate-900">
                    Perbandingan pencapaian BKU dan Usaha Keluarga dengan muatan Wilker Stat dan ST 2023.
                </p>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-white border-b border-slate-200">
                        <tr>
                            <th rowspan="2"
                                class="sticky left-0 z-10 px-5 py-4 text-left
                                text-[11px] uppercase tracking-wider text-slate-900
                                whitespace-nowrap border-r border-slate-200 align-bottom
                                bg-white">
                                Kecamatan / Petugas
                            </th>

                            @foreach ($tanggalUploads as $tanggal)
                                <th colspan="3"
                                    class="px-6 py-3 text-center text-[11px]
                            uppercase tracking-wider text-slate-900
                            whitespace-nowrap border-l-2 border-slate-200">

                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}

                                </th>
                            @endforeach

                        </tr>

                        <tr>

                            @foreach ($tanggalUploads as $tanggal)
                                <th
                                    class="px-3 py-2 text-center text-[12px]
                            font-semibold uppercase tracking-wider
                            text-slate-900 border-l-2 border-slate-200
                            whitespace-nowrap">
                                    BKU Ditemukan dan Baru
                                </th>

                                <th
                                    class="px-3 py-2 text-center text-[12px]
                            font-semibold uppercase tracking-wider
                            text-slate-900 whitespace-nowrap">
                                    Usaha dalam Keluarga<br> Ditemukan dan Baru
                                </th>

                                <th
                                    class="px-3 py-2 text-center text-[12px]
                            font-semibold uppercase tracking-wider
                            text-slate-900 whitespace-nowrap">
                                    Keluarga Ditemukan dan Baru
                                </th>
                            @endforeach

                        </tr>

                    </thead>
                    {{--  BODY --}}
                    @forelse ($progressTable as $kecamatan => $group)

                        <tbody x-data="{ open: false }" class="divide-y divide-slate-100">

                            {{-- BARIS KECAMATAN --}}

                            <tr @click="open = !open" class="hover:bg-slate-50 cursor-pointer transition-colors">
                                {{-- NAMA KECAMATAN --}}
                                <td
                                    class="sticky left-0 z-10 bg-white px-5 py-4
                            font-semibold text-slate-700 whitespace-nowrap
                            border-r border-slate-200">

                                    <span x-text="open ? '▾' : '▸'" class="mr-2 text-slate-900">
                                    </span>

                                    {{ $kecamatan }}

                                </td>
                                {{-- DATA KECAMATAN PER TANGGAL --}}
                                @foreach ($tanggalUploads as $tanggal)
                                    @php

                                        $tanggalKey = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

                                        $item = $group['totals'][$tanggalKey] ?? null;

                                    @endphp

                                    {{-- BKU --}}

                                    <td
                                        class="px-3 py-4 text-center whitespace-nowrap
                                border-l border-slate-100">
                                        @if ($item)
                                            {{-- NILAI --}}
                                            <div class="font-semibold text-slate-900">

                                                {{ number_format($item['bku']) }}

                                            </div>

                                            {{-- PERSENTASE --}}
                                            @php

                                                $persen = $item['percentage']['bku'] ?? null;

                                                $trend = $item['trend']['bku'] ?? 'none';

                                            @endphp


                                            @if ($persen !== null)
                                                <div
                                                    class="mt-1 text-[11px] font-semibold
                                            {{ $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-slate-400') }}">

                                                    @if ($trend === 'up')
                                                        ↑
                                                    @elseif ($trend === 'down')
                                                        ↓
                                                    @else
                                                        →
                                                    @endif

                                                    {{ number_format(abs($persen), 1) }}%

                                                </div>
                                            @endif
                                        @else
                                            -
                                        @endif

                                    </td>

                                    {{-- USAHA KELUARGA --}}

                                    <td class="px-3 py-4 text-center whitespace-nowrap">

                                        @if ($item)
                                            {{-- NILAI --}}
                                            <div class="text-slate-900 font-semibold">

                                                {{ number_format($item['usaha_keluarga']) }}

                                            </div>


                                            {{-- PERSENTASE --}}
                                            @php

                                                $persen = $item['percentage']['usaha_keluarga'] ?? null;

                                                $trend = $item['trend']['usaha_keluarga'] ?? 'none';

                                            @endphp


                                            @if ($persen !== null)
                                                <div
                                                    class="mt-1 text-[11px] font-semibold
                                            {{ $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-slate-400') }}">

                                                    @if ($trend === 'up')
                                                        ↑
                                                    @elseif ($trend === 'down')
                                                        ↓
                                                    @else
                                                        →
                                                    @endif

                                                    {{ number_format(abs($persen), 1) }}%

                                                </div>
                                            @endif
                                        @else
                                            -
                                        @endif

                                    </td>

                                    {{-- KELUARGA --}}

                                    <td class="px-3 py-4 text-center whitespace-nowrap">

                                        @if ($item)
                                            <div class="text-slate-900 font-semibold">
                                                {{ number_format($item['keluarga']) }}
                                            </div>

                                            @php
                                                $persen = $item['percentage']['keluarga'] ?? null;
                                                $trend = $item['trend']['keluarga'] ?? 'none';
                                            @endphp

                                            @if ($persen !== null)
                                                <div
                                                    class="mt-1 text-[11px] font-semibold
                {{ $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-slate-400') }}">

                                                    @if ($trend === 'up')
                                                        ↑
                                                    @elseif ($trend === 'down')
                                                        ↓
                                                    @else
                                                        →
                                                    @endif

                                                    {{ number_format(abs($persen), 1) }}%

                                                </div>
                                            @elseif ($item['keluarga'] > 0)
                                                <div class="mt-1 text-[11px] font-semibold text-green-600">
                                                    ↑ Baru
                                                </div>
                                                {{-- @else
                                                <div class="mt-1 text-[12px] font-semibold text-slate-400">
                                                    → 0%
                                                </div> --}}
                                            @endif
                                        @else
                                            -
                                        @endif

                                    </td>
                                @endforeach

                            </tr>

                            {{-- BARIS PETUGAS --}}

                            @foreach ($group['petugas'] as $namaPetugas => $tanggalData)
                                <tr x-show="open" x-cloak class="bg-slate-50/60">

                                    {{-- NAMA PETUGAS --}}

                                    <td
                                        class="sticky left-0 z-10 bg-slate-50
                                pl-10 pr-5 py-3 text-slate-900
                                whitespace-nowrap border-r border-slate-200">

                                        {{ $namaPetugas }}

                                    </td>

                                    {{-- DATA PETUGAS PER TANGGAL --}}

                                    @foreach ($tanggalUploads as $tanggal)
                                        @php

                                            $tanggalKey = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

                                            $item = $tanggalData[$tanggalKey] ?? null;

                                        @endphp

                                        {{-- BKU PETUGAS --}}

                                        <td
                                            class="px-3 py-3 text-center whitespace-nowrap
                                    border-l border-slate-200">

                                            @if ($item)
                                                {{-- NILAI --}}
                                                <div class="text-sm font-semibold text-slate-700">

                                                    {{ number_format($item['bku']) }}

                                                </div>

                                                {{-- PERSENTASE --}}
                                                @php

                                                    $persen = $item['percentage']['bku'] ?? null;

                                                    $trend = $item['trend']['bku'] ?? 'none';

                                                @endphp

                                                @if ($persen !== null)
                                                    <div
                                                        class="mt-1 text-[11px] font-semibold
                                                {{ $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-slate-900') }}">

                                                        @if ($trend === 'up')
                                                            ↑
                                                        @elseif ($trend === 'down')
                                                            ↓
                                                        @else
                                                            →
                                                        @endif

                                                        {{ number_format(abs($persen), 1) }}%

                                                    </div>
                                                @endif
                                            @else
                                                -
                                            @endif

                                        </td>

                                        {{-- USAHA KELUARGA PETUGAS --}}

                                        <td class="px-3 py-3 text-center whitespace-nowrap">

                                            @if ($item)
                                                {{-- NILAI --}}
                                                <div class="text-sm text-slate-900">

                                                    {{ number_format($item['usaha_keluarga']) }}

                                                </div>

                                                {{-- PERSENTASE --}}
                                                @php

                                                    $persen = $item['percentage']['usaha_keluarga'] ?? null;

                                                    $trend = $item['trend']['usaha_keluarga'] ?? 'none';

                                                @endphp

                                                @if ($persen !== null)
                                                    <div
                                                        class="mt-1 text-[11px] font-semibold
                                                {{ $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-slate-900') }}">

                                                        @if ($trend === 'up')
                                                            ↑
                                                        @elseif ($trend === 'down')
                                                            ↓
                                                        @else
                                                            →
                                                        @endif

                                                        {{ number_format(abs($persen), 1) }}%

                                                    </div>
                                                @endif
                                            @else
                                                -
                                            @endif

                                        </td>

                                        {{-- KELUARGA PETUGAS --}}

                                        <td class="px-3 py-3 text-center whitespace-nowrap">

                                            @if ($item)
                                                {{-- NILAI --}}
                                                <div class="text-sm text-slate-900">

                                                    {{ number_format($item['keluarga']) }}

                                                </div>

                                                {{-- PERSENTASE --}}
                                                @php

                                                    $persen = $item['percentage']['keluarga'] ?? null;

                                                    $trend = $item['trend']['keluarga'] ?? 'none';

                                                @endphp

                                                @if ($persen !== null)
                                                    <div
                                                        class="mt-1 text-[11px] font-semibold
                                                {{ $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-slate-900') }}">

                                                        @if ($trend === 'up')
                                                            ↑
                                                        @elseif ($trend === 'down')
                                                            ↓
                                                        @else
                                                            →
                                                        @endif

                                                        {{ number_format(abs($persen), 1) }}%

                                                    </div>
                                                @endif
                                            @else
                                                -
                                            @endif

                                        </td>
                                    @endforeach

                                </tr>
                            @endforeach

                        </tbody>


                    @empty

                        {{--  DATA KOSONG --}}

                        <tbody>

                            <tr>

                                <td colspan="{{ count($tanggalUploads) * 3 + 1 }}"
                                    class="px-5 py-10 text-center text-slate-400">

                                    Belum ada data perkembangan Usaha.

                                </td>

                            </tr>

                        </tbody>
                    @endforelse
                    <tfoot>
                        <tr class="bg-slate-100 border-t-2 border-slate-300">

                            <td
                                class="sticky left-0 z-10 bg-slate-100 px-5 py-4 font-bold text-slate-900 whitespace-nowrap border-r border-slate-200">
                                TOTAL SEMUA KECAMATAN
                            </td>

                            @foreach ($tanggalUploads as $tanggal)
                                @php
                                    $tanggalKey = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
                                    $total = $progressGrandTotals[$tanggalKey] ?? [
                                        'bku' => 0,
                                        'usaha_keluarga' => 0,
                                        'keluarga' => 0,
                                    ];
                                @endphp

                                <td class="px-3 py-4 text-center whitespace-nowrap border-l border-slate-200">
                                    <div class="font-bold text-slate-900">
                                        {{ number_format($total['bku']) }}
                                    </div>
                                </td>

                                <td class="px-3 py-4 text-center whitespace-nowrap">
                                    <div class="font-bold text-slate-900">
                                        {{ number_format($total['usaha_keluarga']) }}
                                    </div>
                                </td>

                                <td class="px-3 py-4 text-center whitespace-nowrap">
                                    <div class="font-bold text-slate-900">
                                        {{ number_format($total['keluarga']) }}
                                    </div>
                                </td>
                            @endforeach

                        </tr>
                    </tfoot>
                </table>

            </div>

        </div>
        
        {{-- ================= FILTER & EXPORT DATA USAHA (GABUNGAN) ================= --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="p-6">

                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">
                            Filter & Export Data Usaha
                        </h3>
                        <p class="text-sm text-slate-500 mt-1">
                            Atur filter data dan kolom yang ditampilkan, lalu terapkan sekaligus.
                        </p>
                    </div>

                    @if(request()->anyFilled(['nama_kecamatan', 'kd_kab', 'ppl', 'pml']))
                        <span class="text-[10px] font-semibold px-2 py-1 rounded-full bg-sky-100 text-sky-700">
                            Filter Aktif
                        </span>
                    @endif
                </div>

                <form method="GET" action="{{ route('usaha') }}" x-data
                    @submit="$store.usahaColumns.applyDraft()">
                    <input type="hidden" name="columns" id="exportColumns">

                    {{-- ===== BAGIAN 1: FILTER DATA ===== --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                Tanggal Upload
                            </label>
                            <select name="tanggal"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                                <option value="">Upload Terbaru</option>
                                @foreach ($availableUploadDates as $tgl)
                                    <option value="{{ $tgl }}" {{ request('tanggal') == $tgl ? 'selected' : '' }}>
                                        {{ \Illuminate\Support\Carbon::parse($tgl)->translatedFormat('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                Kecamatan
                            </label>
                            <select name="nama_kecamatan"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                                <option value="">Semua Kecamatan</option>
                                @foreach ($kecamatanOptions as $kec)
                                    <option value="{{ $kec }}" {{ request('nama_kecamatan') == $kec ? 'selected' : '' }}>
                                        {{ $kec }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                Kode Kabupaten
                            </label>
                            <select name="kd_kab"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                                <option value="">Semua Kabupaten</option>
                                @foreach ($kabupatenOptions as $kab)
                                    <option value="{{ $kab }}" {{ request('kd_kab') == $kab ? 'selected' : '' }}>
                                        {{ $kab }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                PPL
                            </label>
                            <select name="ppl"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                                <option value="">Semua PPL</option>
                                @foreach ($pplOptions as $ppl)
                                    <option value="{{ $ppl->petugas_username }}"
                                        {{ request('ppl') == $ppl->petugas_username ? 'selected' : '' }}>
                                        {{ $ppl->nama_petugas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                PML
                            </label>
                            <select name="pml"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700">
                                <option value="">Semua PML</option>
                                @foreach ($pmlOptions as $pml)
                                    <option value="{{ $pml }}" {{ request('pml') == $pml ? 'selected' : '' }}>
                                        {{ $pml }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- ===== BAGIAN 2: FILTER KOLOM (collapsible di dalam form yang sama) ===== --}}
                    <div x-data="{ showColumns: false }" class="mt-5 pt-4 border-t border-slate-100">

                    <button type="button" @click="showColumns = !showColumns; if (showColumns) $store.usahaColumns.initDraft()" class="w-full flex items-center justify-between text-left">

                        <div class="flex items-center gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-700">Kolom Tabel</span>
                                    <span x-show="$store.usahaColumns.visibleCount() < $store.usahaColumns.keys().length" x-cloak
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">
                                        <span x-text="$store.usahaColumns.visibleCount()"></span> / <span x-text="$store.usahaColumns.keys().length"></span> aktif
                                    </span>
                                </div>

                                <p class="text-xs text-slate-400 mt-0.5">
                                    Kolom yang ditampilkan di tabel —
                                    <span x-text="$store.usahaColumns.visibleCount()"></span> dari
                                    <span x-text="$store.usahaColumns.keys().length"></span> kolom aktif
                                </p>
                            </div>
                        </div>

                        <span x-text="showColumns ? '▾' : '▸'" class="text-slate-400 text-lg"></span>
                    </button>
                    
                        <div x-show="showColumns" x-cloak class="pt-4">

                            <div class="flex gap-2 mb-4">
                                <button type="button" @click="$store.usahaColumns.draftShowAll()"
                                    class="text-xs font-medium px-3 py-1.5 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 transition">
                                    Tampilkan Semua
                                </button>
                                <button type="button" @click="$store.usahaColumns.draftHideAll()"
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

                        </div>

                    </div>

                    {{-- ===== SATU TOMBOL UNTUK KEDUANYA ===== --}}
                    <div class="flex items-center justify-between mt-5 pt-4 border-t border-slate-100">

                        <div>
                            @if(request()->anyFilled(['nama_kecamatan', 'kd_kab', 'ppl', 'pml']))
                                <a href="{{ route('usaha') }}"
                                    class="text-sm font-semibold text-slate-500 hover:text-slate-700 transition">
                                    Reset Semua Filter
                                </a>
                            @endif
                        </div>

                        <div class="flex gap-3">
                            <button type="submit"
                                onclick="Alpine.store('usahaColumns').applyDraft(); document.getElementById('exportColumns').value = JSON.stringify(Alpine.store('usahaColumns').visibleColumns())"
                                class="bg-sky-600 hover:bg-sky-700 text-white rounded-xl px-6 h-11 text-sm font-semibold shadow-sm hover:shadow transition">
                                Terapkan Filter
                            </button>

                            <button type="submit"
                                formaction="{{ route('export.usaha.grouped') }}"
                                onclick="document.getElementById('exportColumns').value = JSON.stringify($store.usahaColumns.visibleColumns())"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-6 h-11 text-sm font-semibold shadow-sm hover:shadow transition">
                                Export ke Excel
                            </button>
                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- TABEL USAHA --}}

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
                            <th x-show="$store.usahaColumns.desa" x-cloak class="text-left px-5 py-4 whitespace-nowrap">
                                Desa</th>
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

                    @forelse ($dataGrouped as $kecamatan => $group)
                    @php
                        $totals = $group['totals'];
                        $desaGroups = $group['desa'];
                    @endphp

                    <tbody x-data="{ open: false, openDesa: {} }" class="divide-y divide-slate-100">

                        {{-- ROW: KECAMATAN --}}
                        <tr @click="open = !open" class="bg-slate-100 hover:bg-slate-200 cursor-pointer transition-colors">
                            <td class="px-5 py-3 font-bold text-slate-700 whitespace-nowrap sticky left-0 z-10 bg-slate-100">
                                <span x-text="open ? '▾' : '▸'" class="mr-2 text-slate-400"></span>
                                {{ $kecamatan }}
                            </td>

                            <td x-show="$store.usahaColumns.id_wilayah" x-cloak class="px-5 py-3 bg-slate-100"></td>
                            <td x-show="$store.usahaColumns.kd_kab" x-cloak class="px-5 py-3 bg-slate-100 font-semibold text-slate-600">
                            </td>
                            <td x-show="$store.usahaColumns.desa" x-cloak class="px-5 py-3 bg-slate-100"></td>
                            <td x-show="$store.usahaColumns.nama_sls" x-cloak class="px-5 py-3 font-semibold bg-slate-100"></td>

                            <td x-show="$store.usahaColumns.ub_prelist" x-cloak class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_ub_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.um_prelist" x-cloak class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_um_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.umk_prelist" x-cloak class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_umk_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditemukan_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600 bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_ditemukan_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditutup_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-red-600 bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_ditutup_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ganda_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-amber-600 bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_ganda_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-slate-600 bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_tidak_ditemukan_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_baru_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-blue-600 bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_baru_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditemukan_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_ditemukan_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tutup_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_tutup_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ganda_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_ganda_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_tidak_ditemukan_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_baru_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_baru_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_ditemukan" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600 bg-slate-100">
                                {{ number_format($totals['jumlah_keluarga_ditemukan'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_meninggal" x-cloak
                                class="px-5 py-3 text-right font-bold text-red-600 bg-slate-100">
                                {{ number_format($totals['jumlah_keluarga_meninggal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_eligible" x-cloak
                                class="px-5 py-3 text-right font-bold text-amber-600 bg-slate-100">
                                {{ number_format($totals['jumlah_keluarga_tidak_eligible'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_ditemui" x-cloak
                                class="px-5 py-3 text-right font-bold text-orange-600 bg-slate-100">
                                {{ number_format($totals['jumlah_keluarga_tidak_ditemui'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_ditemukan" x-cloak
                                class="px-5 py-3 text-right font-bold text-slate-600 bg-slate-100">
                                {{ number_format($totals['jumlah_keluarga_tidak_ditemukan'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_baru" x-cloak
                                class="px-5 py-3 text-right font-bold text-blue-600 bg-slate-100">
                                {{ number_format($totals['jumlah_keluarga_baru'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.prelist_usaha" x-cloak class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_prelist_usaha'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_realisasi" x-cloak
                                class="px-5 py-3 text-right font-bold text-sky-600 bg-slate-100">
                                {{ number_format($totals['jumlah_usaha_realisasi'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.prelist_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($totals['jumlah_prelist_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_realisasi" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600 bg-slate-100">
                                {{ number_format($totals['jumlah_keluarga_realisasi'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.ppl" x-cloak class="px-5 py-3 bg-slate-100"></td>

                            <td x-show="$store.usahaColumns.pml" x-cloak class="px-5 py-3 bg-slate-100"></td>

                            <td x-show="$store.usahaColumns.last_update" x-cloak class="px-5 py-3 bg-slate-100"></td>
                        </tr>

                        @foreach ($desaGroups as $namaDesa => $desaGroup)
                            @php
                                $desaTotals = $desaGroup['totals'];
                                $petugasGroups = $desaGroup['petugas'];
                                $desaKey = \Illuminate\Support\Str::slug($kecamatan . '-' . $namaDesa) ?: md5($kecamatan . $namaDesa);
                            @endphp

                            {{-- ROW: DESA --}}
                            <tr x-show="open" x-cloak @click="openDesa['{{ $desaKey }}'] = !openDesa['{{ $desaKey }}']"
                                class="bg-slate-100 hover:bg-slate-200 cursor-pointer transition-colors">

                                <td
                                    class="px-5 py-3 pl-10 font-semibold text-slate-700 whitespace-nowrap sticky left-0 z-10 bg-slate-100">
                                    <span x-text="openDesa['{{ $desaKey }}'] ? '▾' : '▸'" class="mr-2 text-slate-400"></span>
                                    {{ $loop->iteration }}. {{ $namaDesa }}
                                </td>

                                <td x-show="$store.usahaColumns.id_wilayah" x-cloak class="px-5 py-3 bg-slate-100"></td>
                                <td x-show="$store.usahaColumns.kd_kab" x-cloak class="px-5 py-3 bg-slate-100"></td>
                                <td x-show="$store.usahaColumns.desa" x-cloak class="px-5 py-3 bg-slate-100"></td>
                                <td x-show="$store.usahaColumns.nama_sls" x-cloak class="px-5 py-3 bg-slate-100"></td>

                                <td x-show="$store.usahaColumns.ub_prelist" x-cloak class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_ub_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.um_prelist" x-cloak class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_um_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.umk_prelist" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_umk_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditemukan_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_ditemukan_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditutup_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-red-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_ditutup_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ganda_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-amber-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_ganda_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-slate-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_tidak_ditemukan_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_baru_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-blue-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_baru_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditemukan_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_ditemukan_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tutup_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_tutup_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ganda_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_ganda_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_tidak_ditemukan_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_baru_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_baru_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_ditemukan" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_keluarga_ditemukan'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_meninggal" x-cloak
                                class="px-5 py-3 text-right font-bold text-red-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_keluarga_meninggal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_eligible" x-cloak
                                class="px-5 py-3 text-right font-bold text-amber-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_keluarga_tidak_eligible'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_ditemui" x-cloak
                                class="px-5 py-3 text-right font-bold text-orange-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_keluarga_tidak_ditemui'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_ditemukan" x-cloak
                                class="px-5 py-3 text-right font-bold text-slate-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_keluarga_tidak_ditemukan'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_baru" x-cloak
                                class="px-5 py-3 text-right font-bold text-blue-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_keluarga_baru'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.prelist_usaha" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_prelist_usaha'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_realisasi" x-cloak
                                class="px-5 py-3 text-right font-bold text-sky-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_usaha_realisasi'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.prelist_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold bg-slate-100">
                                {{ number_format($desaTotals['jumlah_prelist_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_realisasi" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600 bg-slate-100">
                                {{ number_format($desaTotals['jumlah_keluarga_realisasi'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.ppl" x-cloak class="px-5 py-3 bg-slate-100"></td>

                            <td x-show="$store.usahaColumns.pml" x-cloak class="px-5 py-3 bg-slate-100"></td>

                            <td x-show="$store.usahaColumns.last_update" x-cloak class="px-5 py-3 bg-slate-100"></td>
                            </tr>

                            @foreach ($petugasGroups as $namaPetugas => $rows)
                                <tr x-show="open && openDesa['{{ $desaKey }}']" x-cloak
                                    class="bg-slate-50 hover:bg-slate-100 transition-colors">

                                    <td class="px-5 py-3 pl-14 whitespace-nowrap sticky left-0 z-10 bg-slate-50">
                                        <div class="font-semibold text-slate-600 text-sm">{{ $namaPetugas }}</div>
                                        <div class="text-xs text-slate-400">{{ $rows->first()->email_petugas ?: '-' }}</div>
                                    </td>

                                    <td x-show="$store.usahaColumns.id_wilayah" x-cloak class="px-5 py-3 bg-slate-50"></td>
                                    <td x-show="$store.usahaColumns.kd_kab" x-cloak class="px-5 py-3 bg-slate-50"></td>
                                    <td x-show="$store.usahaColumns.desa" x-cloak class="px-5 py-3 bg-slate-50"></td>
                                    <td x-show="$store.usahaColumns.nama_sls" x-cloak class="px-5 py-3 bg-slate-50"></td>

                                    <td x-show="$store.usahaColumns.ub_prelist" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_ub_prelist_awal')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.um_prelist" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_um_prelist_awal')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.umk_prelist" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_umk_prelist_awal')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_ditemukan_bku" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-emerald-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_ditemukan_bku')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_ditutup_bku" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-red-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_ditutup_bku')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_ganda_bku" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-amber-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_ganda_bku')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_bku" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-slate-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_tidak_ditemukan_bku')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_baru_bku" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-blue-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_baru_bku')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_ditemukan_keluarga" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_ditemukan_usaha_keluarga')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_tutup_keluarga" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_tutup_usaha_keluarga')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_ganda_keluarga" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_ganda_usaha_keluarga')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_keluarga" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_tidak_ditemukan_usaha_keluarga')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_baru_keluarga" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_baru_usaha_keluarga')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.keluarga_ditemukan" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-emerald-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_keluarga_ditemukan')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.keluarga_meninggal" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-red-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_keluarga_meninggal')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.keluarga_tidak_eligible" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-amber-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_keluarga_tidak_eligible')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.keluarga_tidak_ditemui" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-orange-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_keluarga_tidak_ditemui')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.keluarga_tidak_ditemukan" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-slate-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_keluarga_tidak_ditemukan')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.keluarga_baru" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-blue-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_keluarga_baru')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.prelist_usaha" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_prelist_usaha')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.usaha_realisasi" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-sky-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_usaha_realisasi')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.prelist_keluarga" x-cloak
                                        class="px-5 py-3 text-right font-semibold bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_prelist_keluarga')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.keluarga_realisasi" x-cloak
                                        class="px-5 py-3 text-right font-semibold text-emerald-600 bg-slate-50">
                                        {{ number_format($rows->sum('jumlah_keluarga_realisasi')) }}
                                    </td>

                                    <td x-show="$store.usahaColumns.ppl" x-cloak class="px-5 py-3 bg-slate-50"></td>
                                    <td x-show="$store.usahaColumns.pml" x-cloak class="px-5 py-3 bg-slate-50"></td>
                                    <td x-show="$store.usahaColumns.last_update" x-cloak class="px-5 py-3 bg-slate-50"></td>
                                </tr>

                                @foreach ($rows as $i => $row)
                                    <tr x-show="open && openDesa['{{ $desaKey }}']" x-cloak
                                        class="hover:bg-slate-50 transition-colors">

                                        <td class="px-5 py-3 text-slate-400">{{ $i + 1 }}</td>

                                        <td x-show="$store.usahaColumns.id_wilayah" x-cloak class="px-5 py-3 text-slate-600">
                                            {{ $row->id_wilayah ?: '-' }}
                                        </td>

                                        <td x-show="$store.usahaColumns.kd_kab" x-cloak class="px-5 py-3 text-slate-600">
                                            {{ $row->kd_kab ?: '-' }}
                                        </td>

                                        <td x-show="$store.usahaColumns.desa" x-cloak class="px-5 py-3 text-slate-600">
                                            {{ $row->nama_desa ?: '-' }}
                                        </td>

                                        <td x-show="$store.usahaColumns.nama_sls" x-cloak class="px-5 py-3">
                                            <div class="font-semibold text-slate-700">{{ $row->nama_sls ?: '-' }}</div>
                                        </td>

                                        <td x-show="$store.usahaColumns.ub_prelist" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_ub_prelist_awal ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.um_prelist" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_um_prelist_awal ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.umk_prelist" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_umk_prelist_awal ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_ditemukan_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-emerald-600">
                                            {{ number_format($row->jumlah_usaha_ditemukan_bku ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_ditutup_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-red-600">
                                            {{ number_format($row->jumlah_usaha_ditutup_bku ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_ganda_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-amber-600">
                                            {{ number_format($row->jumlah_usaha_ganda_bku ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-slate-600">
                                            {{ number_format($row->jumlah_usaha_tidak_ditemukan_bku ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_baru_bku" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-blue-600">
                                            {{ number_format($row->jumlah_usaha_baru_bku ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_ditemukan_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_ditemukan_usaha_keluarga ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_tutup_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_tutup_usaha_keluarga ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_ganda_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_ganda_usaha_keluarga ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_tidak_ditemukan_usaha_keluarga ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_baru_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_usaha_baru_usaha_keluarga ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.keluarga_ditemukan" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-emerald-600">
                                            {{ number_format($row->jumlah_keluarga_ditemukan ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.keluarga_meninggal" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-red-600">
                                            {{ number_format($row->jumlah_keluarga_meninggal ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.keluarga_tidak_eligible" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-amber-600">
                                            {{ number_format($row->jumlah_keluarga_tidak_eligible ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.keluarga_tidak_ditemui" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-orange-600">
                                            {{ number_format($row->jumlah_keluarga_tidak_ditemui ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.keluarga_tidak_ditemukan" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-slate-600">
                                            {{ number_format($row->jumlah_keluarga_tidak_ditemukan ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.keluarga_baru" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-blue-600">
                                            {{ number_format($row->jumlah_keluarga_baru ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.prelist_usaha" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_prelist_usaha ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.usaha_realisasi" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-sky-600">
                                            {{ number_format($row->jumlah_usaha_realisasi ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.prelist_keluarga" x-cloak
                                            class="px-5 py-3 text-right font-semibold">
                                            {{ number_format($row->jumlah_prelist_keluarga ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.keluarga_realisasi" x-cloak
                                            class="px-5 py-3 text-right font-semibold text-emerald-600">
                                            {{ number_format($row->jumlah_keluarga_realisasi ?? 0) }}
                                        </td>

                                        <td x-show="$store.usahaColumns.ppl" x-cloak class="px-5 py-3 text-slate-600">
                                            {{ $row->ppl ?: '-' }}
                                        </td>

                                        <td x-show="$store.usahaColumns.pml" x-cloak class="px-5 py-3 text-slate-600">
                                            {{ $row->pml ?: '-' }}
                                        </td>

                                        <td x-show="$store.usahaColumns.last_update" x-cloak
                                            class="px-5 py-3 text-slate-600 whitespace-nowrap">
                                            {{ $row->last_update ?: '-' }}
                                        </td>

                                    </tr>
                                @endforeach
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
                    <tfoot>
                        <tr class="bg-slate-200 border-t-2 border-slate-300">

                            <td class="px-5 py-3 font-bold text-slate-700 whitespace-nowrap">
                                TOTAL KESELURUHAN
                            </td>

                            <td x-show="$store.usahaColumns.id_wilayah" x-cloak class="px-5 py-3"></td>

                            <td x-show="$store.usahaColumns.kd_kab" x-cloak class="px-5 py-3"></td>

                            <td x-show="$store.usahaColumns.nama_sls" x-cloak class="px-5 py-3"></td>

                            <td x-show="$store.usahaColumns.ub_prelist" x-cloak class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_ub_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.um_prelist" x-cloak class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_um_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.umk_prelist" x-cloak class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_umk_prelist_awal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditemukan_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600">
                                {{ number_format($grandTotals['jumlah_usaha_ditemukan_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditutup_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-red-600">
                                {{ number_format($grandTotals['jumlah_usaha_ditutup_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ganda_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-amber-600">
                                {{ number_format($grandTotals['jumlah_usaha_ganda_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-slate-600">
                                {{ number_format($grandTotals['jumlah_usaha_tidak_ditemukan_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_baru_bku" x-cloak
                                class="px-5 py-3 text-right font-bold text-blue-600">
                                {{ number_format($grandTotals['jumlah_usaha_baru_bku'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ditemukan_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_usaha_ditemukan_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tutup_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_usaha_tutup_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_ganda_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_usaha_ganda_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_tidak_ditemukan_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_usaha_tidak_ditemukan_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_baru_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_usaha_baru_usaha_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_ditemukan" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600">
                                {{ number_format($grandTotals['jumlah_keluarga_ditemukan'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_meninggal" x-cloak
                                class="px-5 py-3 text-right font-bold text-red-600">
                                {{ number_format($grandTotals['jumlah_keluarga_meninggal'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_eligible" x-cloak
                                class="px-5 py-3 text-right font-bold text-amber-600">
                                {{ number_format($grandTotals['jumlah_keluarga_tidak_eligible'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_ditemui" x-cloak
                                class="px-5 py-3 text-right font-bold text-orange-600">
                                {{ number_format($grandTotals['jumlah_keluarga_tidak_ditemui'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_tidak_ditemukan" x-cloak
                                class="px-5 py-3 text-right font-bold text-slate-600">
                                {{ number_format($grandTotals['jumlah_keluarga_tidak_ditemukan'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_baru" x-cloak
                                class="px-5 py-3 text-right font-bold text-blue-600">
                                {{ number_format($grandTotals['jumlah_keluarga_baru'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.prelist_usaha" x-cloak class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_prelist_usaha'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.usaha_realisasi" x-cloak
                                class="px-5 py-3 text-right font-bold text-sky-600">
                                {{ number_format($grandTotals['jumlah_usaha_realisasi'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.prelist_keluarga" x-cloak
                                class="px-5 py-3 text-right font-bold">
                                {{ number_format($grandTotals['jumlah_prelist_keluarga'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.keluarga_realisasi" x-cloak
                                class="px-5 py-3 text-right font-bold text-emerald-600">
                                {{ number_format($grandTotals['jumlah_keluarga_realisasi'] ?? 0) }}
                            </td>

                            <td x-show="$store.usahaColumns.ppl" x-cloak class="px-5 py-3"></td>

                            <td x-show="$store.usahaColumns.pml" x-cloak class="px-5 py-3"></td>

                            <td x-show="$store.usahaColumns.last_update" x-cloak class="px-5 py-3"></td>

                        </tr>
                    </tfoot>
                </table>

            </div>

        </div>

    </div>

@endsection
