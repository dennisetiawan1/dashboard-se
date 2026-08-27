@extends('layouts.app')

@section('title', 'MAPS')

@section('content')
{{-- SWITCH DASHBOARD --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-2 mb-6">

    <div class="flex gap-2">

        <a
            href="{{ route('dashboard') }}"
            class="flex-1 text-center rounded-xl px-5 py-3 text-sm font-semibold
                   bg-sky-600 text-white shadow-sm">
            Assignment
        </a>

        <a
            href="{{ route('usaha') }}"
            class="flex-1 text-center rounded-xl px-5 py-3 text-sm font-semibold
                   bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
            Usaha
        </a>

    </div>

</div>

<div class="space-y-6">

    {{-- ================= FILTER ================= --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

        <div class="px-6 py-3.5 border-b bg-gradient-to-r from-sky-600 to-blue-700 text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-sky-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-sm font-semibold">Pilih tanggal dan filter data yang ingin ditampilkan</h2>
        </div>

        <form method="GET"
              action="{{ route('dashboard') }}"
              class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-6 gap-5">

                {{-- Tanggal --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        Tanggal
                    </label>

                    <select
                        name="tanggal"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/40 focus:outline-none transition">

                        @forelse ($availableDates as $date)

                            <option value="{{ $date }}"
                                {{ $selectedDate == $date ? 'selected' : '' }}>

                                {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}

                            </option>

                        @empty

                            <option>Belum ada data</option>

                        @endforelse

                    </select>
                </div>

                {{-- Petugas --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        Petugas
                    </label>

                    <select
                        id="petugas"
                        name="petugas_username"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/40 focus:outline-none transition">

                        <option value="">Semua Petugas</option>

                        @foreach($pplOptions as $p)

                            <option
                                value="{{ $p->petugas_username }}"
                                {{ $filters['petugas_username']==$p->petugas_username ? 'selected' : '' }}>

                                {{ $p->nama_petugas ?: $p->petugas_username }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- Kecamatan --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        Kecamatan
                    </label>

                   <select
                    id="kecamatan"
                    name="nama_kecamatan"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/40 focus:outline-none transition">

                        <option value="">Semua Kecamatan</option>

                        @foreach($kecamatanOptions as $kec)

                            <option value="{{ $kec }}"
                                {{ $filters['nama_kecamatan']==$kec ? 'selected' : '' }}>

                                {{ $kec }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- SLS --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        SLS Code
                    </label>

                    <input
                        type="text"
                        name="sls_code"
                        value="{{ $filters['sls_code'] }}"
                        list="sls-suggestions"
                        placeholder="Cari SLS..."
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/40 focus:outline-none transition">

                    <datalist id="sls-suggestions">

                        @foreach($slsOptions as $sls)

                            <option value="{{ $sls }}"></option>

                        @endforeach

                    </datalist>

                </div>

                {{-- Role Petugas --}}
                <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                    Role Petugas
                </label>

                <select
                    name="petugas_role"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/40 focus:outline-none transition">

                    <option value="Pencacah"
                        {{ $filters['petugas_role'] == 'Pencacah' ? 'selected' : '' }}>
                        Pencacah
                    </option>

                    <option value="Pengawas"
                        {{ $filters['petugas_role'] == 'Pengawas' ? 'selected' : '' }}>
                        Pengawas
                    </option>

                </select>
            </div>

                {{-- Button --}}
                <div class="flex items-end gap-2">

                    <button
                        type="submit"
                        class="flex-1 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold transition h-11 shadow-sm hover:shadow">

                        Filter Data

                    </button>

                    @if($filters['petugas_username'] || $filters['petugas_role'] || $filters['sls_code'] || $filters['nama_kecamatan'])

                    <a
                        href="{{ route('dashboard',['tanggal'=>$selectedDate]) }}"
                        class="rounded-xl border border-slate-300 px-4 h-11 flex items-center text-sm font-medium text-slate-600 hover:bg-slate-100 transition">

                        Reset

                    </a>

                    @endif

                </div>

            </div>

        </form>

    </div>

    {{-- ================= EXPORT ================= --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

            <div>

                <h3 class="text-lg font-semibold text-slate-800">
                    Export Data
                </h3>

                <p class="text-sm text-slate-500 mt-1">
                    Export data sesuai filter yang sedang dipilih.
                </p>

            </div>

            <form method="GET"
                  action="{{ route('export') }}"
                  class="flex flex-wrap gap-4 items-end">

                <input type="hidden" name="tanggal" value="{{ $selectedDate }}">
                <input type="hidden" name="petugas_username" value="{{ $filters['petugas_username'] }}">
                <input type="hidden" name="petugas_role" value="{{ $filters['petugas_role'] }}">
                <input type="hidden" name="sls_code" value="{{ $filters['sls_code'] }}">
                <input type="hidden" name="nama_kecamatan" value="{{ $filters['nama_kecamatan'] }}">

                <div>

                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        Cakupan
                    </label>

                    <select
                        name="scope"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 w-56 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/40 focus:outline-none transition">

                        <option value="current">

                            {{ $selectedDate
                                ? \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y')
                                : '-' }}

                        </option>

                    </select>

                </div>

                <div>

                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        Format
                    </label>

                    <input type="hidden" name="format" value="xlsx">

                    <input
                        type="text"
                        value="Excel"
                        readonly
                        class="rounded-xl border border-slate-300 bg-gray-100 px-3 py-2.5 text-sm text-slate-400 w-36 cursor-not-allowed">

                </div>

                <button
                    class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-6 h-11 text-sm font-semibold shadow-sm hover:shadow transition">

                    Export

                </button>

            </form>

        </div>

    </div>

    @if (empty($availableDates) || $availableDates->isEmpty())

        <div class="bg-white rounded-2xl border border-slate-200 p-14 text-center">
            <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6 4h6m2 5H7a2 2 0 01-2-2V4a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V20a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-slate-500 mb-3">Belum ada data yang diupload.</p>
            <a href="{{ route('uploads.index') }}" class="text-blue-600 font-medium hover:underline">Upload file pertama Anda &rarr;</a>
        </div>

    @else

    <div class="space-y-6">

            {{-- ================= KPI HIGHLIGHT ================= --}}
        <div class="grid md:grid-cols-3 gap-5">

            {{-- ================= NON OPEN ================= --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-500 to-cyan-600 p-6 shadow-md">
                <div class="absolute -right-12 -bottom-12 w-44 h-44 rounded-full bg-white/10"></div>
                <div class="absolute right-8 top-8 w-20 h-20 rounded-full border border-white/10"></div>
                <div class="relative z-10">
                    <p class="text-sky-100 text-xs font-semibold uppercase tracking-widest">% Non Open</p>
                    <div class="mt-2 text-5xl font-extrabold text-white">{{ $summary['pct_non_open'] }}%</div>
                </div>
                <div class="relative z-10 mt-5 border-t border-white/20 pt-4">
                    <p class="text-sm text-sky-100 leading-relaxed">
                        <strong class="text-white">{{ number_format($summary['non_open']) }}</strong>
                        dari
                        <strong class="text-white">{{ number_format($summary['total']) }}</strong>
                        assignment telah keluar dari status <strong class="text-white">Open</strong>.
                    </p>
                </div>
            </div>

            {{-- ================= SELAIN OPEN & DRAFT ================= --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-500 to-cyan-700 p-6 shadow-md">
                <div class="absolute -right-12 -bottom-12 w-44 h-44 rounded-full bg-white/10"></div>
                <div class="absolute right-8 top-8 w-20 h-20 rounded-full border border-white/10"></div>
                <div class="relative z-10">
                    <p class="text-teal-100 text-xs font-semibold uppercase tracking-widest">% Selain Open & Draft</p>
                    <div class="mt-2 text-5xl font-extrabold text-white">{{ $summary['pct_submitted'] }}%</div>
                </div>
                <div class="relative z-10 mt-5 border-t border-white/20 pt-4">
                    <p class="text-sm text-teal-100 leading-relaxed">
                        <strong class="text-white">{{ number_format($summary['non_open_draft']) }}</strong>
                        assignment telah mencapai status
                        <strong class="text-white">Submitted</strong>,
                        <strong class="text-white">Approved</strong>
                        atau
                        <strong class="text-white">Rejected</strong>.
                    </p>
                </div>
            </div>

            {{-- ================= APPROVED ================= --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-500 to-green-700 p-6 shadow-md">
                <div class="absolute -right-12 -bottom-12 w-44 h-44 rounded-full bg-white/10"></div>
                <div class="absolute right-8 top-8 w-20 h-20 rounded-full border border-white/10"></div>
                <div class="relative z-10">
                    <p class="text-emerald-100 text-xs font-semibold uppercase tracking-widest">% Approved</p>
                    <div class="mt-2 text-5xl font-extrabold text-white">{{ $summary['pct_approved'] }}%</div>
                </div>
                <div class="relative z-10 mt-5 border-t border-white/20 pt-4">
                    <p class="text-sm text-emerald-100 leading-relaxed">
                        <strong class="text-white">{{ number_format($summary['approved']) }}</strong>
                        dari
                        <strong class="text-white">{{ number_format($summary['total']) }}</strong>
                        assignment telah mencapai status <strong class="text-white">Approved</strong>.
                    </p>
                </div>
            </div>

        </div>

        {{-- ================= KPI CARDS ================= --}}
        <div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 h-full">

                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-slate-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>

                        </svg>

                    </div>

                    <div class="text-sm font-extrabold uppercase tracking-wide text-slate-600">
                        Total Assignment
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['total']) }}
                    </div>

                    <div class="mt-4">
                        @include('dashboard.partials.delta', ['delta' => $comparison['total'] ?? null])
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 h-full">

                    <div class="w-11 h-11 rounded-xl bg-sky-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-sky-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>

                    </div>

                    <div class="text-sm font-extrabold uppercase tracking-wide text-sky-600">
                        Open
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['open']) }}
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $summary['pct_open'] }}% dari total
                    </p>

                    <div class="mt-4">
                        @include('dashboard.partials.delta', ['delta' => $comparison['open'] ?? null])
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 h-full">

                    <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-amber-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M9 13h6m-6 4h6m2 5H7a2 2 0 01-2-2V4a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V20a2 2 0 01-2 2z"/>

                        </svg>

                    </div>

                    <div class="text-sm font-extrabold uppercase tracking-wide text-amber-600">
                        Draft
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['draft']) }}
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $summary['pct_draft'] }}% dari total
                    </p>

                    <div class="mt-4">
                        @include('dashboard.partials.delta', ['delta' => $comparison['draft'] ?? null])
                    </div>

                </div>

               <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 h-full">

                    <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-blue-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>

                        </svg>

                    </div>

                    <div class="text-sm font-extrabold uppercase tracking-wide text-blue-600">
                        Submitted
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['submitted']) }}
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $summary['pct_submitted_pencacah'] }}% dari total
                    </p>

                    <div class="mt-4">
                        @include('dashboard.partials.delta', ['delta' => $comparison['submitted'] ?? null])
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 h-full">

                    <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-emerald-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>

                        </svg>

                    </div>

                    <div class="text-sm font-extrabold uppercase tracking-wide text-emerald-600">
                        Approved
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['approved']) }}
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $summary['pct_approved'] }}% dari total
                    </p>

                    <div class="mt-4">
                        @include('dashboard.partials.delta', ['delta' => $comparison['approved'] ?? null])
                    </div>

                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-5 h-full">

                    <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center mb-4">

                        <svg class="w-5 h-5 text-red-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M6 18L18 6M6 6l12 12"/>

                        </svg>

                    </div>

                    <div class="text-sm font-extrabold uppercase tracking-wide text-red-600">
                        Rejected
                    </div>

                    <div class="mt-2 text-2xl font-bold text-slate-800">
                        {{ number_format($summary['rejected']) }}
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $summary['pct_rejected'] }}% dari total
                    </p>

                    <div class="mt-4">
                        @include('dashboard.partials.delta', ['delta' => $comparison['rejected'] ?? null])
                    </div>

                </div>
            </div>

            @if ($comparison)
            @endif

        </div>

        {{-- ================= GRAFIK ================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide">Tren Status Assignment (Histori 7 Hari Terakhir)</h3>
                </div>
            <div class="px-5 pb-5">
                <div class="relative h-[360px]">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide">Komposisi Status Hari Ini</h3>
                </div>
            <div class="px-5 pb-5">
                <div class="relative h-[360px]">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
            </div>
        </div>

        {{-- ================= TABEL PER PETUGAS (DIKELOMPOKKAN PER KECAMATAN) ================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                    Ringkasan Status Assignment per Petugas
                    ({{ \Illuminate\Support\Carbon::parse($selectedDate)->translatedFormat('d F Y') }})
                </h3>
            </div>
            <div class="table-scroll">
                <table class="w-full text-sm">
                   <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 sticky top-0 z-10">
                       <tr>
                        <th class="text-left px-5 py-4 font-bold whitespace-nowrap">#</th>

                        <th class="text-left px-5 py-4 font-bold whitespace-nowrap">
                            Petugas / Kecamatan
                        </th>

                        <th class="text-right px-5 py-4 font-bold whitespace-nowrap">
                            Total Assignment
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-sky-600 whitespace-nowrap">
                            Open
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-amber-600 whitespace-nowrap">
                            Draft
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-blue-600 whitespace-nowrap">
                            Submitted
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-emerald-600 whitespace-nowrap">
                            Approved
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-red-600 whitespace-nowrap">
                            Rejected
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-slate-600 whitespace-nowrap">
                            Non Open
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-indigo-600 whitespace-nowrap">
                            Submit+
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-cyan-600 whitespace-nowrap">
                            % Non Open
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-teal-600 whitespace-nowrap">
                            % Selain Open & Draft
                        </th>

                        <th class="text-right px-5 py-4 font-bold text-emerald-600 whitespace-nowrap">
                        % Approved
                        </th>
                    </tr>

                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($perPetugasGrouped as $group)
                            <tr
                                class="bg-sky-50/70 border-y border-sky-100 cursor-pointer"
                                onclick="toggleGroup('group-{{ $loop->index }}')">

                                <td class="px-5 py-3"></td>

                                <td class="px-5 py-3 font-bold text-sky-800">
                                    <span
                                        id="icon-group-{{ $loop->index }}"
                                        class="inline-block w-4">
                                        ▸
                                    </span>

                                    📍 {{ $group['label'] }}
                                </td>

                                <td class="px-5 py-3 text-right font-bold text-slate-800">
                                {{ number_format($group['subtotal']['total_assignment']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-sky-600">
                                {{ number_format($group['subtotal']['status_open']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-amber-600">
                                {{ number_format($group['subtotal']['status_draft']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-blue-600">
                                {{ number_format($group['subtotal']['status_submitted_pencacah']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-emerald-600">
                                {{ number_format($group['subtotal']['status_approved_pengawas']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-red-600">
                                {{ number_format($group['subtotal']['status_rejected_pengawas']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-slate-600">
                                {{ number_format($group['subtotal']['status_non_open']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-indigo-600">
                                {{ number_format($group['subtotal']['status_non_open_draft']) }}
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-cyan-700">
                                {{ $group['subtotal']['pct_non_open'] }}%
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-teal-700">
                                {{ $group['subtotal']['pct_submitted'] }}%
                                </td>

                                <td class="px-5 py-3 text-right font-semibold text-emerald-700">
                                {{ $group['subtotal']['pct_approved'] }}%
                                </td>

                            </tr>

                          @foreach ($group['petugas'] as $i => $row)
                            <tr
                                class="group-{{ $loop->parent->index }} hidden hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-5 py-3">
                                        <div class="font-medium text-slate-700">{{ $row->nama_petugas ?: $row->petugas_username }}</div>
                                        <div class="text-xs text-slate-400">{{ $row->petugas_username }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ number_format($row->total_assignment) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-sky-600">{{ number_format($row->status_open) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-amber-600">{{ number_format($row->status_draft) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-blue-600">{{ number_format($row->status_submitted_pencacah) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ number_format($row->status_approved_pengawas) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-red-600">{{ number_format($row->status_rejected_pengawas) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-600">{{ number_format($row->status_non_open) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-indigo-600">{{ number_format($row->status_non_open_draft) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-cyan-700">{{ $row->pct_non_open }}%</td>
                                    <td class="px-5 py-3 text-right font-semibold text-teal-700">{{ $row->pct_submitted }}%</td>
                                    <td class="px-5 py-3 text-right font-bold text-emerald-300">{{ $row['pct_approved'] }}%</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="13" class="px-5 py-8 text-center text-slate-400">Tidak ada data untuk filter ini.</td></tr>
                        @endforelse

                        @if ($perPetugasGrouped->isNotEmpty())
                            <tr class="bg-slate-800">
                                <td class="px-5 py-3"></td>
                                <td class="px-5 py-3 font-bold text-white">GRAND TOTAL</td>
                                <td class="px-5 py-3 text-right font-bold text-slate-300">{{ number_format($summary['total']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-red-300">{{ number_format($summary['open']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-amber-300">{{ number_format($summary['draft']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-blue-300">{{ number_format($summary['submitted']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-emerald-300">{{ number_format($summary['approved']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-orange-300">{{ number_format($summary['rejected']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-slate-300">{{ number_format($summary['non_open']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-indigo-300">{{ number_format($summary['non_open_draft']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-sky-300">{{ $summary['pct_non_open'] }}%</td>
                                <td class="px-5 py-3 text-right font-bold text-teal-300">{{ $summary['pct_submitted'] }}%</td>
                                <td class="px-5 py-3 text-right font-bold text-emerald-300">{{ $summary['pct_approved'] }}%</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @endif

</div>

    <script id="dashboard-data" type="application/json">
    {!! json_encode([
        'trend' => $trend,
        'summary' => $summary,
        'selectedLabel' => request()->has('tanggal') && $selectedDate
            ? \Illuminate\Support\Carbon::parse($selectedDate)->translatedFormat('d M')
            : null,
    ]) !!}
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>

    <script>
    const kecamatanPetugasMap = @json($kecamatanPetugasMap);
    const kecamatanSlsMap = @json($kecamatanSlsMap);

    const allPetugas = @json($pplOptions->map(fn($p) => ['username' => $p->petugas_username, 'nama' => $p->nama_petugas ?? $p->petugas_username]));
    const allSls = @json($slsOptions);

    const selKecamatan = document.querySelector('select[name="nama_kecamatan"]');
    const selPetugas = document.querySelector('select[name="petugas_username"]');
    const selSls = document.querySelector('input[name="sls_code"]');
    const datalistSls = document.getElementById('sls-suggestions');

    const currentPetugas = "{{ $filters['petugas_username'] }}";

    function updateDropdowns(kecamatan) {
        const petugasList = kecamatan && kecamatanPetugasMap[kecamatan]
            ? kecamatanPetugasMap[kecamatan]
            : allPetugas;

        selPetugas.innerHTML = '<option value="">Semua Petugas</option>';
        petugasList.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.username;
            opt.textContent = p.nama || p.username;
            if (p.username === currentPetugas) opt.selected = true;
            selPetugas.appendChild(opt);
        });

        const slsList = kecamatan && kecamatanSlsMap[kecamatan]
            ? kecamatanSlsMap[kecamatan]
            : allSls;

        datalistSls.innerHTML = '';
        slsList.forEach(sls => {
            const opt = document.createElement('option');
            opt.value = sls;
            datalistSls.appendChild(opt);
        });
    }

    updateDropdowns(selKecamatan.value);

    selKecamatan.addEventListener('change', function() {
        updateDropdowns(this.value);
        selPetugas.value = '';
        if (selSls) selSls.value = '';
    });
    </script>

    <script>
function toggleGroup(group){

    const rows = document.querySelectorAll("." + group);

    rows.forEach(function(row){
        row.classList.toggle("hidden");
    });

    const icon = document.getElementById("icon-" + group);

    if(icon.textContent.trim() === "▸"){
        icon.textContent = "▾";
    }else{
        icon.textContent = "▸";
    }

}
</script>

@endsection