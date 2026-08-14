<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAPS - Monitoring Assignment Progress System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-bps.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col md:flex-row">

    {{-- Header brand kecil khusus mobile (muncul saat panel biru disembunyikan) --}}
    <div class="md:hidden bg-[#003d7a] px-6 py-5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l4-6 3 4 5-8"/>
            </svg>
        </div>
        <div>
            <p class="text-white font-extrabold text-sm leading-tight tracking-wide">MAPS</p>
            <p class="text-blue-200 text-[11px] leading-tight">Monitoring Assignment Progress System</p>
        </div>
    </div>

    {{-- Sisi Kiri (biru) --}}
    <div class="hidden md:block md:w-1/2 bg-[#003d7a] relative overflow-hidden" style="min-height:100vh;">

        {{-- Dekorasi lingkaran background --}}
        <div style="position:absolute;width:400px;height:400px;border-radius:50%;border:1px solid rgba(255,255,255,0.05);top:-100px;right:-100px;"></div>
        <div style="position:absolute;width:300px;height:300px;border-radius:50%;border:1px solid rgba(255,255,255,0.05);bottom:-50px;left:-50px;"></div>

        <div style="position:absolute;top:50%;left:3rem;transform:translateY(-50%);z-index:10;">

            <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center mb-6">
                <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l4-6 3 4 5-8"/>
                </svg>
            </div>

            <p class="text-blue-300 text-sm font-medium mb-2">
            Selamat Datang 👋
            </p>

            <h1 class="text-white text-5xl font-extrabold tracking-wide mb-2">
                MAPS
            </h1>

            <p class="text-blue-200 text-lg mb-6">
            Monitoring Assignment Progress System
            </p>
        </div>

        <div style="position:absolute;top:0;bottom:0;left:0;right:0;opacity:0.45;z-index:5;">
            <canvas id="loginChart" style="width:100%;height:100%;"></canvas>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
        const ctx = document.getElementById('loginChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['25 Jun', '26 Jun', '27 Jun', '28 Jun', '29 Jun', '30 Jun', '01 Jul'],
                datasets: [
                    {
                        label: 'Non Open',
                        data: [22000, 30000, 38000, 45000, 48000, 50000, 52748],
                        backgroundColor: 'rgba(255,255,255,0.5)',
                        borderRadius: 4,
                        order: 2,
                    },
                    {
                        label: 'Total Assignment',
                        data: [158000, 162000, 165000, 168000, 170000, 172000, 174575],
                        backgroundColor: 'rgba(255,255,255,0.15)',
                        borderRadius: 4,
                        order: 3,
                    },
                    {
                        label: 'Tren',
                        data: [22000, 30000, 38000, 45000, 48000, 50000, 52748],
                        type: 'line',
                        borderColor: 'rgba(251,191,36,0.8)',
                        backgroundColor: 'transparent',
                        pointBackgroundColor: 'rgba(251,191,36,0.8)',
                        pointRadius: 4,
                        borderWidth: 2,
                        tension: 0.4,
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: {
                        ticks: { display: false },
                        grid: { color: 'rgba(255,255,255,0.05)' },
                    },
                    y: {
                        ticks: { display: false },
                        grid: { color: 'rgba(255,255,255,0.05)' },
                    }
                }
            }
        });
        </script>
    </div>

    {{-- Sisi Kanan (putih) --}}
    <div class="w-full md:w-1/2 bg-white relative overflow-hidden flex items-center justify-center p-8 flex-1" style="min-height:calc(100vh - 0px);">

        {{-- Dekorasi halus, gema dari motif lingkaran sisi kiri --}}
        <div class="pointer-events-none absolute -top-24 -right-24 w-80 h-80 rounded-full bg-gradient-to-br from-sky-50 to-transparent"></div>
        <div class="pointer-events-none absolute top-16 right-16 w-56 h-56 rounded-full border border-slate-100"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-16 w-64 h-64 rounded-full border border-slate-100"></div>
        <div class="pointer-events-none absolute bottom-24 left-24 w-24 h-24 rounded-full bg-gradient-to-tr from-amber-50 to-transparent"></div>

        <div class="w-full max-w-sm relative z-10">

            <h1 class="text-2xl font-bold text-slate-800 mb-1">
                Masuk sebagai Admin
            </h1>

            <p class="text-sm text-slate-400 mb-8">
                Masukkan password untuk mengakses fitur upload data.
            </p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        Password Admin
                    </label>

                    <div class="relative">
                        <input
                            id="passwordInput"
                            type="password"
                            name="password"
                            required
                            autofocus
                            placeholder="Masukkan password..."
                            class="w-full border border-slate-300 rounded-xl pl-4 pr-11 py-3 text-sm focus:outline-none focus:border-[#003d7a] focus:ring-2 focus:ring-[#003d7a]/30 transition">

                        <button
                            type="button"
                            id="togglePassword"
                            aria-label="Tampilkan password"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition">
                            {{-- Icon: mata terbuka (default) --}}
                            <svg id="iconEyeOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{-- Icon: mata dicoret (saat password ditampilkan) --}}
                            <svg id="iconEyeOff" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.828 2.83M9.363 5.365A9.466 9.466 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.523 10.523 0 01-4.293 5.32M6.223 6.223A10.45 10.45 0 002.458 12c1.274 4.057 5.065 7 9.542 7a9.46 9.46 0 004.293-1.02"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full bg-[#003d7a] hover:bg-[#002d5c] text-white font-semibold rounded-xl px-4 py-3 text-sm flex items-center justify-center gap-2 shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                    </svg>
                    Masuk
                </button>

            </form>

        </div>
    </div>

    <script>
        const pwInput = document.getElementById('passwordInput');
        const toggleBtn = document.getElementById('togglePassword');
        const eyeOpen = document.getElementById('iconEyeOpen');
        const eyeOff = document.getElementById('iconEyeOff');

        toggleBtn.addEventListener('click', function () {
            const isHidden = pwInput.type === 'password';
            pwInput.type = isHidden ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isHidden);
            eyeOff.classList.toggle('hidden', !isHidden);
            toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        });
    </script>

</body>
</html>