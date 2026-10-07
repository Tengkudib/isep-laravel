@php
$game_cfg = [
    'type' => 'connect4',
    'title' => 'Connect Four',
    'icon' => 'fas fa-circle-dot',
    'gradient' => 'linear-gradient(135deg, #0B2545 0%, #1D4ED8 60%, #F59E0B 100%)',
    'xp' => ['easy' => 10, 'medium' => 20, 'hard' => 30],
    'subtitle' => t('Jatuhkan syiling dan sambung empat sebaris.', 'Drop discs and connect four in a row.'),
    'rules' => '<ul class="mb-0"><li>' . t('Tekan anak panah (atau mana-mana petak) pada lajur untuk menjatuhkan syiling.', 'Tap the arrow (or any cell) in a column to drop a disc.') . '</li><li>'
        . t('Sambung empat syiling anda secara mendatar, menegak atau menyerong untuk menang.', 'Connect four of your discs horizontally, vertically or diagonally to win.') . '</li><li>'
        . t('Pemain pertama berwarna <strong style="color:#EF4444">merah</strong>, pemain kedua <strong style="color:#D4A20B">kuning</strong>.', 'The first player is <strong style="color:#EF4444">red</strong>, the second <strong style="color:#D4A20B">yellow</strong>.') . '</li></ul>',
    'i18n' => ['youAre' => t('Anda bermain sebagai %s', 'You are playing as %s'), 'column' => t('Lajur', 'Column')],
    'css' => '
        .c4-wrap { max-width: 520px; margin: 0 auto; }
        .c4-board { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; padding: 10px; background: linear-gradient(180deg, #1D4ED8, #1E3A8A); border-radius: 18px; box-shadow: 0 14px 30px rgba(29,78,216,0.35); }
        .c4-cell { aspect-ratio: 1; border-radius: 50%; background: rgba(255,255,255,0.92); display: flex; cursor: pointer; overflow: hidden; }
        .c4-disc { width: 100%; height: 100%; border-radius: 50%; }
        .c4-disc.p1 { background: radial-gradient(circle at 35% 35%, #FCA5A5, #EF4444 55%, #B91C1C); }
        .c4-disc.p2 { background: radial-gradient(circle at 35% 35%, #FEF08A, #FACC15 55%, #CA8A04); }
        .c4-disc.drop { animation: c4Drop .35s cubic-bezier(.4,0,.6,1.4); }
        .c4-disc.win { box-shadow: inset 0 0 0 4px #fff, 0 0 14px 4px rgba(255,255,255,0.9); animation: c4Pulse 1s ease-in-out infinite; }
        @keyframes c4Drop { from { transform: translateY(calc(-110% * (var(--row) + 1))); } to { transform: none; } }
        @keyframes c4Pulse { 50% { transform: scale(.88); } }
        .c4-cols { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; padding: 6px 10px 0; }
        .c4-col-btn { border: none; background: transparent; color: var(--isep-primary); font-size: 1.1rem; border-radius: 8px; padding: 4px 0; }
        .c4-col-btn:not(:disabled):hover { background: rgba(29,78,216,0.12); }
        .c4-col-btn:disabled { opacity: .25; }
        .c4-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; vertical-align: middle; }
        .c4-dot.p1 { background: #EF4444; } .c4-dot.p2 { background: #FACC15; }
        [data-theme="dark"] .c4-cell { background: #1f2433; }
        [data-theme="dark"] .c4-col-btn { color: #F5D061; }
        @media (prefers-reduced-motion: reduce) { .c4-disc.drop, .c4-disc.win { animation: none; } }
    ',
];
@endphp
@extends('layouts.student')

@section('title'){{ $game_cfg['title'] }} - iSEP
@endsection
@section('theme_first', '1')

@section('content')
@include('student.games._frame', ['game_cfg' => $game_cfg, 'xp_today' => $xp_today])
@endsection
