@extends('layouts.student')

@section('title')Dashboard - iSEP
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; }
        .topbar {
            background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%);
            color: white;
            padding: var(--isep-sp-6) var(--isep-sp-6);
            border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
        }
        .topbar h4 { font-size: var(--isep-fs-xl); }
        .stat-pill {
            background: rgba(255,255,255,0.15);
            border-radius: var(--isep-r-lg);
            padding: var(--isep-sp-3) var(--isep-sp-4);
            backdrop-filter: blur(6px);
        }
        .card-modern {
            border: none;
            border-radius: var(--isep-r-xl);
            box-shadow: var(--isep-shadow-sm);
        }
        .continue-card {
            background: linear-gradient(135deg, #D4AF37, #B8941F);
            color: #0B2545;
            border-radius: var(--isep-r-xl);
        }
        .lang-card {
            border-radius: var(--isep-r-xl);
        }
        .lang-card:hover { transform: translateY(-4px); }
        .progress { height: 8px; border-radius: var(--isep-r-sm); }
        .badge-pill {
            background: #fff3cd;
            border-radius: var(--isep-r-lg);
            padding: var(--isep-sp-3);
            text-align: center;
            font-size: 1.6rem;
        }

        /* ---------- XP earned toast (shown after winning a game vs computer) ---------- */
        .xp-toast {
            position: fixed; top: 20px; right: 20px; z-index: 1300; max-width: 340px;
            display: flex; align-items: center; gap: 14px; padding: 16px 20px; border-radius: 16px;
            background: linear-gradient(135deg, #D4AF37, #B8941F); color: #0B2545;
            box-shadow: 0 14px 34px rgba(0,0,0,0.25);
            transform: translateX(120%); opacity: 0; transition: transform 0.45s cubic-bezier(.34,1.56,.64,1), opacity 0.35s ease;
        }
        .xp-toast.show { transform: translateX(0); opacity: 1; }
        .xp-toast .xp-toast-icon {
            width: 46px; height: 46px; border-radius: 50%; background: rgba(255,255,255,0.4);
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;
        }
        .xp-toast .xp-toast-title { font-weight: 800; font-size: 1.05rem; margin-bottom: 2px; }
        .xp-toast .xp-toast-sub { font-size: 0.82rem; opacity: 0.85; }
        .xp-toast .xp-toast-close {
            background: none; border: none; color: #0B2545; opacity: 0.6; font-size: 1rem; cursor: pointer; align-self: flex-start;
        }
        .xp-toast .xp-toast-close:hover { opacity: 1; }
        @media (max-width: 480px) {
            .xp-toast { left: 16px; right: 16px; max-width: none; }
        }
    </style>
@endpush

@section('content')
@if ($xp_gained > 0)
<div class="xp-toast" id="xpToast">
    <div class="xp-toast-icon"><i class="fas fa-bolt"></i></div>
    <div class="flex-grow-1">
        <div class="xp-toast-title">+{!! $xp_gained !!} {{ t('XP Diperoleh!', 'XP Earned!') }}</div>
        <div class="xp-toast-sub">{!! $xp_game ? t('Daripada ', 'From ') . htmlspecialchars($xp_game) . t(' lawan komputer', ' vs computer') : t('Aktiviti baharu selesai', 'New activity completed') !!}</div>
    </div>
    <button type="button" class="xp-toast-close" onclick="dismissXpToast()"><i class="fas fa-times"></i></button>
</div>
<script>
    function dismissXpToast() {
        const t = document.getElementById('xpToast');
        if (!t) return;
        t.classList.remove('show');
        setTimeout(() => t.remove(), 400);
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('xp_gained');
            url.searchParams.delete('xp_game');
            window.history.replaceState({}, '', url);
        }
    }
    setTimeout(() => document.getElementById('xpToast').classList.add('show'), 100);
    setTimeout(dismissXpToast, 6000);
</script>
@endif

<div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">{{ t('Selamat kembali, ', 'Welcome back, ') }}<span class="{!! $cosmetics['name_effect_class'] ?? '' !!}">{{ $user['name'] }}</span>! 👋</h4>
        @if ($cosmetics['title_text'])
        <span class="badge" style="background:rgba(212,175,55,0.25); color:#D4AF37; font-size:0.7rem;">{{ $cosmetics['title_text'] }}</span>
        @endif
        <p class="mb-0 opacity-75">{{ t('Teruskan perjalanan pembelajaran anda hari ini.', 'Continue your learning journey today.') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <div class="stat-pill text-center"><div class="fw-bold">{!! $flame_emoji !!} {!! (int)$user['learning_streak'] !!}</div><small>{{ t('Streak', 'Streak') }}</small></div>
        <div class="stat-pill text-center"><div class="fw-bold">💎 {!! (int)$user['xp_points'] !!}</div><small>{{ t('XP', 'XP') }}</small></div>
        <div class="stat-pill text-center"><div class="fw-bold">🧊 {!! (int)$user['streak_freezes'] !!}</div><small>{{ t('Freeze', 'Freeze') }}</small></div>
    </div>
</div>

<div class="container-fluid p-4">
    <!-- Level Card -->
    <div class="card-modern p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--isep-primary),var(--isep-secondary));display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1.3rem;">
                    {!! $level !!}
                </div>
                <div>
                    <div class="fw-bold">{{ t('Level', 'Level') }} {!! $level !!}</div>
                    <small class="text-muted">{!! $xp_to_next !!} {{ t('XP lagi ke Level', 'XP more to Level') }} {!! $level + 1 !!}</small>
                </div>
            </div>
            <div style="flex:1; min-width:200px; max-width:340px;">
                <div class="progress" style="height:10px;">
                    <div class="progress-bar" style="width:{!! $level_progress_pct !!}%; background:linear-gradient(90deg,var(--isep-primary),var(--isep-secondary));"></div>
                </div>
                <small class="text-muted">{!! $xp_into_level !!} / {!! $xp_per_level !!} XP</small>
            </div>
        </div>
    </div>


    <!-- Continue Learning -->
    @if ($continue_learning)
    <div class="continue-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <small class="text-white-50 text-uppercase fw-semibold">{{ t('Teruskan Belajar', 'Continue Learning') }}</small>
                <h4 class="fw-bold mb-1">{{ $continue_learning['name'] }}</h4>
                <p class="mb-2">{{ t('Bab', 'Chapter') }} {!! $continue_learning['chapter_number'] !!} — {{ $continue_learning['chapter_title'] }}</p>
                <div class="progress bg-white bg-opacity-25" style="width:260px;">
                    <div class="progress-bar bg-white" style="width:{!! $continue_learning['completion_percentage'] !!}%"></div>
                </div>
                <small>{!! $continue_learning['completion_percentage'] !!}% {{ t('selesai', 'complete') }}</small>
            </div>
            <a href="{{ route('student.chapter', $continue_learning['chapter_id']) }}" class="btn btn-light fw-semibold text-primary px-4">
                {{ t('Sambung', 'Continue') }} <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    @else
    <div class="card-modern mb-4">
        <div class="isep-empty">
            <div class="isep-empty-icon"><i class="fas fa-rocket"></i></div>
            <div class="isep-empty-title">{{ t('Mulakan Perjalanan Pembelajaran Anda!', 'Start Your Learning Journey!') }}</div>
            <div class="isep-empty-sub">{{ t('Pilih satu bahasa pengaturcaraan di bawah untuk mula belajar dan peroleh XP pertama anda.', 'Choose a programming language below to start learning and earn your first XP.') }}</div>
        </div>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-modern p-3 text-center">
                <h3 class="fw-bold text-primary mb-0">{!! $stats['chapters_completed'] !!}</h3>
                <small class="text-muted">{{ t('Bab Selesai', 'Chapters Completed') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern p-3 text-center">
                <h3 class="fw-bold text-success mb-0">{!! $stats['exercises_completed'] !!}</h3>
                <small class="text-muted">{{ t('Latihan Selesai', 'Exercises Completed') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern p-3 text-center">
                <h3 class="fw-bold text-warning mb-0">{!! $stats['quiz_average'] !!}%</h3>
                <small class="text-muted">{{ t('Purata Kuiz', 'Quiz Average') }}</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Languages -->
        <div class="col-lg-8">
            <h5 class="fw-bold mb-3">{{ t('Bahasa Pengaturcaraan', 'Programming Languages') }}</h5>
            <div class="row g-3">
                @foreach ($languages as $lang)
                <div class="col-md-6">
                    <div class="card-modern lang-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div style="width:44px;height:44px;border-radius:12px;background:{{ $lang['color_theme'] }}22;color:{{ $lang['color_theme'] }};display:flex;align-items:center;justify-content:center;font-size:1.2rem;" class="me-2">
                                <i class="{{ $lang['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $lang['name'] }}</div>
                                <small class="text-muted">{{ $lang['difficulty'] }} · {!! $lang['total_chapters'] !!} {{ t('bab', 'chapters') }}</small>
                            </div>
                        </div>
                        <div class="progress mb-1">
                            <div class="progress-bar" style="width:{!! $lang['progress'] !!}%; background:{{ $lang['color_theme'] }};"></div>
                        </div>
                        <small class="text-muted">{!! $lang['progress'] !!}% {{ t('selesai', 'complete') }}</small>
                        <a href="{{ route('student.language', $lang['slug']) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">{{ t('Mula Belajar', 'Start Learning') }}</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Badges -->
        <div class="col-lg-4">
            <h5 class="fw-bold mb-3">{{ t('Lencana Terkini', 'Recent Badges') }}</h5>
            <div class="card-modern p-3 mb-4">
                @if (count($earned_badges) === 0)
                    <div class="isep-empty py-3">
                        <div class="isep-empty-icon"><i class="fas fa-medal"></i></div>
                        <div class="isep-empty-title">{{ t('Belum ada lencana', 'No badges yet') }}</div>
                        <div class="isep-empty-sub">{{ t('Selesaikan nota, kuiz atau latihan pertama anda untuk buka lencana pertama!', 'Complete your first notes, quiz or exercise to unlock your first badge!') }}</div>
                    </div>
                @else
                    <div class="row g-2">
                        @foreach ($earned_badges as $b)
                        <div class="col-4">
                            <div class="badge-pill">{!! $b['icon'] !!}</div>
                            <small class="d-block text-center mt-1">{{ $b['name'] }}</small>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Leaderboard Mini -->
            <div class="card-modern p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0"><i class="fas fa-trophy text-warning me-1"></i>{{ t('Leaderboard Minggu Ini', 'This Week\'s Leaderboard') }}</h6>
                    <a href="{{ route('student.leaderboard') }}" class="small">{{ t('Lihat semua', 'View all') }}</a>
                </div>
                @if (count($leaderboard_preview) === 0)
                    <div class="isep-empty py-3">
                        <div class="isep-empty-icon"><i class="fas fa-trophy"></i></div>
                        <div class="isep-empty-title">{{ t('Belum ada aktiviti minggu ini', 'No activity this week yet') }}</div>
                        <div class="isep-empty-sub">{{ t('Jadi yang pertama - selesaikan satu aktiviti untuk muncul di sini!', 'Be the first - complete an activity to appear here!') }}</div>
                    </div>
                @else
                    @foreach ($leaderboard_preview as $i => $row)
@php
                        $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');
@endphp
                    <div class="d-flex align-items-center gap-2 py-1">
                        <span>{!! $medal !!}</span>
                        <span class="flex-grow-1 small {!! $row['id'] == $student_id ? 'fw-bold text-primary' : '' !!}">{{ $row['name'] }}</span>
                        <span class="small fw-semibold">💎{!! (int)$row['period_xp'] !!}</span>
                    </div>
                    @endforeach
                @endif
            </div>

            <!-- Misi Harian -->
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-flag text-danger me-1"></i>{{ t('Misi Harian', 'Daily Mission') }}</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ t('Selesaikan 1 aktiviti', 'Complete 1 activity') }}</span>
                        <span>{!! min($today_activity_count, 1) !!}/1</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-success" style="width:{!! min(100, $today_activity_count * 100) !!}%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ t('Perolehi 50 XP hari ini', 'Earn 50 XP today') }}</span>
                        <span>{!! min($today_xp, 50) !!}/50</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-success" style="width:{!! min(100, round(($today_xp / 50) * 100)) !!}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
