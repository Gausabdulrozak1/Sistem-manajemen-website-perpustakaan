<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f3">
    <title>@yield('title', 'RuangBaca') · RuangBaca</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>
        <aside class="sidebar" id="main-sidebar" aria-label="Navigasi utama">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="RuangBaca, ke dashboard">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M6 8.5c3.8-.9 7.2-.2 10 2.1v14c-2.8-2.3-6.2-3-10-2.1v-14Zm20 0c-3.8-.9-7.2-.2-10 2.1v14c2.8-2.3 6.2-3 10-2.1v-14Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M16 10.6v14" stroke="currentColor" stroke-width="1.8"/></svg>
                </span>
                <span class="brand-copy"><strong>RuangBaca</strong><small>Manajemen Perpustakaan</small></span>
            </a>

            <div class="sidebar-label">MENU UTAMA</div>
            <nav class="primary-nav">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="4" rx="1.5"/><rect x="13.5" y="10.5" width="7" height="10" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/></svg>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link {{ request()->routeIs('learning') ? 'is-active' : '' }}" href="{{ route('learning') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5-5 7 5 7m8-14 5 7-5 7M14 4l-4 16"/></svg>
                    <span>Belajar Laravel</span>
                </a>
                <a class="nav-link {{ request()->routeIs('books.*') ? 'is-active' : '' }}" href="{{ route('books.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 4.5v13A2.5 2.5 0 0 0 7.5 20M9 8h6M9 11h6"/></svg>
                    <span>Koleksi Buku</span>
                </a>
                <a class="nav-link {{ request()->routeIs('loans.*') ? 'is-active' : '' }}" href="{{ route('loans.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5h10A2.5 2.5 0 0 1 16.5 8v12H7A3 3 0 0 1 4 17V5.5Z"/><path d="M16.5 10H20v10h-9M8 9h5M8 12h5M8 15h3"/></svg>
                    <span>Sirkulasi</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}" href="{{ route('reports.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h16"/><path d="m7 15 4-4 3 2 5-6"/><circle cx="7" cy="15" r=".7"/><circle cx="11" cy="11" r=".7"/><circle cx="14" cy="13" r=".7"/><circle cx="19" cy="7" r=".7"/></svg>
                    <span>Laporan</span>
                </a>
                @can('manage-users')
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'is-active' : '' }}" href="{{ route('users.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M17 8v6m-3-3h6"/></svg>
                        <span>Pengguna</span>
                    </a>
                @endcan
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'is-active' : '' }}" href="{{ route('categories.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20.5 13.2-7.3 7.3L3 10.3V3h7.3l10.2 10.2Z"/><circle cx="7.5" cy="7.5" r="1"/></svg>
                    <span>Kategori</span>
                </a>
                <a class="nav-link {{ request()->routeIs('members.*') ? 'is-active' : '' }}" href="{{ route('members.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M16 5.2a3.5 3.5 0 0 1 0 6.6M17 14a5.6 5.6 0 0 1 4.2 5.4"/></svg>
                    <span>Anggota</span>
                </a>
            </nav>

            <div class="sidebar-note">
                <span class="sidebar-note-icon" aria-hidden="true">✦</span>
                <p><strong>Ruang untuk bertumbuh</strong><br>Kelola koleksi dan bantu lebih banyak orang menemukan bacaan.</p>
            </div>
            <div class="sidebar-footer"><span class="status-dot"></span> Sistem perpustakaan <span class="sidebar-version">v1.0</span></div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <button class="menu-toggle" type="button" aria-label="Buka navigasi" aria-controls="main-sidebar" aria-expanded="false" data-sidebar-toggle>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="topbar-context"><span>Perpustakaan</span><span class="crumb-divider">/</span><strong>@yield('breadcrumb', 'Dashboard')</strong></div>
                <div class="topbar-right">
                    <span class="today-label">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="topbar-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm6-6V11a6 6 0 0 0-5-5.9V4a1 1 0 0 0-2 0v1.1A6 6 0 0 0 6 11v4l-2 2h16l-2-2Z"/></svg></span>
                    <span class="topbar-user">{{ auth()->user()->name }}</span>
                    <form class="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit" aria-label="Keluar" title="Keluar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3m9-8h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"/></svg><span>Keluar</span></button>
                    </form>
                </div>
            </header>
            <div class="page-content">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
