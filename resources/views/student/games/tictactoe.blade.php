@php
$game_cfg = [
    'type' => 'tictactoe',
    'title' => 'Tic-Tac-Toe',
    'icon' => 'fas fa-hashtag',
    'gradient' => 'linear-gradient(135deg, #0B2545 0%, #7C3AED 60%, #EC4899 100%)',
    'xp' => ['easy' => 5, 'medium' => 10, 'hard' => 15],
    'subtitle' => t('Susun tiga simbol sebaris untuk menang.', 'Line up three of your marks to win.'),
    'rules' => '<ul class="mb-0"><li>' . t('Pemain pertama ialah <strong>X</strong>, pemain kedua <strong>O</strong>.', 'The first player is <strong>X</strong>, the second is <strong>O</strong>.') . '</li><li>'
        . t('Susun tiga simbol secara mendatar, menegak atau menyerong untuk menang.', 'Line up three marks horizontally, vertically or diagonally to win.') . '</li><li>'
        . t('Tahap Sukar tidak pernah kalah - cuba dapatkan seri!', 'Hard mode never loses - try to get a draw!') . '</li></ul>',
    'i18n' => ['youAre' => t('Anda bermain sebagai %s', 'You are playing as %s')],
    'css' => '
        .ttt-board { display: grid; grid-template-columns: repeat(3, minmax(72px, 120px)); gap: 10px; justify-content: center; }
        .ttt-cell { aspect-ratio: 1; border: none; border-radius: 18px; background: var(--isep-card-bg, #fff); box-shadow: var(--isep-shadow-md); font-size: clamp(2.2rem, 9vw, 3.6rem); font-weight: 900; transition: transform .15s ease, box-shadow .15s ease; }
        .ttt-cell:not(:disabled):hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(124,58,237,0.25); }
        .ttt-cell.x { color: #7C3AED; animation: tttPop .25s ease; }
        .ttt-cell.o { color: #EC4899; animation: tttPop .25s ease; }
        .ttt-cell.win { background: linear-gradient(135deg, #F5D061, #D4AF37); color: #0B2545; }
        .ttt-cell:disabled { cursor: default; }
        @keyframes tttPop { from { transform: scale(.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .ttt-tag.x { color: #7C3AED; } .ttt-tag.o { color: #EC4899; }
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
