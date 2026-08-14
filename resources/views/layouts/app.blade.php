<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MAPS')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-bps.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        #sidebar { width: 56px; overflow: hidden; transition: width 0.3s ease; }
        #sidebar.expanded { width: 224px; }
        #sidebar .sidebar-text { display: none; white-space: nowrap; }
        #sidebar.expanded .sidebar-text { display: inline; }
        #sidebar .sidebar-logo { display: none; }
        #sidebar.expanded .sidebar-logo { display: block; }
        #sidebar .sidebar-icon-only { display: flex; }
        #sidebar.expanded .sidebar-icon-only { display: none; }
        #sidebar .nav-link { justify-content: center; }
        #sidebar.expanded .nav-link { justify-content: flex-start; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    <div class="flex min-h-screen w-full" id="app-wrapper">

        <!-- Sidebar -->
        <aside id="sidebar" class="flex flex-col bg-[#003d7a] border-r border-blue-900 shrink-0">

            {{-- Header sidebar: collapsed = logo kecil, expanded = logo + teks --}}
            <div class="h-20 flex items-center border-b border-blue-800 px-3">
                <div class="sidebar-icon-only">
                    <img src="{{ asset('images/logo-bps.png') }}" alt="BPS" class="w-16 h-16 object-contain">
                </div>
                <div class="sidebar-logo flex items-center gap-3">
                    <img src="{{ asset('images/logo-bps.png') }}" alt="BPS" class="w-10 h-10 object-contain shrink-0">
                    <div>
                        <div class="font-bold text-white text-sm leading-tight">MAPS</div>
                        <div class="text-blue-300 text-[10px] leading-snug">Monitoring Assignment Progress System</div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-2 py-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="nav-link flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-blue-200 hover:bg-white/5 hover:text-white' }}"
                   title="Dashboard">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    <span class="sidebar-text">Dashboard</span>
                </a>
                @if(session('role') === 'admin')
                <a href="{{ route('uploads.index') }}"
                   class="nav-link flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('uploads.*') ? 'bg-white/10 text-white' : 'text-blue-200 hover:bg-white/5 hover:text-white' }}"
                   title="Upload Data">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/></svg>
                    <span class="sidebar-text">Upload Data</span>
                </a>
                @endif
            </nav>

            <div class="p-2 border-t border-blue-800 text-center">
                <div class="sidebar-text text-[10px] text-blue-400">&copy; {{ date('Y') }} BPS Ogan Ilir</div>
                <div class="sidebar-icon-only text-[10px] text-blue-600">&copy;</div>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500" title="Sembunyikan/tampilkan sidebar">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="w-8 h-8 rounded-full bg-[#003d7a] flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold text-slate-800 leading-tight">@yield('header_title', 'MAPS')</h1>
                        <p class="text-[11px] text-slate-400">@yield('header_subtitle', 'Monitoring Assignment Progress System')</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden md:block">
                        <div class="text-xs font-semibold text-slate-600">{{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y') }}</div>
                        <div class="text-[11px] text-slate-400">{{ \Illuminate\Support\Carbon::now()->translatedFormat('l') }}</div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-[#003d7a]/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#003d7a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    @if(session('role') === 'admin')
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center hover:bg-red-100" title="Logout">
                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center hover:bg-blue-100" title="Login Admin">
                        <svg class="w-4 h-4 text-[#003d7a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A8.966 8.966 0 0112 15a8.966 8.966 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </a>
                    @endif
                </div>
            </header>

            <main class="flex-1 p-4 md:p-6 space-y-6">
                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('expanded');
        }
    </script>

</body>
</html>