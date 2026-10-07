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
        .level-card { cursor: pointer; transition: transform .2s ease, box-shadow .2s ease; }
        .level-card:hover, .level-card:focus-visible { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(19,49,92,0.15); outline: none; }
        .level-card-cta { display: flex; align-items: center; gap: 10px; padding: 8px 14px; border-radius: 12px; background: rgba(212,175,55,0.12); border: 1px dashed rgba(212,175,55,0.6); }
        .lr-gift-sm { font-size: 1.5rem; animation: lrWiggle 2.6s ease-in-out infinite; display: inline-block; }
        @keyframes lrWiggle { 0%, 80%, 100% { transform: rotate(0); } 85% { transform: rotate(-12deg); } 90% { transform: rotate(10deg); } 95% { transform: rotate(-6deg); } }
        .level-reward-toast { background: linear-gradient(135deg, #D4AF37, #F5D061) !important; color: #0B2545; animation: lrPop .5s cubic-bezier(.2,.9,.3,1.4) both; }
        .level-reward-toast small { color: #0B2545; opacity: .85; }
        .lr-gift { font-size: 2rem; animation: lrWiggle 1.8s ease-in-out infinite; }
        @keyframes lrPop { from { opacity: 0; transform: scale(.92); } to { opacity: 1; transform: none; } }
        .lr-modal { border-radius: 18px; }
        .lr-track { position: relative; display: flex; flex-direction: column; gap: 10px; }
        .lr-item { display: flex; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 14px; border: 1px solid rgba(107,122,143,0.2); background: rgba(107,122,143,0.04); }
        .lr-item.unlocked { border-color: rgba(25,135,84,0.35); background: rgba(25,135,84,0.06); }
        .lr-item.next { border: 2px solid #D4AF37; box-shadow: 0 0 0 4px rgba(212,175,55,0.15); }
        .lr-level { width: 52px; flex-shrink: 0; text-align: center; font-weight: 800; color: var(--isep-primary); background: rgba(19,49,92,0.08); border-radius: 10px; padding: 6px 0; font-size: .85rem; }
        .lr-preview { width: 56px; height: 56px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .lr-ring { width: 50px; height: 50px; border-radius: 50%; padding: 4px; }
        .lr-ring span { width: 100%; height: 100%; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; background: linear-gradient(135deg,var(--isep-primary),var(--isep-secondary)); }
        .lr-name { font-size: 1.6rem; font-weight: 800; }
        .lr-emoji { font-size: 2rem; }
        .lr-swatch { display: flex; border-radius: 10px; overflow: hidden; width: 50px; height: 34px; }
        .lr-swatch span { flex: 1; }
        .lr-confetti { display: flex; flex-wrap: wrap; gap: 4px; width: 46px; justify-content: center; }
        .lr-confetti span { width: 10px; height: 10px; border-radius: 3px; transform: rotate(20deg); }
        .lr-status { text-align: right; flex-shrink: 0; }
        .min-w-0 { min-width: 0; }
        [data-theme="dark"] .lr-level { color: #F5D061; background: rgba(255,255,255,0.06); }
        [data-theme="dark"] .level-card-cta { background: rgba(212,175,55,0.08); }
        @media (prefers-reduced-motion: reduce) { .lr-gift, .lr-gift-sm, .level-reward-toast { animation: none; } }
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
    @if ($new_level_rewards)
    <div class="level-reward-toast card-modern p-3 mb-4 d-flex align-items-center gap-3" role="status">
        <div class="lr-gift">🎁</div>
        <div class="flex-grow-1">
            <div class="fw-bold">{{ t('Ganjaran level baharu!', 'New level reward!') }}</div>
            <small>{{ implode(', ', array_map(fn ($r) => $r['name'] . ' (Level ' . $r['unlock_level'] . ')', $new_level_rewards)) }}</small>
        </div>
        <a href="{{ route('student.shop') }}" class="btn btn-sm btn-light fw-semibold">{{ t('Pakai di Kedai XP', 'Equip in XP Shop') }}</a>
    </div>
    @endif

    <!-- Level Card -->
    <div class="card-modern p-4 mb-4 level-card" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#levelRewardsModal" title="{{ t('Lihat ganjaran level', 'View level rewards') }}">
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
            <div class="level-card-cta">
                <span class="lr-gift-sm">🎁</span>
                <div>
                    <div class="fw-semibold small">{{ t('Ganjaran Level', 'Level Rewards') }}</div>
                    <small class="text-muted">
                        @if ($next_reward)
                            {{ t('Seterusnya', 'Next') }}: {{ $next_reward['name'] }} · Lv {{ (int) $next_reward['unlock_level'] }}
                        @else
                            {{ t('Semua ganjaran diperoleh!', 'All rewards unlocked!') }}
                        @endif
                    </small>
                </div>
                <i class="fas fa-chevron-right text-muted"></i>
            </div>
        </div>
    </div>

    <div class="modal fade" id="levelRewardsModal" tabindex="-1" aria-labelledby="levelRewardsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content lr-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="levelRewardsTitle">🎁 {{ t('Ganjaran Level', 'Level Rewards') }}</h5>
                        <small class="text-muted">{{ t('Naik level untuk membuka ganjaran eksklusif. Ganjaran tidak boleh dibeli di Kedai XP.', 'Level up to unlock exclusive rewards. Rewards cannot be bought in the XP Shop.') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ t('Tutup', 'Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="lr-track">
                        @foreach ($level_rewards as $r)
                        @php
                            $unlocked = $level >= (int) $r['unlock_level'];
                            $owned_r = in_array((int) $r['id'], $owned_item_ids, true);
                            $xp_needed = max(0, ((int) $r['unlock_level'] - 1) * $xp_per_level - $total_xp_earned);
                            $type_label = [
                                'title' => t('Gelaran', 'Title'), 'flame' => t('Api Streak', 'Streak Flame'), 'border' => t('Bingkai Profil', 'Profile Frame'),
                                'name_effect' => t('Kesan Nama', 'Name Effect'), 'theme' => t('Tema', 'Theme'), 'celebration' => t('Sambutan', 'Celebration'),
                            ][$r['item_type']] ?? $r['item_type'];
                        @endphp
                        <div class="lr-item {{ $unlocked ? 'unlocked' : 'locked' }}{{ $next_reward && $next_reward['id'] == $r['id'] ? ' next' : '' }}">
                            <div class="lr-level">Lv {{ (int) $r['unlock_level'] }}</div>
                            <div class="lr-preview">
                                @if ($r['item_type'] === 'border')
                                    <div class="lr-ring" style="background:{{ $r['border_style'] }};"><span>{{ strtoupper(substr($user['name'], 0, 1)) }}</span></div>
                                @elseif ($r['item_type'] === 'name_effect')
                                    <span class="lr-name {{ $r['extra_data'] }}">Aa</span>
                                @elseif ($r['item_type'] === 'theme')
                                    <div class="lr-swatch">@foreach (explode(',', $r['extra_data']) as $c)<span style="background:{{ $c }};"></span>@endforeach</div>
                                @elseif ($r['item_type'] === 'celebration')
                                    <div class="lr-confetti">@foreach (array_slice(explode(',', $r['extra_data']), 0, 5) as $c)<span style="background:{{ $c }};"></span>@endforeach</div>
                                @elseif ($r['item_type'] === 'flame')
                                    <span class="lr-emoji">{{ $r['extra_data'] }}</span>
                                @else
                                    <span class="lr-emoji">{{ $r['icon'] }}</span>
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold">{{ $r['name'] }}</div>
                                <small class="text-muted">{{ $type_label }}{{ $r['item_type'] === 'title' ? ' · "' . $r['extra_data'] . '"' : '' }}</small>
                            </div>
                            <div class="lr-status">
                                @if ($unlocked && $owned_r)
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ t('Diperoleh', 'Unlocked') }}</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-lock me-1"></i>Level {{ (int) $r['unlock_level'] }}</span>
                                    <div class="small text-muted mt-1">{{ $xp_needed }} XP {{ t('lagi', 'to go') }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <a href="{{ route('student.shop') }}" class="btn btn-primary"><i class="fas fa-store me-1"></i>{{ t('Pakai di Kedai XP', 'Equip in XP Shop') }}</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.level-card').forEach(function (card) {
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
        });
    });
    </script>


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
