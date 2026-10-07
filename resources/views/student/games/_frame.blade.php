@php
    $__g = $game_cfg;
    $__xp_today = $xp_today ?? [];
    $__diff_labels = ['easy' => t('Mudah', 'Easy'), 'medium' => t('Sederhana', 'Medium'), 'hard' => t('Sukar', 'Hard')];
    $__i18n = array_merge([
        'diffEasy' => $__diff_labels['easy'], 'diffMedium' => $__diff_labels['medium'], 'diffHard' => $__diff_labels['hard'],
        'lobbyEmpty' => t('Tiada perlawanan terbuka buat masa ini. Cipta satu!', 'No open matches right now. Create one!'),
        'joinBtn' => t('Sertai', 'Join'),
        'opponentComputer' => t('Lawan: Komputer', 'Opponent: Computer'),
        'opponentPrefix' => t('Lawan: ', 'Opponent: '),
        'waiting' => t('Menunggu...', 'Waiting...'),
        'computerThinking' => t('Komputer sedang berfikir...', 'Computer is thinking...'),
        'yourTurn' => t('Giliran anda', 'Your turn'),
        'computerTurn' => t('Giliran Komputer', "Computer's turn"),
        'opponentTurn' => t('Giliran lawan', "Opponent's turn"),
        'gameOver' => t('Permainan tamat', 'Game over'),
        'resultDraw' => t('Permainan tamat seri.', 'The game ended in a draw.'),
        'resultWin' => t('Tahniah, anda menang!', 'Congratulations, you won!'),
        'resultLose' => t('Anda kalah. Cuba lagi!', 'You lost. Try again!'),
        'modalWinTitle' => t('Anda Menang!', 'You Win!'),
        'modalWinSub' => t('Tahniah, anda kalahkan komputer (%s).', 'Congratulations, you beat the computer (%s).'),
        'modalWinSubPvp' => t('Tahniah, anda kalahkan lawan anda!', 'Congratulations, you beat your opponent!'),
        'modalLoseTitle' => t('Anda Kalah', 'You Lose'),
        'modalLoseSub' => t('Jangan risau, cuba lagi!', "Don't worry, try again!"),
        'modalDrawTitle' => t('Seri!', 'Draw!'),
        'modalDrawSub' => t('Perlawanan yang sengit - hasilnya seri.', 'An intense game - it ended in a draw.'),
        'confirmResign' => t('Adakah anda pasti mahu mengalah?', 'Are you sure you want to resign?'),
        'questionTitle' => t('Soalan Koding!', 'Coding Question!'),
        'questionCorrect' => t('Betul!', 'Correct!'),
        'questionWrong' => t('Salah. Jawapan betul: ', 'Wrong. Correct answer: '),
    ], $__g['i18n'] ?? []);
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/game-engine.css') }}?v=1">
<style>
    .topbar { background: {!! $__g['gradient'] !!}; }
    {!! $__g['css'] ?? '' !!}
</style>
@endpush

<div class="topbar">
    <a href="{{ route('student.games') }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Permainan', 'Games') }}</a>
    <h4 class="fw-bold mt-1 mb-0"><i class="{{ $__g['icon'] }} me-2"></i>{{ $__g['title'] }}</h4>
    @if (!empty($__g['subtitle']))<p class="mb-0 mt-1 opacity-75 small">{{ $__g['subtitle'] }}</p>@endif
</div>

<div class="container-fluid p-4" style="max-width: 820px;">
    <div id="selectionPanel">
        <div id="modeChoice" class="row g-3">
            <div class="col-md-6">
                <div class="mode-btn" role="button" tabindex="0" onclick="GE.showAiDifficulty()">
                    <i class="fas fa-robot"></i>
                    <h6 class="fw-bold mb-1">{{ t('Lawan Komputer', 'Play vs Computer') }}</h6>
                    <small class="text-muted">{!! t('Pilih tahap kesukaran &middot; dapat XP bila menang', 'Choose a difficulty level &middot; earn XP when you win') !!}</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mode-btn" role="button" tabindex="0" onclick="GE.showPvpLobby()">
                    <i class="fas fa-user-friends"></i>
                    <h6 class="fw-bold mb-1">{{ t('Lawan Pelajar Lain', 'Play vs Another Student') }}</h6>
                    <small class="text-muted">{!! t('Cipta atau sertai perlawanan &middot; tiada XP', 'Create or join a match &middot; no XP') !!}</small>
                </div>
            </div>
        </div>

        <div id="aiDifficultyPanel" class="card-modern p-4 mt-3" style="display:none;">
            <h6 class="fw-bold mb-3"><i class="fas fa-robot me-2"></i>{{ t('Pilih Tahap Kesukaran', 'Choose a Difficulty Level') }}</h6>
            <div class="row g-2">
                @foreach (['easy' => 'success', 'medium' => 'warning', 'hard' => 'danger'] as $d => $color)
                <div class="col-4"><button class="btn btn-outline-{{ $color }} diff-btn w-100" onclick="GE.startAi('{{ $d }}')" data-diff="{{ $d }}">{{ $__diff_labels[$d] }}<br>
                    @if (in_array($d, $__xp_today, true))<small class="fw-normal"><i class="fas fa-check me-1"></i>{{ t('XP hari ini', 'XP today') }}</small>
                    @else<small class="fw-normal">+{{ (int) $__g['xp'][$d] }} XP</small>@endif
                </button></div>
                @endforeach
            </div>
            <button class="btn btn-link btn-sm mt-2 text-muted" onclick="GE.backToModeChoice()">&larr; {{ t('Kembali', 'Back') }}</button>
        </div>

        <div id="pvpLobbyPanel" class="card-modern p-4 mt-3" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-friends me-2"></i>{{ t('Lawan Pelajar Lain', 'Play vs Another Student') }}</h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="GE.refreshLobby()" aria-label="{{ t('Muat semula', 'Refresh') }}"><i class="fas fa-rotate"></i></button>
            </div>
            <button class="btn btn-primary w-100 mb-3" onclick="GE.createPvp()"><i class="fas fa-plus me-2"></i>{{ t('Cipta Perlawanan Baharu', 'Create New Match') }}</button>
            <div id="lobbyList"><p class="text-muted small text-center py-3">{{ t('Memuatkan senarai...', 'Loading list...') }}</p></div>
            <button class="btn btn-link btn-sm mt-2 text-muted" onclick="GE.backToModeChoice()">&larr; {{ t('Kembali', 'Back') }}</button>
        </div>

        <div id="waitingPanel" class="card-modern p-4 mt-3 text-center" style="display:none;">
            <div class="waiting-spinner mb-3"></div>
            <h6 class="fw-bold">{{ t('Menunggu pelajar lain menyertai...', 'Waiting for another student to join...') }}</h6>
            <p class="text-muted small">{{ t('Perlawanan anda kini disenaraikan dalam lobi.', 'Your match is now listed in the lobby.') }}</p>
            <button class="btn btn-outline-danger btn-sm" onclick="GE.cancelWaiting()">{{ t('Batalkan', 'Cancel') }}</button>
        </div>

        @if (!empty($__g['rules']))
        <div class="card-modern p-4 mt-3 small text-muted">
            <h6 class="fw-bold text-body mb-2"><i class="fas fa-book-open me-2"></i>{{ t('Cara Bermain', 'How to Play') }}</h6>
            {!! $__g['rules'] !!}
        </div>
        @endif
    </div>

    <div id="gamePanel" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <span id="opponentLabel" class="fw-semibold"></span>
            <span id="turnPill" class="turn-pill bg-primary text-white"></span>
        </div>
        <div id="gameBoard" class="mb-3"></div>
        <div id="gameStatus" class="text-center text-muted small mb-3" aria-live="polite"></div>
        <div class="text-center d-flex gap-2 justify-content-center">
            <button class="btn btn-outline-danger btn-sm" id="resignBtn" onclick="GE.resign()"><i class="fas fa-flag me-1"></i>{{ t('Mengalah', 'Resign') }}</button>
            <button class="btn btn-outline-secondary btn-sm" onclick="GE.backToSelection()"><i class="fas fa-arrow-left me-1"></i>{{ t('Kembali', 'Back') }}</button>
        </div>
    </div>
</div>

<canvas id="confettiCanvas" style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:999; display:none;"></canvas>

<div class="result-overlay" id="resultOverlay">
    <div class="result-card" id="resultCard" role="dialog" aria-modal="true" aria-labelledby="resultTitle">
        <div class="result-icon" id="resultIcon"><i class="fas fa-trophy"></i></div>
        <div class="result-title" id="resultTitle"></div>
        <div class="result-sub" id="resultSub"></div>
        <div class="result-xp-badge" id="resultXpBadge" style="display:none;"><i class="fas fa-bolt me-1"></i><span id="resultXpText"></span></div>
        <div class="result-actions">
            <button type="button" class="btn btn-outline-secondary flex-fill" onclick="GE.goToDashboard()">{{ t('Kembali ke Dashboard', 'Back to Dashboard') }}</button>
            <button type="button" class="btn btn-primary flex-fill" onclick="GE.playAgain()">{{ t('Main Semula', 'Play Again') }}</button>
        </div>
    </div>
</div>

<div class="result-overlay" id="questionOverlay">
    <div class="result-card question-card" role="dialog" aria-modal="true" aria-labelledby="questionTitle">
        <div class="question-badge" id="questionReason"></div>
        <h5 class="fw-bold mb-2" id="questionTitle">{{ $__i18n['questionTitle'] }}</h5>
        <p class="question-text" id="questionText"></p>
        <div class="d-grid gap-2" id="questionOptions"></div>
        <div class="question-feedback mt-3" id="questionFeedback"></div>
    </div>
</div>

<script>
window.GAME_CFG = {
    type: {!! json_encode($__g['type']) !!},
    title: {!! json_encode($__g['title']) !!},
    csrf: {!! json_encode(csrf_token()) !!},
    tokenField: '_token',
    api: {!! json_encode(route('student.games.api', [], false)) !!},
    dashboard: {!! json_encode(route('student.dashboard', [], false)) !!},
    i18n: {!! json_encode($__i18n) !!}
};
</script>
<script src="{{ asset('assets/game-engine.js') }}?v=1"></script>
<script src="{{ asset('assets/games/' . $__g['type'] . '.js') }}?v=1"></script>
<script>GE.init();</script>
