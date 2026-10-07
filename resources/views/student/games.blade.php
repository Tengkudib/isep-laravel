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
        .game-banner.tictactoe { background: linear-gradient(135deg, #0B2545 0%, #7C3AED 60%, #EC4899 100%); }
        .game-banner.connect4 { background: linear-gradient(135deg, #0B2545 0%, #1D4ED8 60%, #F59E0B 100%); }
        .game-banner.snakes { background: linear-gradient(135deg, #0B2545 0%, #15803D 60%, #D4AF37 100%); }
        .game-banner.quizrush { background: linear-gradient(135deg, #0B2545 0%, #0EA5E9 60%, #D4AF37 100%); }
        .game-banner { position: relative; }
        .game-tag { position: absolute; top: 12px; right: 12px; font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: rgba(255,255,255,.9); color: #0B2545; }
        .xp-table td, .xp-table th { padding: 6px 10px; font-size: .85rem; }
        .xp-note { font-size: 0.78rem; }
    </style>
@endpush

@section('content')
@php
    $games = [
        ['chess', route('student.chess'), 'fas fa-chess-knight', 'Chess', t('Permainan catur klasik. Uji strategi anda dengan komputer atau pelajar lain.', 'Classic chess. Test your strategy against the computer or other students.'), null],
        ['dam', route('student.dam'), 'fas fa-circle', 'Dam Haji', t('Permainan dam tradisional Malaysia. Tangkap semua buah lawan untuk menang!', "Traditional Malaysian checkers. Capture all your opponent's pieces to win!"), null],
        ['snakes', route('student.boardgame', 'snakes'), 'fas fa-dice', t('Ular & Tangga', 'Snakes & Ladders'), t('Baling dadu dan jawab soalan koding untuk naik tangga dan elak ular!', 'Roll the dice and answer coding questions to climb ladders and dodge snakes!'), t('Baharu', 'New')],
        ['connect4', route('student.boardgame', 'connect4'), 'fas fa-circle-dot', 'Connect Four', t('Jatuhkan syiling dan sambung empat sebaris sebelum lawan anda.', 'Drop discs and connect four in a row before your opponent.'), t('Baharu', 'New')],
        ['tictactoe', route('student.boardgame', 'tictactoe'), 'fas fa-hashtag', 'Tic-Tac-Toe', t('X lawan O - pantas dan seronok. Boleh anda kalahkan tahap Sukar?', 'X versus O - quick and fun. Can you beat Hard mode?'), t('Baharu', 'New')],
        ['quizrush', route('student.quizrush'), 'fas fa-stopwatch', t('Kuiz Koding Pantas', 'Quick Code Quiz'), t('Jawab seberapa banyak soalan koding dalam 60 saat untuk XP!', 'Answer as many coding questions as you can in 60 seconds for XP!'), t('Baharu', 'New')],
    ];
@endphp
<div class="topbar">
    <h4 class="fw-bold mb-1"><i class="fas fa-gamepad me-2"></i>{{ t('Permainan', 'Games') }}</h4>
    <p class="mb-0 opacity-75">{{ t('Asah minda anda - lawan komputer atau pelajar lain!', 'Sharpen your mind - play against the computer or other students!') }}</p>
</div>

<div class="container-fluid p-4" style="max-width: 1100px;">
    <div class="row g-4">
        @foreach ($games as [$key, $url, $icon, $name, $desc, $tag])
        <div class="col-md-6 col-xl-4">
            <div class="card-modern game-card">
                <div class="game-banner {{ $key }}"><i class="{{ $icon }}"></i>@if ($tag)<span class="game-tag">{{ $tag }}</span>@endif</div>
                <div class="p-4">
                    <h5 class="fw-bold mb-1">{{ $name }}</h5>
                    <p class="text-muted small mb-3">{{ $desc }}</p>
                    <a href="{{ $url }}" class="btn btn-primary w-100 mb-2"><i class="fas fa-play me-2"></i>{{ t('Main', 'Play') }}</a>
                    <p class="text-muted xp-note mb-0"><i class="fas fa-bolt text-warning me-1"></i>{{ $key === 'quizrush'
                        ? t('2 XP setiap jawapan betul (maks 30 XP), satu pusingan sehari.', '2 XP per correct answer (max 30 XP), one round a day.')
                        : t('XP diberi bila menang lawan komputer - sekali sehari bagi setiap tahap.', 'XP is awarded for wins against the computer - once a day per level.') }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card-modern p-4 mt-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-circle-info me-2 text-primary"></i>{{ t('Cara Ganjaran XP Berfungsi', 'How XP Rewards Work') }}</h6>
        <div class="table-responsive">
            <table class="table xp-table mb-3">
                <thead><tr><th>{{ t('Permainan', 'Game') }}</th><th>{{ t('Mudah', 'Easy') }}</th><th>{{ t('Sederhana', 'Medium') }}</th><th>{{ t('Sukar', 'Hard') }}</th></tr></thead>
                <tbody>
                    <tr><td>Chess / Dam Haji</td><td>+15</td><td>+30</td><td>+50</td></tr>
                    <tr><td>Connect Four</td><td>+10</td><td>+20</td><td>+30</td></tr>
                    <tr><td>{{ t('Ular & Tangga', 'Snakes & Ladders') }}</td><td>+10</td><td>+15</td><td>+20</td></tr>
                    <tr><td>Tic-Tac-Toe</td><td>+5</td><td>+10</td><td>+15</td></tr>
                </tbody>
            </table>
        </div>
        <ul class="text-muted small mb-0">
            <li>{{ t('XP permainan papan diberi bila menang lawan komputer - sekali sehari bagi setiap tahap kesukaran setiap permainan.', 'Board game XP is given for wins against the computer - once a day for each difficulty level of each game.') }}</li>
            <li>{{ t('Kuiz Koding Pantas: 2 XP setiap jawapan betul, maksimum 30 XP, satu pusingan sahaja sehari.', 'Quick Code Quiz: 2 XP per correct answer, max 30 XP, one round a day only.') }}</li>
            <li>{{ t('Melawan pelajar lain tidak memberikan XP - semata-mata untuk keseronokan dan latihan!', 'Playing against other students gives no XP - purely for fun and practice!') }}</li>
        </ul>
    </div>
</div>
@endsection
