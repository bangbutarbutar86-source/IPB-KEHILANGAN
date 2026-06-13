{{--
     ============================================================
     Fungsi: Template induk (layout) yang berisi sidebar, topbar,
             dan tempat konten halaman ditampilkan (@yield).
             Semua halaman admin pakai layout ini.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — IPB Kehilangan</title>

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ipb-blue:       #3D4BA0;
            --ipb-blue-dark:  #2d3880;
            --ipb-blue-light: #eef0fa;
            --ipb-yellow:     #F5C518;
            --sidebar-w:      220px;
            --topbar-h:       60px;
            --text:           #1a1a2e;
            --text-muted:     #6b7280;
            --border:         #e5e7eb;
            --bg:             #f4f5fb;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--ipb-blue);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        .sidebar-logo .brand {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .sidebar-logo .sub {
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            margin-top: 3px;
        }
        .badge-admin {
            display: inline-block;
            margin-top: 10px;
            background: var(--ipb-yellow);
            color: var(--ipb-blue);
            font-size: 10px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }

        .nav-section { padding: 16px 0 4px; }
        .nav-label {
            font-size: 9px;
            color: rgba(255,255,255,0.35);
            padding: 0 20px 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 20px;
            color: rgba(255,255,255,0.65);
            font-size: 13px;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }
        .nav-item.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
            border-left-color: var(--ipb-yellow);
        }
        .nav-item svg { width: 17px; height: 17px; opacity: 0.85; flex-shrink: 0; }
        .nav-badge {
            margin-left: auto;
            background: #e24b4a;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .sidebar-bottom {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.12);
            padding: 16px 20px;
        }
        .admin-info { display: flex; align-items: center; gap: 10px; }
        .avatar-circle {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--ipb-yellow);
            color: var(--ipb-blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600;
            flex-shrink: 0;
        }
        .admin-info .name { font-size: 12px; font-weight: 600; color: #fff; }
        .admin-info .role { font-size: 10px; color: rgba(255,255,255,0.45); }

        /* ── MAIN AREA ── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── TOPBAR ── */
        .topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }
        .topbar-search {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 14px;
            background: var(--bg);
            font-size: 13px;
            color: var(--text-muted);
            min-width: 220px;
        }
        .topbar-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 13px;
            color: var(--text);
            width: 100%;
        }
        .topbar-admin {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-muted);
        }
        .logout-btn {
            font-size: 12px;
            color: #e24b4a;
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fff5f5;
        }
        .logout-btn:hover { background: #fef2f2; }

        /* ── CONTENT ── */
        .content {
            padding: 28px;
            flex: 1;
        }

        /* ── ALERT ── */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .alert-success { background: #eaf3de; color: #3b6d11; border: 1px solid #c0dd97; }
        .alert-error   { background: #fcebeb; color: #a32d2d; border: 1px solid #f7c1c1; }
    </style>

    @stack('styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="brand">IPB KEHILANGAN</div>
        <div class="sub">Sistem Barang Hilang IPB</div>
        <div class="badge-admin">ADMIN PANEL</div>
    </div>

    <nav class="nav-section">
        <div class="nav-label">Menu Utama</div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('admin.laporan.index') }}"
           class="nav-item {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9 12h6M9 16h6M9 8h3M5 4h14a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/>
            </svg>
            Kelola Laporan
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Manajemen User
        </a>
    </nav>

    <div class="sidebar-bottom">
        <div class="admin-info">
            <div class="avatar-circle">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
            </div>
            <div>
                <div class="name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="role">Super Admin</div>
            </div>
        </div>
    </div>
</aside>

{{-- MAIN --}}
<div class="main-wrapper">

    <header class="topbar">
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-admin">
            {{ auth()->user()->name ?? 'Admin' }}
        </div>
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="logout-btn">
            Keluar
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </header>

    <main class="content">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>