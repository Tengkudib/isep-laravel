@php
    $__user = auth()->user();
    $__role = $__user->role ?? '';
    $__role_label = ['student' => t('Pelajar', 'Student'), 'lecturer' => t('Pensyarah', 'Lecturer'), 'admin' => t('Pentadbir', 'Admin')][$__role] ?? $__role;
    $__active = fn (...$names) => request()->routeIs(...$names) ? 'active' : '';
@endphp
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('isep-theme') || 'light');</script>
<style>
    .isep-navbar {
        background: #0a1128;
        padding: 10px 24px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
        font-family: 'Segoe UI', sans-serif;
    }
    .isep-navbar .brand { color: white; font-weight: 700; text-decoration: none; display:flex; align-items:center; gap:8px; }
    .isep-navbar .links { display: flex; gap: 4px; flex-wrap: wrap; }
    .isep-navbar .links a {
        color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.88rem;
        padding: 7px 14px; border-radius: var(--isep-r-md); transition: all var(--isep-duration) var(--isep-ease);
    }
    .isep-navbar .links a:hover { background: rgba(255,255,255,0.1); color: white; }
    .isep-navbar .links a.active { background: rgba(255,255,255,0.18); color: white; font-weight: 600; }
    [data-theme="dark"] .isep-navbar .links a { color: rgba(255,255,255,0.7) !important; }
    [data-theme="dark"] .isep-navbar .links a.active,
    [data-theme="dark"] .isep-navbar .links a:hover { color: #ffffff !important; }
    .isep-navbar .right { display: flex; align-items: center; gap: 10px; }
    .isep-navbar .role-badge {
        font-size: 0.72rem; padding: 3px 10px; border-radius: 999px; font-weight: 600; text-transform: uppercase;
    }
    .isep-navbar .role-badge.student { background: #0d6efd33; color: #7db4ff; }
    .isep-navbar .role-badge.lecturer { background: #19875433; color: #6fd99a; }
    .isep-navbar .role-badge.admin { background: #ffc10733; color: #ffdb70; }
    .isep-navbar .logout-btn {
        color: white; text-decoration: none; font-size: 0.85rem;
        padding: 6px 14px; border: 1px solid rgba(255,255,255,0.3); border-radius: var(--isep-r-md);
        transition: background var(--isep-duration) var(--isep-ease);
    }
    .isep-navbar .logout-btn:hover { background: rgba(255,255,255,0.1); }
    .isep-navbar .theme-toggle-nav-btn {
        background: rgba(255,255,255,0.1); color: white; border: none; border-radius: var(--isep-r-md);
        width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: background var(--isep-duration) var(--isep-ease);
    }
    .isep-navbar .theme-toggle-nav-btn:hover { background: rgba(255,255,255,0.2); }
    .isep-navbar .lang-toggle-nav-btn {
        background: rgba(255,255,255,0.1); color: white; border: none; border-radius: var(--isep-r-md);
        height: 34px; padding: 0 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;
        font-size: 0.78rem; font-weight: 700; transition: background var(--isep-duration) var(--isep-ease);
    }
    .isep-navbar .lang-toggle-nav-btn:hover { background: rgba(255,255,255,0.2); }
</style>
<nav class="isep-navbar">
    <a href="{{ route('home') }}" class="brand"><img src="{{ asset('assets/img/iSEP-256.png') }}" alt="iSEP" style="height:28px;"> iSEP</a>

    <div class="links">
        @if ($__role === 'student')
            <a href="{{ route('student.dashboard') }}" class="{{ $__active('student.dashboard') }}"><i class="fas fa-th-large me-1"></i>{{ t('Dashboard', 'Dashboard') }}</a>
            <a href="{{ route('student.certificates') }}" class="{{ $__active('student.certificates') }}"><i class="fas fa-certificate me-1"></i>{{ t('Sijil Saya', 'My Certificates') }}</a>
        @elseif ($__role === 'lecturer')
            <a href="{{ route('lecturer.dashboard') }}" class="{{ $__active('lecturer.dashboard') }}"><i class="fas fa-th-large me-1"></i>{{ t('Dashboard', 'Dashboard') }}</a>
            <a href="{{ route('lecturer.reports') }}" class="{{ $__active('lecturer.reports') }}"><i class="fas fa-chart-bar me-1"></i>{{ t('Laporan', 'Reports') }}</a>
            <a href="{{ route('lecturer.report') }}" class="{{ $__active('lecturer.report') }}"><i class="fas fa-comment-dots me-1"></i>{{ t('Maklum Balas', 'Feedback') }}</a>
            <a href="{{ route('lecturer.profile') }}" class="{{ $__active('lecturer.profile') }}"><i class="fas fa-user me-1"></i>{{ t('Profil', 'Profile') }}</a>
        @elseif ($__role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="{{ $__active('admin.dashboard') }}"><i class="fas fa-th-large me-1"></i>{{ t('Dashboard', 'Dashboard') }}</a>
            <a href="{{ route('admin.languages') }}" class="{{ $__active('admin.languages') }}"><i class="fas fa-book me-1"></i>{{ t('Bahasa & Kandungan', 'Languages & Content') }}</a>
            <a href="{{ route('admin.users') }}" class="{{ $__active('admin.users') }}"><i class="fas fa-users me-1"></i>{{ t('Pengguna', 'Users') }}</a>
            <a href="{{ route('admin.feedback') }}" class="{{ $__active('admin.feedback') }}"><i class="fas fa-comment-dots me-1"></i>{{ t('Maklum Balas', 'Feedback') }}</a>
            <a href="{{ route('admin.analytics') }}" class="{{ $__active('admin.analytics') }}"><i class="fas fa-chart-pie me-1"></i>{{ t('Analitik', 'Analytics') }}</a>
        @endif
    </div>

    <div class="right">
        <button type="button" class="lang-toggle-nav-btn" onclick="toggleIsepLang()" title="{{ t('Tukar bahasa', 'Switch language') }}">{{ current_lang() === 'en' ? 'BM' : 'EN' }}</button>
        <button type="button" class="theme-toggle-nav-btn" onclick="toggleIsepTheme()" title="{{ t('Tukar mod gelap/terang', 'Toggle dark/light mode') }}">
            <i id="themeToggleIcon" class="fas fa-moon"></i>
        </button>
        <span class="role-badge {{ $__role }}">{{ $__role_label }}</span>
        <span class="text-white small d-none d-md-inline">{{ $__user->name ?? '' }}</span>
        <a href="{{ route('logout') }}" data-logout class="logout-btn" title="{{ t('Log Keluar', 'Log Out') }}"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</nav>
<script src="{{ asset('assets/theme-toggle.js') }}?v=3"></script>
