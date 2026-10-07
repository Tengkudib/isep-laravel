@extends('layouts.student')

@section('title'){{ t('Papan Kedudukan', 'Leaderboard') }} - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { background: #f4f6fb; font-family: 'Segoe UI', sans-serif; }
        .topbar { background: linear-gradient(135deg, #0a1128 0%, #1e3a8a 45%, #6d28d9 80%, #06b6d4 100%); color: white; padding: 28px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-sm); }
        .period-tabs a { padding: 8px 20px; border-radius: var(--isep-r-lg); text-decoration: none; color: #6c757d; font-weight: 600; font-size: 0.9rem; }
        .period-tabs a.active { background: var(--isep-primary); color: white; }
        [data-theme="dark"] .period-tabs a { color: rgba(255,255,255,0.65) !important; }
        [data-theme="dark"] .period-tabs a.active { color: #ffffff !important; }
        .lb-row { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-radius: var(--isep-r-lg); margin-bottom: 8px; }
        .lb-row.top1 { background: linear-gradient(135deg,#D4AF3733,#D4AF3711); }
        .lb-row.top2 { background: linear-gradient(135deg,#C0C0C033,#C0C0C011); }
        .lb-row.top3 { background: linear-gradient(135deg,#CD7F3233,#CD7F3211); }
        .lb-row.me { border: 2px solid var(--isep-primary); }
        .rank-num { width: 32px; text-align: center; font-weight: 800; color: #6c757d; }
        .avatar-ring { width: 42px; height: 42px; border-radius: 50%; padding: 3px; flex-shrink: 0; }
        .avatar-ring .inner { width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg,var(--isep-primary),var(--isep-secondary)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem; }

        .podium { display: grid; grid-template-columns: 1fr 1.15fr 1fr; align-items: end; gap: 12px; margin-bottom: 28px; padding: 8px 4px 0; }
        .podium-spot { display: flex; flex-direction: column; align-items: center; text-align: center; min-width: 0; animation: podiumRise 0.6s cubic-bezier(.2,.8,.2,1) both; }
        .podium-spot.p1 { animation-delay: 0.25s; }
        .podium-spot.p2 { animation-delay: 0.1s; }
        .podium-spot.p3 { animation-delay: 0.4s; }
        @keyframes podiumRise { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: none; } }
        .podium-crown { font-size: 1.9rem; line-height: 1; margin-bottom: 4px; animation: crownFloat 2.4s ease-in-out infinite; filter: drop-shadow(0 3px 4px rgba(212,175,55,0.5)); }
        @keyframes crownFloat { 50% { transform: translateY(-5px) rotate(-4deg); } }
        .podium-avatar { position: relative; border-radius: 50%; padding: 4px; margin-bottom: 10px; }
        .podium-avatar .inner { width: 100%; height: 100%; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; background: linear-gradient(135deg,var(--isep-primary),var(--isep-secondary)); border: 3px solid var(--isep-card-bg, #fff); }
        .p1 .podium-avatar { width: 92px; height: 92px; font-size: 1.6rem; box-shadow: 0 0 0 4px #F5D061, 0 10px 28px rgba(212,175,55,0.55); }
        .p2 .podium-avatar { width: 74px; height: 74px; font-size: 1.25rem; box-shadow: 0 0 0 4px #CFD6E0, 0 8px 20px rgba(140,150,165,0.45); }
        .p3 .podium-avatar { width: 70px; height: 70px; font-size: 1.15rem; box-shadow: 0 0 0 4px #E3A36B, 0 8px 20px rgba(205,127,50,0.45); }
        .podium-medal { position: absolute; bottom: -8px; right: -6px; font-size: 1.5rem; line-height: 1; }
        .podium-name { font-weight: 700; font-size: 0.95rem; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding: 0 4px; }
        .podium-title { font-size: 0.72rem; color: #D4AF37; font-weight: 600; min-height: 1.1em; }
        .podium-xp { display: inline-block; margin: 6px 0 10px; padding: 3px 12px; border-radius: 999px; font-weight: 800; font-size: 0.85rem; background: rgba(19,49,92,0.08); color: var(--isep-primary); }
        .podium-block { width: 100%; border-radius: 14px 14px 6px 6px; display: flex; align-items: flex-start; justify-content: center; padding-top: 10px; font-size: 2rem; font-weight: 900; color: rgba(255,255,255,0.92); text-shadow: 0 2px 6px rgba(0,0,0,0.25); position: relative; overflow: hidden; }
        .podium-block::after { content: ''; position: absolute; inset: 0; background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,0.35) 45%, transparent 60%); transform: translateX(-100%); animation: podiumShine 3.5s ease-in-out infinite; }
        @keyframes podiumShine { 60%, 100% { transform: translateX(100%); } }
        .p1 .podium-block { height: 150px; background: linear-gradient(180deg, #F5D061 0%, #D4AF37 55%, #B8941F 100%); box-shadow: 0 12px 30px rgba(212,175,55,0.4); }
        .p2 .podium-block { height: 110px; background: linear-gradient(180deg, #E4E9F0 0%, #B8C2CF 60%, #98A3B3 100%); box-shadow: 0 10px 24px rgba(140,150,165,0.35); }
        .p3 .podium-block { height: 85px; background: linear-gradient(180deg, #F0B27A 0%, #CD7F32 60%, #A8652A 100%); box-shadow: 0 10px 24px rgba(205,127,50,0.35); }
        .podium-spot.me .podium-name { color: var(--isep-primary); }
        .podium-you { display: inline-block; font-size: 0.65rem; font-weight: 700; color: white; background: var(--isep-primary); border-radius: 999px; padding: 1px 8px; margin-bottom: 2px; }
        .podium-empty .podium-avatar .inner { background: rgba(107,122,143,0.25); }
        .podium-empty .podium-block { filter: grayscale(0.6); opacity: 0.55; }
        [data-theme="dark"] .podium-xp { background: rgba(255,255,255,0.08); color: #F5D061; }
        [data-theme="dark"] .lb-row .text-primary { color: #F5D061 !important; }
        [data-theme="dark"] .podium-spot.me .podium-name { color: #F5D061; }
        @media (max-width: 575.98px) {
            .podium { gap: 6px; }
            .p1 .podium-avatar { width: 70px; height: 70px; font-size: 1.25rem; }
            .p2 .podium-avatar, .p3 .podium-avatar { width: 56px; height: 56px; font-size: 1rem; }
            .p1 .podium-block { height: 120px; } .p2 .podium-block { height: 88px; } .p3 .podium-block { height: 68px; }
            .podium-name { font-size: 0.8rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .podium-spot, .podium-crown, .podium-block::after { animation: none; }
        }
    </style>
@endpush

@section('content')
<div class="topbar">
    <h4 class="fw-bold mb-1"><i class="fas fa-trophy me-2"></i>{{ t('Papan Kedudukan', 'Leaderboard') }}</h4>
    <p class="mb-0 opacity-75">{{ t('Lihat kedudukan anda berbanding pelajar lain', 'See how you rank against other students') }}</p>
</div>

<div class="container-fluid p-4" style="max-width: 720px;">
    <div class="d-flex gap-2 period-tabs mb-4">
        <a href="{{ route('student.leaderboard', ['period' => 'weekly']) }}" class="{!! $period === 'weekly' ? 'active' : '' !!}">{{ t('Mingguan', 'Weekly') }}</a>
        <a href="{{ route('student.leaderboard', ['period' => 'monthly']) }}" class="{!! $period === 'monthly' ? 'active' : '' !!}">{{ t('Bulanan', 'Monthly') }}</a>
        <a href="{{ route('student.leaderboard', ['period' => 'overall']) }}" class="{!! $period === 'overall' ? 'active' : '' !!}">{{ t('Keseluruhan', 'Overall') }}</a>
    </div>

    @if ($my_rank)
    <div class="alert alert-primary border-0 shadow-sm mb-4">
        {{ t('Kedudukan anda', 'Your rank') }} ({!! $period_labels[$period] !!}): <strong>#{!! $my_rank !!}</strong> {{ t('daripada', 'out of') }} {!! count($leaderboard) !!} {{ t('pelajar', 'students') }}
    </div>
    @endif

    @if (count($leaderboard) > 0)
    <div class="podium">
        @foreach ([2, 1, 3] as $place)
        @php
            $prow = $leaderboard[$place - 1] ?? null;
            $is_me = $prow && $prow['id'] == $student_id;
            $pmedal = ['', '🥇', '🥈', '🥉'][$place];
        @endphp
        <div class="podium-spot p{{ $place }}{{ $is_me ? ' me' : '' }}{{ $prow ? '' : ' podium-empty' }}">
            @if ($place === 1)<div class="podium-crown">👑</div>@endif
            <div class="podium-avatar" style="background:{{ $prow && $prow['border_style'] ? $prow['border_style'] : 'transparent' }};">
                <div class="inner">{{ $prow ? strtoupper(substr($prow['name'], 0, 1)) : '?' }}</div>
                <span class="podium-medal">{{ $pmedal }}</span>
            </div>
            @if ($prow)
                @if ($is_me)<span class="podium-you">{{ t('Anda', 'You') }}</span>@endif
                <div class="podium-name" title="{{ $prow['name'] }}"><span class="{{ $prow['name_effect_class'] ?? '' }}">{{ $prow['name'] }}</span></div>
                <div class="podium-title">{{ $prow['title_text'] ?? '' }}</div>
                <div class="podium-xp">⚡ {{ (int) $prow['period_xp'] }} XP</div>
            @else
                <div class="podium-name text-muted">—</div>
                <div class="podium-title"></div>
                <div class="podium-xp">⚡ 0 XP</div>
            @endif
            <div class="podium-block">{{ $place }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="card-modern p-3">
        @if (count($leaderboard) > 0 && count($leaderboard) <= 3)
            <p class="text-muted text-center small mb-0">{{ t('Hanya tiga pelajar teratas setakat ini.', 'Only the top three students so far.') }}</p>
        @endif
        @if (count($leaderboard) === 0)
            <div class="isep-empty">
                <div class="isep-empty-icon"><i class="fas fa-trophy"></i></div>
                <div class="isep-empty-title">{{ t('Belum Ada Aktiviti', 'No Activity Yet') }}</div>
                <div class="isep-empty-sub">{{ t('Tiada data untuk tempoh ini. Jadi yang pertama - selesaikan satu aktiviti untuk muncul di sini!', 'No data for this period. Be the first - complete an activity to appear here!') }}</div>
            </div>
        @endif
        @foreach (array_slice($leaderboard, 3, null, true) as $i => $row)
@php
            $rank = $i + 1;
            $row_class = $rank === 1 ? 'top1' : ($rank === 2 ? 'top2' : ($rank === 3 ? 'top3' : ''));
            if ($row['id'] == $student_id) $row_class .= ' me';
            $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : null));
@endphp
        <div class="lb-row {!! $row_class !!}">
            <div class="rank-num">{!! $medal ?? '#' . $rank !!}</div>
            <div class="avatar-ring" style="background:{!! $row['border_style'] ? htmlspecialchars($row['border_style']) : 'transparent' !!};">
                <div class="inner">{!! strtoupper(substr($row['name'], 0, 1)) !!}</div>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold {!! $row['name_effect_class'] ?? '' !!}">
                    {{ $row['name'] }}{!! $row['id'] == $student_id ? ' <span class="text-primary small">(' . t('Anda', 'You') . ')</span>' : '' !!}
                </div>
                @if (!empty($row['title_text']))
                <small style="color:#D4AF37; font-weight:600;">{{ $row['title_text'] }}</small>
                @endif
            </div>
            <div class="fw-bold text-primary">⚡ {!! (int)$row['period_xp'] !!}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
