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

    <div class="card-modern p-3">
        @if (count($leaderboard) === 0)
            <div class="isep-empty">
                <div class="isep-empty-icon"><i class="fas fa-trophy"></i></div>
                <div class="isep-empty-title">{{ t('Belum Ada Aktiviti', 'No Activity Yet') }}</div>
                <div class="isep-empty-sub">{{ t('Tiada data untuk tempoh ini. Jadi yang pertama - selesaikan satu aktiviti untuk muncul di sini!', 'No data for this period. Be the first - complete an activity to appear here!') }}</div>
            </div>
        @endif
        @foreach ($leaderboard as $i => $row)
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
