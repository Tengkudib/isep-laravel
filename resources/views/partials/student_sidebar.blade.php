@php
    $__user = auth()->user();
    $__initial = strtoupper(substr($__user->name ?? 'S', 0, 1));
    $__cos = app(\App\Services\LearningService::class)->getEquippedCosmetics($__user);
    $__border_style = $__cos['border_style'];
    $__flame = $__cos['flame_emoji'] ?: '🔥';

    // Kira warna gradient sidebar terus (inline) - elak sebarang isu cascade CSS variable
    if ($__cos['theme_colors']) {
        [$__grad_a, $__grad_b, $__grad_c] = explode(',', $__cos['theme_colors']);
    } else {
        $__grad_a = '#0B2545'; $__grad_b = '#13315C'; $__grad_c = '#D4AF37';
    }
    $__sidebar_bg = 'linear-gradient(rgba(0,0,0,0.22), rgba(0,0,0,0.22)), linear-gradient(180deg, '
        . e($__grad_a) . ' 0%, ' . e($__grad_b) . ' 55%, ' . e($__grad_c) . ' 100%)';
    $__active = fn (...$names) => request()->routeIs(...$names) ? 'active' : '';
@endphp
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('isep-theme') || 'light');</script>
@if ($__cos['theme_colors'])
@php [$__tp, $__ts, $__ta] = explode(',', $__cos['theme_colors']); @endphp
<style>
    :root, [data-theme="dark"] {
        --isep-primary: {{ $__tp }} !important;
        --isep-primary-dark: {{ $__tp }} !important;
        --isep-secondary: {{ $__ts }} !important;
        --isep-secondary-dark: {{ $__ts }} !important;
        --isep-accent: {{ $__ta }} !important;
        --bs-primary: {{ $__tp }} !important;
    }
</style>
@endif
<style>
    body.has-sidebar { margin-left: 240px; }


    .student-sidebar {
        position: fixed; top: 0; left: 0; height: 100vh; width: 240px;
        display: flex; flex-direction: column; padding: 22px 16px;
        z-index: 200; overflow-y: auto;
    }
    .student-sidebar .brand {
        display: flex; align-items: center; gap: 10px; text-decoration: none;
        color: white; font-weight: 800; font-size: 1.15rem; margin-bottom: 26px; padding: 0 6px;
        text-shadow: 0 1px 3px rgba(0,0,0,0.35);
    }
    .student-sidebar .brand img { height: 30px; }

    .student-sidebar .profile-summary {
        background: rgba(255,255,255,0.14); border-radius: var(--isep-r-xl); padding: 14px;
        display: flex; align-items: center; gap: 10px; margin-bottom: 22px;
        text-decoration: none; transition: background var(--isep-duration) var(--isep-ease);
    }
    .student-sidebar .profile-summary:hover { background: rgba(255,255,255,0.22); }
    .student-sidebar .profile-avatar {
        width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.25);
        display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;
        flex-shrink: 0;
    }
    .student-sidebar .profile-summary .name { color: white; font-weight: 600; font-size: 0.88rem; line-height: 1.2; text-shadow: 0 1px 3px rgba(0,0,0,0.35); }
    .student-sidebar .profile-summary .name .name-fx-gradient,
    .student-sidebar .profile-summary .name .name-fx-shimmer { text-shadow: none; }
    .student-sidebar .profile-summary .role { color: rgba(255,255,255,0.85); font-size: 0.75rem; text-shadow: 0 1px 3px rgba(0,0,0,0.35); }

    .student-sidebar nav { display: flex; flex-direction: column; gap: 4px; flex-grow: 1; }
    .student-sidebar nav a {
        display: flex; align-items: center; gap: 10px; color: #ffffff !important;
        text-decoration: none; padding: 11px 14px; border-radius: var(--isep-r-md); font-size: 0.92rem; font-weight: 600;
        text-shadow: 0 1px 3px rgba(0,0,0,0.35); transition: background var(--isep-duration) var(--isep-ease);
    }
    .student-sidebar nav a i { width: 18px; text-align: center; }
    .student-sidebar nav a:hover { background: rgba(255,255,255,0.15); color: white !important; }
    .student-sidebar nav a.active { background: rgba(255,255,255,0.28); color: white !important; font-weight: 700; }

    .student-sidebar .logout-link {
        display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.9) !important;
        text-decoration: none; padding: 11px 14px; border-radius: var(--isep-r-md); font-size: 0.9rem;
        border-top: 1px solid rgba(255,255,255,0.2); margin-top: 12px; padding-top: 16px;
        text-shadow: 0 1px 3px rgba(0,0,0,0.35); transition: color var(--isep-duration) var(--isep-ease);
    }
    .student-sidebar .logout-link:hover { color: white; }
    .lang-toggle-btn {
        display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%;
        background: rgba(255,255,255,0.14); color: white !important; border: none; border-radius: var(--isep-r-md);
        padding: 9px 14px; font-size: 0.82rem; font-weight: 700; cursor: pointer; margin-top: 8px;
        transition: background var(--isep-duration) var(--isep-ease);
    }
    .lang-toggle-btn:hover { background: rgba(255,255,255,0.24); }

    @media (max-width: 900px) {
        body.has-sidebar { margin-left: 0; padding-top: 64px; }
        .student-sidebar {
            width: 100%; height: 64px; flex-direction: row; align-items: center;
            padding: 10px 16px; overflow-x: auto; overflow-y: hidden;
        }
        .student-sidebar .brand { margin-bottom: 0; margin-right: 12px; white-space: nowrap; }
        .student-sidebar .profile-summary { display: none; }
        .student-sidebar nav { flex-direction: row; flex-grow: 0; }
        .student-sidebar nav a { white-space: nowrap; padding: 8px 12px; }
        .student-sidebar .logout-link { border-top: none; margin-top: 0; padding-top: 8px; }
    }
</style>

<aside class="student-sidebar" style="background: {!! $__sidebar_bg !!};">
    <a href="{{ route('home') }}" class="brand"><img src="{{ asset('assets/img/iSEP-256.png') }}" alt="iSEP"> iSEP</a>

    <a href="{{ route('student.profile') }}" class="profile-summary">
        <div class="profile-avatar" style="{{ $__border_style ? 'background:' . $__border_style . '; padding:3px;' : '' }}">
            @if ($__border_style)
            <div style="width:100%;height:100%;border-radius:50%;background:rgba(255,255,255,0.25);display:flex;align-items:center;justify-content:center;">{{ $__initial }}</div>
            @else
                {{ $__initial }}
            @endif
        </div>
        <div>
            <div class="name"><span class="{{ $__cos['name_effect_class'] ?? '' }}">{{ $__user->name }}</span></div>
            @if ($__cos['title_text'])
            <div style="font-size:0.68rem; color:#D4AF37; font-weight:600;">{{ $__cos['title_text'] }}</div>
            @endif
            <div class="role">💎 {{ (int) $__user->xp_points }} XP</div>
        </div>
    </a>

    <nav>
        <a href="{{ route('student.dashboard') }}" class="{{ $__active('student.dashboard') }}"><i class="fas fa-th-large"></i>{{ t('Dashboard', 'Dashboard') }}</a>
        <a href="{{ route('student.certificates') }}" class="{{ $__active('student.certificates') }}"><i class="fas fa-certificate"></i>{{ t('Sijil Saya', 'My Certificates') }}</a>
        <a href="{{ route('student.leaderboard') }}" class="{{ $__active('student.leaderboard') }}"><i class="fas fa-trophy"></i>{{ t('Leaderboard', 'Leaderboard') }}</a>
        <a href="{{ route('student.games') }}" class="{{ $__active('student.games', 'student.chess', 'student.dam') }}"><i class="fas fa-gamepad"></i>{{ t('Permainan', 'Games') }}</a>
        <a href="{{ route('student.shop') }}" class="{{ $__active('student.shop') }}"><i class="fas fa-store"></i>{{ t('Kedai XP', 'XP Shop') }}</a>
        <a href="{{ route('student.profile') }}" class="{{ $__active('student.profile') }}"><i class="fas fa-user"></i>{{ t('Profil', 'Profile') }}</a>
        <a href="{{ route('student.report') }}" class="{{ $__active('student.report') }}"><i class="fas fa-comment-dots"></i>{{ t('Laporan', 'Report') }}</a>
    </nav>

    <button type="button" class="theme-toggle-btn" onclick="toggleIsepTheme()">
        <i id="themeToggleIcon" class="fas fa-moon"></i>
        <span id="themeToggleLabel">{{ t('Mod Gelap', 'Dark Mode') }}</span>
    </button>
    <button type="button" class="lang-toggle-btn" onclick="toggleIsepLang()"><i class="fas fa-globe"></i> {{ current_lang() === 'en' ? 'Bahasa Melayu' : 'English' }}</button>

    <a href="{{ route('logout') }}" data-logout class="logout-link"><i class="fas fa-sign-out-alt"></i>{{ t('Log Keluar', 'Log Out') }}</a>
</aside>
<script src="{{ asset('assets/theme-toggle.js') }}?v=3"></script>
@include('partials.chatbot')
