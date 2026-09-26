@extends('layouts.student')

@section('title'){{ t('Permainan', 'Games') }} - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { background: #FAF7F0; font-family: 'Segoe UI', sans-serif; }
        .topbar {
            background: linear-gradient(135deg, #0B2545 0%, #13315C 55%, #D4AF37 100%);
            color: white; padding: 28px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
        }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-sm); }
        .game-card { overflow: hidden; transition: transform var(--isep-duration) var(--isep-ease); height: 100%; }
        .game-card:hover { transform: translateY(-6px); }
        .game-banner {
            height: 150px; display: flex; align-items: center; justify-content: center;
            font-size: 4rem; color: white;
        }
        .game-banner.chess { background: linear-gradient(135deg, #0B2545, #4a5568); }
        .game-banner.dam { background: linear-gradient(135deg, #0B2545 0%, #13315C 55%, #D4AF37 100%); }
        .xp-note { font-size: 0.78rem; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <h4 class="fw-bold mb-1"><i class="fas fa-gamepad me-2"></i>{{ t('Permainan', 'Games') }}</h4>
    <p class="mb-0 opacity-75">{!! t('Asah minda anda dengan Chess &amp; Dam Haji - lawan komputer atau pelajar lain!', 'Sharpen your mind with Chess &amp; Dam Haji - play against the computer or other students!') !!}</p>
</div>

<div class="container-fluid p-4" style="max-width: 900px;">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-modern game-card">
                <div class="game-banner chess"><i class="fas fa-chess-knight"></i></div>
                <div class="p-4">
                    <h5 class="fw-bold mb-1">Chess</h5>
                    <p class="text-muted small mb-3">{{ t('Permainan catur klasik. Uji strategi anda dengan komputer atau pelajar lain.', 'Classic chess game. Test your strategy against the computer or other students.') }}</p>
                    <a href="{{ route('student.chess') }}" class="btn btn-primary w-100 mb-2"><i class="fas fa-play me-2"></i>{{ t('Main Chess', 'Play Chess') }}</a>
                    <p class="text-muted xp-note mb-0"><i class="fas fa-bolt text-warning me-1"></i>{{ t('XP diberi hanya bila menang lawan komputer.', 'XP is only awarded when you win against the computer.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-modern game-card">
                <div class="game-banner dam"><i class="fas fa-circle"></i></div>
                <div class="p-4">
                    <h5 class="fw-bold mb-1">Dam Haji</h5>
                    <p class="text-muted small mb-3">{{ t('Permainan dam tradisional Malaysia. Tangkap semua buah lawan untuk menang!', 'Traditional Malaysian checkers game. Capture all your opponent\'s pieces to win!') }}</p>
                    <a href="{{ route('student.dam') }}" class="btn btn-primary w-100 mb-2"><i class="fas fa-play me-2"></i>{{ t('Main Dam Haji', 'Play Dam Haji') }}</a>
                    <p class="text-muted xp-note mb-0"><i class="fas fa-bolt text-warning me-1"></i>{{ t('XP diberi hanya bila menang lawan komputer.', 'XP is only awarded when you win against the computer.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card-modern p-4 mt-4">
        <h6 class="fw-bold mb-2"><i class="fas fa-circle-info me-2 text-primary"></i>{{ t('Cara Ganjaran XP Berfungsi', 'How XP Rewards Work') }}</h6>
        <ul class="text-muted small mb-0">
            <li>{{ t('Menang melawan', 'Win against') }} <strong>{{ t('Komputer (Mudah)', 'Computer (Easy)') }}</strong> = +15 XP</li>
            <li>{{ t('Menang melawan', 'Win against') }} <strong>{{ t('Komputer (Sederhana)', 'Computer (Medium)') }}</strong> = +30 XP</li>
            <li>{{ t('Menang melawan', 'Win against') }} <strong>{{ t('Komputer (Sukar)', 'Computer (Hard)') }}</strong> = +50 XP</li>
            <li>{{ t('Melawan', 'Playing against') }} <strong>{{ t('pelajar lain', 'other students') }}</strong> {{ t('tidak akan memberikan sebarang XP, sama ada menang, kalah atau seri - permainan ini semata-mata untuk keseronokan dan latihan!', 'will not give any XP, whether you win, lose or draw - this game is purely for fun and practice!') }}</li>
        </ul>
    </div>
</div>
@endsection
