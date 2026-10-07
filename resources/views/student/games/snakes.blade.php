@php
$game_cfg = [
    'type' => 'snakes',
    'title' => t('Ular & Tangga', 'Snakes & Ladders'),
    'icon' => 'fas fa-dice',
    'gradient' => 'linear-gradient(135deg, #0B2545 0%, #15803D 60%, #D4AF37 100%)',
    'xp' => ['easy' => 10, 'medium' => 15, 'hard' => 20],
    'subtitle' => t('Ular & Tangga versi pengaturcara - jawab soalan koding untuk naik tangga dan elak ular!', 'Snakes & Ladders for programmers - answer coding questions to climb ladders and dodge snakes!'),
    'rules' => '<ul class="mb-0"><li>' . t('Baling dadu dan gerak ke hadapan. Pemain pertama yang tiba tepat di petak 100 menang (lebihan akan melantun balik).', 'Roll the dice and move forward. The first to land exactly on square 100 wins (overshooting bounces back).') . '</li><li>'
        . t('🪜 Berhenti di kaki tangga: jawab soalan koding dengan betul untuk naik.', '🪜 Land at the foot of a ladder: answer a coding question correctly to climb.') . '</li><li>'
        . t('🐍 Berhenti di kepala ular: jawab dengan betul untuk elak digigit, jika salah anda turun.', "🐍 Land on a snake's head: answer correctly to dodge it, otherwise you slide down.") . '</li><li>'
        . t('Komputer juga "menjawab" - tahap lebih sukar bermaksud komputer lebih kerap betul.', 'The computer "answers" too - harder levels mean it is right more often.') . '</li></ul>',
    'i18n' => [
        'roll' => t('Baling Dadu', 'Roll Dice'), 'you' => t('Anda', 'You'), 'computer' => t('Komputer', 'Computer'), 'opponent' => t('Lawan', 'Opponent'),
        'start' => t('Mula', 'Start'), 'rolled' => t('Dadu: %d', 'Rolled %d'), 'bounce' => t('melantun balik', 'bounced back'),
        'ladderQ' => t('🪜 Tangga ke petak %d! Jawab dengan betul untuk naik.', '🪜 Ladder to square %d! Answer correctly to climb.'),
        'snakeQ' => t('🐍 Ular! Jawab dengan betul untuk elak turun ke petak %d.', '🐍 Snake! Answer correctly to avoid sliding to square %d.'),
        'climbed' => t('naik tangga ke %d', 'climbed to %d'), 'missedLadder' => t('jawapan salah, tidak naik', 'wrong answer, no climb'),
        'dodged' => t('berjaya elak ular', 'dodged the snake'), 'bitten' => t('digigit ular, turun ke %d', 'bitten, slid to %d'),
        'ladderHint' => t('Kaki tangga = soalan untuk naik', 'Ladder foot = question to climb'), 'snakeHint' => t('Kepala ular = soalan untuk elak', 'Snake head = question to dodge'),
    ],
    'css' => '
        .sl-layout { display: grid; grid-template-columns: minmax(0, 1fr) 210px; gap: 18px; align-items: start; }
        .sl-board-wrap { position: relative; }
        .sl-board { display: grid; grid-template-columns: repeat(10, 1fr); border-radius: 14px; overflow: hidden; box-shadow: 0 14px 30px rgba(21,128,61,0.3); border: 4px solid #0B2545; }
        .sl-cell { aspect-ratio: 1; background: #FEF9E7; position: relative; font-size: clamp(.5rem, 1.4vw, .72rem); font-weight: 700; color: rgba(11,37,69,0.55); }
        .sl-cell.alt { background: #E8F5E9; }
        .sl-cell span { position: absolute; top: 2px; left: 4px; }
        .sl-cell.ladder { background: #FDE7C8; } .sl-cell.snake { background: #FADADD; }
        .sl-cell.goal { background: linear-gradient(135deg, #F5D061, #D4AF37); } .sl-cell.goal span { font-size: 1.1rem; top: 18%; left: 24%; }
        .sl-overlay { position: absolute; top: 4px; left: 4px; width: calc(100% - 8px); height: calc(100% - 8px); pointer-events: none; }
        .sl-ladder line { stroke: #92400E; stroke-width: .9; stroke-linecap: round; }
        .sl-ladder line.rung { stroke-width: .6; }
        .sl-snake { fill: none; stroke: url(#slSnake); stroke-width: 2.4; stroke-linecap: round; opacity: .9; }
        .sl-snake-head { fill: #15803D; }
        .sl-tokens { position: absolute; top: 4px; left: 4px; width: calc(100% - 8px); height: calc(100% - 8px); pointer-events: none; }
        .sl-token { position: absolute; width: 5.5%; aspect-ratio: 1; border-radius: 50%; transform: translate(-50%, -50%); border: 3px solid #fff; box-shadow: 0 3px 8px rgba(0,0,0,.4); z-index: 2; }
        .sl-token.p1 { background: #2563EB; } .sl-token.p2 { background: #DC2626; }
        .sl-side { display: flex; flex-direction: column; gap: 10px; }
        .sl-player { display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-radius: 12px; background: var(--isep-card-bg, #fff); box-shadow: var(--isep-shadow-sm); font-weight: 600; border: 2px solid transparent; }
        .sl-player b { margin-left: auto; font-size: 1.1rem; }
        .sl-player.active { border-color: #D4AF37; }
        .sl-dot { width: 14px; height: 14px; border-radius: 50%; } .sl-dot.p1 { background: #2563EB; } .sl-dot.p2 { background: #DC2626; }
        .sl-dice { font-size: 4rem; line-height: 1; text-align: center; color: var(--isep-primary); }
        .sl-event { min-height: 3em; font-size: .85rem; font-weight: 600; text-align: center; }
        .sl-legend { display: flex; flex-direction: column; gap: 2px; }
        [data-theme="dark"] .sl-dice { color: #F5D061; }
        @media (max-width: 767.98px) { .sl-layout { grid-template-columns: 1fr; } }
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
