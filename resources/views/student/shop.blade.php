@extends('layouts.student')

@section('title'){{ t('Kedai XP', 'XP Shop') }} - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { background: var(--isep-bg); font-family: 'Segoe UI', sans-serif; }
        .topbar { background: linear-gradient(135deg, #0a1128 0%, #1e3a8a 45%, #6d28d9 80%, #06b6d4 100%); color: white; padding: 28px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-sm); }
        .cat-tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 24px; }
        .cat-tab {
            display: flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: var(--isep-r-lg);
            background: var(--isep-card-bg); color: var(--isep-text-muted, #6c757d); text-decoration: none;
            font-weight: 600; font-size: 0.88rem; white-space: nowrap; border: 2px solid transparent;
            box-shadow: var(--isep-shadow-sm);
        }
        .cat-tab.active { background: linear-gradient(135deg,var(--isep-primary),var(--isep-secondary)); color: white; }
        .cat-tab:hover:not(.active) { border-color: var(--isep-primary); color: var(--isep-primary); }
        [data-theme="dark"] .cat-tab { color: rgba(255,255,255,0.65) !important; }
        [data-theme="dark"] .cat-tab.active { color: #ffffff !important; }
        .shop-item { padding: 20px; text-align: center; height: 100%; position: relative; }
        .item-icon { font-size: 2.2rem; margin-bottom: 8px; }
        .border-preview { width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; padding: 4px; }
        .border-preview .inner { width: 100%; height: 100%; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: #13315C; font-weight: 700; }
        .owned-badge { position: absolute; top: 10px; right: 10px; }
        .reward-badge { position: absolute; top: 10px; left: 10px; background: linear-gradient(135deg,#D4AF37,#F5D061); color: #0B2545; font-weight: 700; }
        .shop-item.reward-item { border: 1px solid rgba(212,175,55,0.55); box-shadow: 0 0 0 3px rgba(212,175,55,0.12); padding-top: 42px !important; }
        .theme-swatch { display: flex; height: 40px; border-radius: var(--isep-r-lg); overflow: hidden; margin-bottom: 10px; }
        .theme-swatch span { flex: 1; }
        .confetti-preview { display: flex; gap: 4px; justify-content: center; margin-bottom: 10px; }
        .confetti-preview span { width: 14px; height: 14px; border-radius: var(--isep-r-sm); display: inline-block; }
        .name-fx-gradient { background: linear-gradient(90deg,#7B68EE,#20B2AA,#FF6B6B); -webkit-background-clip: text; background-clip: text; color: transparent; font-weight: 800; }
        .name-fx-glow { color: #7B68EE; text-shadow: 0 0 10px #7B68EE, 0 0 20px #20B2AA; font-weight: 800; }
        .name-fx-shimmer { background: linear-gradient(90deg,#7B68EE,#FFD93D,#20B2AA,#7B68EE); background-size: 200% auto; -webkit-background-clip: text; background-clip: text; color: transparent; font-weight: 800; animation: shimmerMove 2.5s linear infinite; }
        @keyframes shimmerMove { to { background-position: 200% center; } }
        .booster-active-banner { background: linear-gradient(135deg,#D4AF37,#B8941F); color: #0B2545; border-radius: var(--isep-r-xl); padding: 14px 20px; margin-bottom: 20px; font-weight: 600; }
    </style>
@endpush

@section('content')
<div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-store me-2"></i>{{ t('Kedai XP', 'XP Shop') }}</h4>
        <p class="mb-0 opacity-75">{{ t('Tukar XP anda dengan ganjaran eksklusif', 'Exchange your XP for exclusive rewards') }}</p>
    </div>
    <div class="stat-pill text-center px-3 py-2" style="background:rgba(255,255,255,0.15); border-radius:12px;">
        <div class="fw-bold">💎 {!! (int)$user['xp_points'] !!}</div>
        <small>{{ t('XP Anda', 'Your XP') }}</small>
    </div>
</div>

<div class="container-fluid p-4">
    @if ($message)
    <div class="alert alert-{!! $message_type !!} border-0 shadow-sm">{{ $message }}</div>
    @endif

    @if ($booster_active)
    <div class="booster-active-banner">
        ⚡ {{ t('Booster', 'Booster') }} {!! $user['xp_booster_multiplier'] !!}x XP {{ t('AKTIF sehingga', 'ACTIVE until') }} {!! date('d M, h:i A', strtotime($user['xp_booster_until'])) !!}!
    </div>
    @endif

    <!-- Tab Kategori -->
    <div class="cat-tabs">
        @foreach ($categories as $key => $cat)
        <a href="{{ route('student.shop', ['tab' => $key]) }}" class="cat-tab {!! $active_tab === $key ? 'active' : '' !!}">
            <i class="fas {!! $cat['icon'] !!}"></i> {!! $cat['label'] !!}
        </a>
        @endforeach
    </div>

    <h5 class="fw-bold mb-3">{!! $categories[$active_tab]['label'] !!}</h5>

    <div class="row g-3">
        @php
 $current_items = $by_type[$active_tab] ?? []; 
@endphp
        @if (count($current_items) === 0)
        <div class="col-12">
            <div class="isep-empty">
                <div class="isep-empty-icon"><i class="fas fa-box-open"></i></div>
                <div class="isep-empty-title">{{ t('Tiada Item Dalam Kategori Ini', 'No Items In This Category') }}</div>
                <div class="isep-empty-sub">{{ t('Cuba lihat kategori lain di atas atau kembali sebentar lagi untuk item baharu.', 'Try another category above or check back later for new items.') }}</div>
            </div>
        </div>
        @endif

        @foreach ($current_items as $item)
@php
            $owned = in_array($item['id'], $owned_ids);
            $is_equipable = isset($equipped_column_map[$item['item_type']]);
            $equipped = $is_equipable && $user[$equipped_column_map[$item['item_type']]] == $item['id'];
@endphp
        <div class="col-md-3 col-6">
            <div class="card-modern shop-item{{ $item['unlock_level'] !== null ? ' reward-item' : '' }}">
                @if ($item['unlock_level'] !== null)<span class="badge reward-badge"><i class="fas fa-gift me-1"></i>{{ t('Ganjaran Level', 'Level Reward') }} {{ (int) $item['unlock_level'] }}</span>@endif
                @if ($owned && $is_equipable)<span class="badge bg-success owned-badge">{{ t('Dimiliki', 'Owned') }}</span>@endif

                @if ($item['item_type'] === 'border')
                    <div class="border-preview" style="background:{{ $item['extra_data'] ?: $item['border_style'] }};">
                        <div class="inner">{!! strtoupper(substr(auth()->user()->name, 0, 1)) !!}</div>
                    </div>

                @elseif ($item['item_type'] === 'theme')
@php
                    $colors = explode(',', $item['extra_data']);
@endphp
                    <div class="theme-swatch">
                        @foreach ($colors as $c)<span style="background:{{ $c }};"></span>@endforeach
                    </div>

                @elseif ($item['item_type'] === 'celebration')
@php
                    $colors = explode(',', $item['extra_data']);
@endphp
                    <div class="confetti-preview">
                        @foreach (array_slice($colors, 0, 6) as $c)<span style="background:{{ $c }};"></span>@endforeach
                    </div>

                @elseif ($item['item_type'] === 'name_effect')
                    <div class="mb-2 {{ $item['extra_data'] }}" style="font-size:1.1rem;">{{ auth()->user()->name }}</div>

                @elseif ($item['item_type'] === 'title')
                    <div class="item-icon">{!! $item['icon'] !!}</div>
                    <span class="badge bg-primary mb-2">{{ $item['extra_data'] }}</span>

                @elseif ($item['item_type'] === 'flame')
                    <div class="item-icon">{{ $item['extra_data'] }}</div>

                @else
                    <div class="item-icon">{!! $item['icon'] !!}</div>
                @endif

                <h6 class="fw-bold small">{{ $item['name'] }}</h6>
                @if ($item['description'])<p class="text-muted small mb-2" style="font-size:0.75rem;">{{ $item['description'] }}</p>@endif

                @if ($item['item_type'] === 'freeze')
                    <p class="small mb-2">{{ t('Anda ada', 'You have') }}: <strong>{!! (int)$user['streak_freezes'] !!}</strong></p>
                    <form method="POST" action="{{ route('student.shop', ['tab' => $active_tab]) }}">
                        @csrf
                        <input type="hidden" name="item_id" value="{!! $item['id'] !!}">
                        <button type="submit" name="buy_item" class="btn btn-primary btn-sm w-100" {!! $user['xp_points'] < $item['cost_xp'] ? 'disabled' : '' !!}>💎{!! $item['cost_xp'] !!} XP</button>
                    </form>

                @elseif ($item['item_type'] === 'booster' && $booster_active)
                    <button class="btn btn-secondary btn-sm w-100" disabled title="{{ t('Booster lain masih aktif', 'Another booster is still active') }}"><i class="fas fa-hourglass-half me-1"></i>{{ t('Tunggu booster tamat', 'Wait for booster to end') }}</button>

                @elseif ($item['item_type'] === 'booster')
                    <form method="POST" action="{{ route('student.shop', ['tab' => $active_tab]) }}">
                        @csrf
                        <input type="hidden" name="item_id" value="{!! $item['id'] !!}">
                        <button type="submit" name="buy_item" class="btn btn-primary btn-sm w-100" {!! $user['xp_points'] < $item['cost_xp'] ? 'disabled' : '' !!}>💎{!! $item['cost_xp'] !!} XP</button>
                    </form>

                @elseif ($item['unlock_level'] !== null && !$owned)

                @elseif (!$owned)
                    <form method="POST" action="{{ route('student.shop', ['tab' => $active_tab]) }}">
                        @csrf
                        <input type="hidden" name="item_id" value="{!! $item['id'] !!}">
                        <button type="submit" name="buy_item" class="btn btn-primary btn-sm w-100" {!! $user['xp_points'] < $item['cost_xp'] ? 'disabled' : '' !!}>💎{!! $item['cost_xp'] !!} XP</button>
                    </form>
                @elseif ($equipped)
                    <button class="btn btn-success btn-sm w-100" disabled><i class="fas fa-check me-1"></i>{{ t('Sedang Dipakai', 'Currently Equipped') }}</button>
                @else
                    <form method="POST" action="{{ route('student.shop', ['tab' => $active_tab]) }}">
                        @csrf
                        <input type="hidden" name="item_id" value="{!! $item['id'] !!}">
                        <input type="hidden" name="item_type" value="{!! $item['item_type'] !!}">
                        <button type="submit" name="equip_item" class="btn btn-outline-primary btn-sm w-100">{{ t('Pakai', 'Equip') }}</button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if ($is_equipable ?? false)
    @php
 $current_equipped_id = $user[$equipped_column_map[$active_tab]] ?? null; 
@endphp
    @if ($current_equipped_id)
    <form method="POST" action="{{ route('student.shop', ['tab' => $active_tab]) }}" class="mt-3">
        @csrf
        <input type="hidden" name="item_id" value="">
        <input type="hidden" name="item_type" value="{!! $active_tab !!}">
        <button type="submit" name="equip_item" class="btn btn-sm btn-outline-secondary">{{ t('Buang Item Ini', 'Remove This Item') }}</button>
    </form>
    @endif
    @endif
</div>
@endsection
