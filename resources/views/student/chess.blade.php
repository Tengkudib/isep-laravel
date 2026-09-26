@extends('layouts.student')

@section('title')Chess - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { background: #FAF7F0; font-family: 'Segoe UI', sans-serif; }
        .topbar {
            background: linear-gradient(135deg, #0B2545 0%, #4a5568 55%, #0B2545 100%);
            color: white; padding: 24px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
        }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-md); }
        .mode-btn { border-radius: var(--isep-r-xl); padding: 28px 20px; text-align: center; cursor: pointer; transition: all var(--isep-duration) var(--isep-ease); border: 2px solid transparent; background: var(--isep-card-bg); }
        .mode-btn:hover { border-color: var(--isep-primary); transform: translateY(-3px); }
        .mode-btn i { font-size: 2rem; margin-bottom: 10px; color: var(--isep-secondary); }
        .diff-btn { border-radius: var(--isep-r-lg); padding: 14px; font-weight: 700; }
        .lobby-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: var(--isep-r-lg); background: var(--isep-bg); margin-bottom: 8px; }

        .board-wrap { display: flex; justify-content: center; }
        .chess-board {
            display: grid; grid-template-columns: repeat(8, minmax(34px, 62px)); grid-template-rows: repeat(8, minmax(34px, 62px));
            border: 4px solid #0B2545; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .sq { display: flex; align-items: center; justify-content: center; font-size: clamp(1.4rem, 4vw, 2.4rem); cursor: pointer; position: relative; user-select: none; }
        .sq.light { background: #EDEBF5; }
        .sq.dark { background: #13315C; }
        .sq.selected { outline: 4px solid #D4AF37; outline-offset: -4px; }
        .sq.legal::after { content: ''; position: absolute; width: 28%; height: 28%; border-radius: 50%; background: rgba(40,167,69,0.55); }
        .sq.legal-capture { outline: 4px solid rgba(255,107,107,0.85); outline-offset: -4px; }
        .sq .piece-w { color: #ffffff; text-shadow: 0 0 2px #000, 0 1px 3px rgba(0,0,0,0.6); }
        .sq .piece-b { color: #16161d; text-shadow: 0 1px 2px rgba(255,255,255,0.35); }
        .turn-pill { padding: 8px 16px; border-radius: var(--isep-r-xl); font-weight: 700; font-size: 0.85rem; }
        .waiting-spinner { width: 46px; height: 46px; border: 5px solid #eee; border-top-color: var(--isep-primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---------- Result popup (win / lose / draw vs Computer) ---------- */
        .result-overlay {
            position: fixed; inset: 0; z-index: 1200; display: flex; align-items: center; justify-content: center;
            background: rgba(10, 10, 20, 0); padding: 20px; pointer-events: none;
            transition: background 0.25s ease;
        }
        .result-overlay.show { background: rgba(10, 10, 20, 0.55); pointer-events: auto; }
        .result-card {
            width: 100%; max-width: 380px; text-align: center; padding: 38px 30px; border-radius: var(--isep-r-xl);
            background: var(--isep-card-bg); box-shadow: var(--isep-shadow-xl);
            transform: scale(0.75) translateY(20px); opacity: 0; transition: transform 0.35s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease;
        }
        .result-overlay.show .result-card { transform: scale(1) translateY(0); opacity: 1; }
        .result-icon {
            width: 88px; height: 88px; margin: 0 auto 18px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 2.6rem; color: white;
            box-shadow: var(--isep-shadow-md);
        }
        .result-card.win .result-icon { background: linear-gradient(135deg, #D4AF37, #B8941F); animation: popBounce 0.6s ease; }
        .result-card.lose .result-icon { background: linear-gradient(135deg, #6c757d, #343a40); }
        .result-card.draw .result-icon { background: linear-gradient(135deg, #13315C, #D4AF37); }
        @keyframes popBounce { 0% { transform: scale(0); } 60% { transform: scale(1.15); } 100% { transform: scale(1); } }
        .result-title { font-weight: 800; font-size: 1.5rem; margin-bottom: 6px; }
        .result-card.win .result-title { color: #d97706; }
        .result-card.lose .result-title { color: #495057; }
        .result-card.draw .result-title { color: var(--isep-primary); }
        .result-sub { color: var(--isep-text-muted, #6c757d); font-size: 0.9rem; margin-bottom: 18px; }
        .result-xp-badge {
            display: inline-block; background: linear-gradient(135deg, #D4AF3733, #B8941F33); color: #8a6d00;
            font-weight: 700; padding: 8px 18px; border-radius: var(--isep-r-xl); margin-bottom: 22px; font-size: 0.95rem;
        }
        .result-actions { display: flex; gap: 10px; }
        .result-actions .btn { border-radius: var(--isep-r-lg); font-weight: 600; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <a href="{{ route('student.games') }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Permainan', 'Games') }}</a>
    <h4 class="fw-bold mt-1 mb-0"><i class="fas fa-chess-knight me-2"></i>Chess</h4>
</div>

<div class="container-fluid p-4" style="max-width: 760px;">

    <!-- SELECTION PANEL -->
    <div id="selectionPanel">
        <div id="modeChoice" class="row g-3">
            <div class="col-md-6">
                <div class="mode-btn" onclick="showAiDifficulty()">
                    <i class="fas fa-robot"></i>
                    <h6 class="fw-bold mb-1">{{ t('Lawan Komputer', 'Play vs Computer') }}</h6>
                    <small class="text-muted">{!! t('Pilih tahap kesukaran &middot; dapat XP bila menang', 'Choose a difficulty level &middot; earn XP when you win') !!}</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mode-btn" onclick="showPvpLobby()">
                    <i class="fas fa-user-friends"></i>
                    <h6 class="fw-bold mb-1">{{ t('Lawan Pelajar Lain', 'Play vs Another Student') }}</h6>
                    <small class="text-muted">{!! t('Cipta atau sertai perlawanan &middot; tiada XP', 'Create or join a match &middot; no XP') !!}</small>
                </div>
            </div>
        </div>

        <div id="aiDifficultyPanel" class="card-modern p-4 mt-3" style="display:none;">
            <h6 class="fw-bold mb-3"><i class="fas fa-robot me-2"></i>{{ t('Pilih Tahap Kesukaran', 'Choose a Difficulty Level') }}</h6>
            <div class="row g-2">
                <div class="col-4"><button class="btn btn-outline-success diff-btn w-100" onclick="startAiGame('easy')">{{ t('Mudah', 'Easy') }}<br><small class="fw-normal">+15 XP</small></button></div>
                <div class="col-4"><button class="btn btn-outline-warning diff-btn w-100" onclick="startAiGame('medium')">{{ t('Sederhana', 'Medium') }}<br><small class="fw-normal">+30 XP</small></button></div>
                <div class="col-4"><button class="btn btn-outline-danger diff-btn w-100" onclick="startAiGame('hard')">{{ t('Sukar', 'Hard') }}<br><small class="fw-normal">+50 XP</small></button></div>
            </div>
            <button class="btn btn-link btn-sm mt-2 text-muted" onclick="backToModeChoice()">&larr; {{ t('Kembali', 'Back') }}</button>
        </div>

        <div id="pvpLobbyPanel" class="card-modern p-4 mt-3" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-friends me-2"></i>{{ t('Lawan Pelajar Lain', 'Play vs Another Student') }}</h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshLobby()"><i class="fas fa-rotate"></i></button>
            </div>
            <button class="btn btn-primary w-100 mb-3" onclick="createPvpMatch()"><i class="fas fa-plus me-2"></i>{{ t('Cipta Perlawanan Baharu', 'Create New Match') }}</button>
            <div id="lobbyList"><p class="text-muted small text-center py-3">{{ t('Memuatkan senarai...', 'Loading list...') }}</p></div>
            <button class="btn btn-link btn-sm mt-2 text-muted" onclick="backToModeChoice()">&larr; {{ t('Kembali', 'Back') }}</button>
        </div>

        <div id="waitingPanel" class="card-modern p-4 mt-3 text-center" style="display:none;">
            <div class="waiting-spinner mb-3"></div>
            <h6 class="fw-bold">{{ t('Menunggu pelajar lain menyertai...', 'Waiting for another student to join...') }}</h6>
            <p class="text-muted small">{{ t('Perlawanan anda kini disenaraikan dalam lobi.', 'Your match is now listed in the lobby.') }}</p>
            <button class="btn btn-outline-danger btn-sm" onclick="cancelWaiting()">{{ t('Batalkan', 'Cancel') }}</button>
        </div>
    </div>

    <!-- GAME PANEL -->
    <div id="gamePanel" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <span id="opponentLabel" class="fw-semibold">{{ t('Lawan: Komputer', 'Opponent: Computer') }}</span>
            </div>
            <span id="turnPill" class="turn-pill bg-primary text-white">{{ t('Giliran anda', 'Your turn') }}</span>
        </div>
        <div class="board-wrap mb-3">
            <div id="chessBoard" class="chess-board"></div>
        </div>
        <div id="gameStatus" class="text-center text-muted small mb-3"></div>
        <div class="text-center d-flex gap-2 justify-content-center">
            <button class="btn btn-outline-danger btn-sm" id="resignBtn" onclick="resignGame()"><i class="fas fa-flag me-1"></i>{{ t('Mengalah', 'Resign') }}</button>
            <button class="btn btn-outline-secondary btn-sm" onclick="backToSelection()"><i class="fas fa-arrow-left me-1"></i>{{ t('Kembali', 'Back') }}</button>
        </div>
    </div>

</div>

<canvas id="confettiCanvas" style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:999; display:none;"></canvas>

<div class="result-overlay" id="resultOverlay">
    <div class="result-card" id="resultCard">
        <div class="result-icon" id="resultIcon"><i class="fas fa-trophy"></i></div>
        <div class="result-title" id="resultTitle">{{ t('Anda Menang!', 'You Win!') }}</div>
        <div class="result-sub" id="resultSub">{{ t('Tahniah, anda kalahkan komputer.', 'Congratulations, you beat the computer.') }}</div>
        <div class="result-xp-badge" id="resultXpBadge" style="display:none;"><i class="fas fa-bolt me-1"></i><span id="resultXpText"></span></div>
        <div class="result-actions">
            <button type="button" class="btn btn-outline-secondary flex-fill" onclick="goToDashboard()">{{ t('Kembali ke Dashboard', 'Back to Dashboard') }}</button>
            <button type="button" class="btn btn-primary flex-fill" id="resultPlayAgainBtn" onclick="playAgainSameSettings()">{{ t('Main Semula', 'Play Again') }}</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
<script>
const CSRF_TOKEN = {!! json_encode(csrf_token()) !!};
const MY_ID = {{ (int) $student_id }};
const GAME_API = {!! json_encode(route('student.games.api')) !!};

// UI text (server-rendered, translated) used by the JS below — game logic itself is untouched.
const I18N = {
    diffEasy: {!! json_encode(t('Mudah', 'Easy')) !!},
    diffMedium: {!! json_encode(t('Sederhana', 'Medium')) !!},
    diffHard: {!! json_encode(t('Sukar', 'Hard')) !!},
    lobbyEmpty: {!! json_encode(t('Tiada perlawanan terbuka buat masa ini. Cipta satu!', 'No open matches right now. Create one!')) !!},
    joinBtn: {!! json_encode(t('Sertai', 'Join')) !!},
    opponentComputer: {!! json_encode(t('Lawan: Komputer', 'Opponent: Computer')) !!},
    opponentPrefix: {!! json_encode(t('Lawan: ', 'Opponent: ')) !!},
    waitingOpponentName: {!! json_encode(t('Menunggu...', 'Waiting...')) !!},
    computerThinking: {!! json_encode(t('Komputer sedang berfikir...', 'Computer is thinking...')) !!},
    draw: {!! json_encode(t('Seri', 'Draw')) !!},
    yourTurn: {!! json_encode(t('Giliran anda', 'Your turn')) !!},
    computerTurn: {!! json_encode(t('Giliran Komputer', "Computer's turn")) !!},
    opponentTurn: {!! json_encode(t('Giliran lawan', "Opponent's turn")) !!},
    checkStatus: {!! json_encode(t('Raja anda/lawan sedang diserang (Check)!', 'Your/opponent king is under attack (Check)!')) !!},
    resultDraw: {!! json_encode(t('Permainan tamat seri.', 'The game ended in a draw.')) !!},
    resultWin: {!! json_encode(t('Tahniah, anda menang!', 'Congratulations, you won!')) !!},
    resultLose: {!! json_encode(t('Anda kalah. Cuba lagi!', 'You lost. Try again!')) !!},
    modalWinTitle: {!! json_encode(t('Anda Menang!', 'You Win!')) !!},
    modalWinSubTemplate: {!! json_encode(t('Tahniah, anda kalahkan komputer (%s).', 'Congratulations, you beat the computer (%s).')) !!},
    modalLoseTitle: {!! json_encode(t('Anda Kalah', 'You Lose')) !!},
    modalLoseSub: {!! json_encode(t('Jangan risau, cuba lagi untuk perbaiki strategi anda!', "Don't worry, try again to improve your strategy!")) !!},
    modalDrawTitle: {!! json_encode(t('Seri!', 'Draw!')) !!},
    modalDrawSub: {!! json_encode(t('Permainan yang sengit - hasilnya seri.', 'An intense game - it ended in a draw.')) !!},
    confirmResign: {!! json_encode(t('Adakah anda pasti mahu mengalah?', 'Are you sure you want to resign?')) !!}
};

let game = new Chess();
let mode = null;          // 'ai' | 'pvp'
let difficulty = null;
let matchId = null;
let myColor = 'w';        // 'w' or 'b'
let selectedSquare = null;
let pollTimer = null;
let waitTimer = null;
let gameOver = false;

function apiPost(action, params) {
    const body = new URLSearchParams({ action, _token: CSRF_TOKEN, ...params });
    return fetch(GAME_API, { method: 'POST', body, headers: { 'Accept': 'application/json' } }).then(r => r.json());
}
function apiGet(action, params) {
    const qs = new URLSearchParams({ action, ...params });
    return fetch(GAME_API + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } }).then(r => r.json());
}

// ---------------- Selection panel navigation ----------------
function showAiDifficulty() {
    document.getElementById('modeChoice').style.display = 'none';
    document.getElementById('aiDifficultyPanel').style.display = 'block';
}
function backToModeChoice() {
    document.getElementById('modeChoice').style.display = 'flex';
    document.getElementById('aiDifficultyPanel').style.display = 'none';
    document.getElementById('pvpLobbyPanel').style.display = 'none';
    document.getElementById('waitingPanel').style.display = 'none';
}
function showPvpLobby() {
    document.getElementById('modeChoice').style.display = 'none';
    document.getElementById('pvpLobbyPanel').style.display = 'block';
    refreshLobby();
}

function refreshLobby() {
    apiGet('lobby', { game_type: 'chess' }).then(res => {
        const list = document.getElementById('lobbyList');
        if (!res.open_matches || res.open_matches.length === 0) {
            list.innerHTML = '<p class="text-muted small text-center py-3">' + I18N.lobbyEmpty + '</p>';
            return;
        }
        list.innerHTML = res.open_matches.map(m =>
            `<div class="lobby-row"><span><i class="fas fa-user me-2"></i>${escapeHtml(m.creator_name)}</span>
             <button class="btn btn-sm btn-primary" onclick="joinPvpMatch(${m.id})">${I18N.joinBtn}</button></div>`
        ).join('');
    });
}

function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

// ---------------- Start / join games ----------------
function startAiGame(diff) {
    mode = 'ai'; difficulty = diff; matchId = null; myColor = 'w';
    game = new Chess();
    gameOver = false;
    document.getElementById('opponentLabel').innerHTML = `${I18N.opponentComputer} <span class="badge bg-secondary">${diff === 'easy' ? I18N.diffEasy : diff === 'medium' ? I18N.diffMedium : I18N.diffHard}</span>`;
    document.getElementById('resignBtn').style.display = 'inline-block';
    enterGamePanel();
    renderBoard();
    updateStatus();
    // Daftar perlawanan di pelayan sejak mula supaya tempoh permainan boleh disahkan untuk XP
    apiPost('create', { game_type: 'chess', mode: 'ai', ai_difficulty: diff }).then(res => {
        if (res.match && !gameOver) matchId = res.match.match_id;
    });
}

function createPvpMatch() {
    apiPost('create', { game_type: 'chess', mode: 'pvp' }).then(res => {
        if (res.error) { alert(res.error); return; }
        matchId = res.match.match_id;
        document.getElementById('pvpLobbyPanel').style.display = 'none';
        document.getElementById('waitingPanel').style.display = 'block';
        waitTimer = setInterval(checkWaitingMatch, 2500);
    });
}
function checkWaitingMatch() {
    apiGet('state', { match_id: matchId }).then(res => {
        if (res.match && res.match.status === 'ongoing') {
            clearInterval(waitTimer);
            mode = 'pvp'; myColor = 'w';
            loadPvpMatch(res.match);
        }
    });
}
function cancelWaiting() {
    clearInterval(waitTimer);
    apiPost('cancel', { match_id: matchId }).then(() => {
        matchId = null;
        document.getElementById('waitingPanel').style.display = 'none';
        document.getElementById('pvpLobbyPanel').style.display = 'block';
        refreshLobby();
    });
}
function joinPvpMatch(id) {
    apiPost('join', { match_id: id }).then(res => {
        if (res.error) { alert(res.error); refreshLobby(); return; }
        matchId = id;
        mode = 'pvp'; myColor = 'b';
        loadPvpMatch(res.match);
    });
}
function loadPvpMatch(m) {
    game = new Chess();
    game.load(m.board_state);
    gameOver = false;
    const oppName = myColor === 'w' ? m.player2_name : m.player1_name;
    document.getElementById('opponentLabel').textContent = I18N.opponentPrefix + (oppName || I18N.waitingOpponentName);
    document.getElementById('resignBtn').style.display = 'inline-block';
    enterGamePanel();
    renderBoard();
    updateStatus();
    pollTimer = setInterval(pollPvpState, 2500);
}

function enterGamePanel() {
    document.getElementById('selectionPanel').style.display = 'none';
    document.getElementById('gamePanel').style.display = 'block';
    selectedSquare = null;
}
function backToSelection() {
    clearInterval(pollTimer); clearInterval(waitTimer);
    document.getElementById('gamePanel').style.display = 'none';
    document.getElementById('selectionPanel').style.display = 'block';
    backToModeChoice();
}

// ---------------- Board rendering ----------------
function squareName(row, col, flip) {
    // row 0 = rank 8 (top) in chess.js .board() ordering; col 0 = file a
    const files = 'abcdefgh';
    const rank = flip ? row + 1 : 8 - row;
    const file = flip ? files[7 - col] : files[col];
    return file + rank;
}
const PIECE_UNICODE = {
    w: { p: '♙', n: '♘', b: '♗', r: '♖', q: '♕', k: '♔' },
    b: { p: '♟', n: '♞', b: '♝', r: '♜', q: '♛', k: '♚' }
};

function renderBoard(legalTargets = []) {
    const boardData = game.board();
    const flip = myColor === 'b';
    const container = document.getElementById('chessBoard');
    let html = '';
    for (let r = 0; r < 8; r++) {
        for (let c = 0; c < 8; c++) {
            const displayRow = flip ? 7 - r : r;
            const displayCol = flip ? 7 - c : c;
            const piece = boardData[displayRow][displayCol];
            const sq = squareName(displayRow, displayCol, false);
            const isLight = (displayRow + displayCol) % 2 === 0;
            let cls = 'sq ' + (isLight ? 'light' : 'dark');
            if (selectedSquare === sq) cls += ' selected';
            const target = legalTargets.find(t => t.to === sq);
            if (target) cls += target.flags.includes('c') || target.flags.includes('e') ? ' legal-capture' : ' legal';
            let inner = '';
            if (piece) inner = `<span class="piece-${piece.color}">${PIECE_UNICODE[piece.color][piece.type]}</span>`;
            html += `<div class="${cls}" data-sq="${sq}" onclick="onSquareClick('${sq}')">${inner}</div>`;
        }
    }
    container.innerHTML = html;
}

function onSquareClick(sq) {
    if (gameOver) return;
    if (mode === 'pvp' && game.turn() !== myColor) return;
    if (mode === 'ai' && game.turn() !== myColor) return;

    const piece = game.get(sq);

    if (selectedSquare === null) {
        if (piece && piece.color === myColor) {
            selectedSquare = sq;
            const moves = game.moves({ square: sq, verbose: true });
            renderBoard(moves);
        }
        return;
    }

    if (selectedSquare === sq) {
        selectedSquare = null;
        renderBoard();
        return;
    }

    const moves = game.moves({ square: selectedSquare, verbose: true });
    const chosen = moves.find(m => m.to === sq);

    if (!chosen) {
        // clicked another own piece -> reselect; otherwise deselect
        if (piece && piece.color === myColor) {
            selectedSquare = sq;
            const newMoves = game.moves({ square: sq, verbose: true });
            renderBoard(newMoves);
        } else {
            selectedSquare = null;
            renderBoard();
        }
        return;
    }

    const moveResult = game.move({ from: selectedSquare, to: sq, promotion: 'q' });
    selectedSquare = null;
    if (!moveResult) { renderBoard(); return; }

    renderBoard();
    updateStatus();
    afterMove();
}

function afterMove() {
    if (checkGameOverAndReport()) return;

    if (mode === 'pvp') {
        const nextTurn = game.turn() === 'w' ? 'player1' : 'player2';
        apiPost('move', { match_id: matchId, board_state: game.fen(), next_turn: nextTurn }).then(res => {
            if (res.error) alert(res.error);
        });
    } else if (mode === 'ai') {
        document.getElementById('turnPill').textContent = I18N.computerThinking;
        document.getElementById('turnPill').className = 'turn-pill bg-secondary text-white';
        setTimeout(makeAiMove, 500);
    }
}

function makeAiMove() {
    const best = getAiMove(game, difficulty);
    if (best) game.move(best);
    renderBoard();
    updateStatus();
    checkGameOverAndReport();
}

function updateStatus() {
    const pill = document.getElementById('turnPill');
    const status = document.getElementById('gameStatus');
    if (game.in_checkmate()) {
        pill.textContent = 'Checkmate!'; pill.className = 'turn-pill bg-danger text-white';
    } else if (game.in_draw() || game.in_stalemate() || game.in_threefold_repetition()) {
        pill.textContent = I18N.draw; pill.className = 'turn-pill bg-secondary text-white';
    } else if (game.turn() === myColor) {
        pill.textContent = I18N.yourTurn; pill.className = 'turn-pill bg-primary text-white';
    } else {
        pill.textContent = mode === 'ai' ? I18N.computerTurn : I18N.opponentTurn; pill.className = 'turn-pill bg-secondary text-white';
    }
    status.textContent = game.in_check() && !game.in_checkmate() ? I18N.checkStatus : '';
}

function checkGameOverAndReport() {
    if (!game.game_over()) return false;
    if (gameOver) return true;
    gameOver = true;

    let winnerRole = 'draw';
    if (game.in_checkmate()) {
        const losingColor = game.turn(); // side to move is the one checkmated
        const winningColor = losingColor === 'w' ? 'b' : 'w';
        winnerRole = winningColor === 'w' ? 'player1' : 'player2';
    }
    updateStatus();

    if (mode === 'ai') {
        if (!matchId) {
            apiPost('create', { game_type: 'chess', mode: 'ai', ai_difficulty: difficulty }).then(res => {
                matchId = res.match.match_id;
                apiPost('finish', { match_id: matchId, winner: winnerRole }).then(res2 => {
                    reportResultUi(winnerRole, res2.xp_message, res2.celebration_colors);
                });
            });
        } else {
            apiPost('finish', { match_id: matchId, winner: winnerRole }).then(res => {
                reportResultUi(winnerRole, res.xp_message, res.celebration_colors);
            });
        }
    } else if (mode === 'pvp') {
        apiPost('finish', { match_id: matchId, winner: winnerRole }).then(() => {
            reportResultUi(winnerRole, null);
        });
        clearInterval(pollTimer);
    }
    return true;
}

function reportResultUi(winnerRole, xpMessage, celebrationColors) {
    const iAmPlayer1 = myColor === 'w';
    const iWon = (iAmPlayer1 && winnerRole === 'player1') || (!iAmPlayer1 && winnerRole === 'player2');
    let msg;
    if (winnerRole === 'draw') msg = I18N.resultDraw;
    else msg = iWon ? I18N.resultWin : I18N.resultLose;
    if (xpMessage) msg += ' ' + xpMessage;
    document.getElementById('gameStatus').innerHTML = '<strong>' + msg + '</strong>';

    if (mode === 'ai') {
        const outcome = winnerRole === 'draw' ? 'draw' : (iWon ? 'win' : 'lose');
        showResultModal(outcome, xpMessage);
        if (outcome === 'win' && celebrationColors) playCelebration(celebrationColors);
    }
}

// ---------------- Result popup (win / lose / draw vs Computer) ----------------
let lastXpAmount = null;

function showResultModal(outcome, xpMessage) {
    lastXpAmount = xpMessage ? parseInt(xpMessage, 10) : null;
    const overlay = document.getElementById('resultOverlay');
    const card = document.getElementById('resultCard');
    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    const sub = document.getElementById('resultSub');
    const xpBadge = document.getElementById('resultXpBadge');
    const xpText = document.getElementById('resultXpText');

    card.className = 'result-card ' + outcome;

    const diffLabel = difficulty === 'easy' ? I18N.diffEasy : difficulty === 'medium' ? I18N.diffMedium : I18N.diffHard;
    if (outcome === 'win') {
        icon.innerHTML = '<i class="fas fa-trophy"></i>';
        title.textContent = I18N.modalWinTitle;
        sub.textContent = I18N.modalWinSubTemplate.replace('%s', diffLabel);
    } else if (outcome === 'lose') {
        icon.innerHTML = '<i class="fas fa-face-frown"></i>';
        title.textContent = I18N.modalLoseTitle;
        sub.textContent = I18N.modalLoseSub;
    } else {
        icon.innerHTML = '<i class="fas fa-handshake"></i>';
        title.textContent = I18N.modalDrawTitle;
        sub.textContent = I18N.modalDrawSub;
    }

    if (xpMessage) {
        xpText.textContent = xpMessage;
        xpBadge.style.display = 'inline-block';
    } else {
        xpBadge.style.display = 'none';
    }

    overlay.classList.add('show');
}
function closeResultModal() {
    document.getElementById('resultOverlay').classList.remove('show');
}
function playAgainSameSettings() {
    closeResultModal();
    startAiGame(difficulty);
}
function goToDashboard() {
    let url = {!! json_encode(route('student.dashboard')) !!};
    if (lastXpAmount) {
        url += '?xp_gained=' + encodeURIComponent(lastXpAmount) + '&xp_game=' + encodeURIComponent('Chess');
    }
    window.location.href = url;
}

// ---------------- Win celebration (same confetti effect as the XP shop) ----------------
function playCelebration(colorsStr) {
    const colors = colorsStr.split(',');
    const canvas = document.getElementById('confettiCanvas');
    canvas.style.display = 'block';
    const ctx = canvas.getContext('2d');
    function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
    resize();

    const pieces = [];
    for (let i = 0; i < 140; i++) {
        pieces.push({
            x: Math.random() * canvas.width,
            y: Math.random() * -canvas.height,
            size: 6 + Math.random() * 6,
            color: colors[Math.floor(Math.random() * colors.length)],
            speedY: 2 + Math.random() * 3,
            speedX: (Math.random() - 0.5) * 2,
            rotation: Math.random() * 360,
            rotSpeed: (Math.random() - 0.5) * 10
        });
    }

    let frame = 0;
    function draw() {
        frame++;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces.forEach(function (p) {
            p.y += p.speedY; p.x += p.speedX; p.rotation += p.rotSpeed;
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rotation * Math.PI / 180);
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
            ctx.restore();
        });
        if (frame < 240) requestAnimationFrame(draw);
        else canvas.style.display = 'none';
    }
    draw();
}

function resignGame() {
    if (gameOver) return;
    if (!confirm(I18N.confirmResign)) return;
    gameOver = true;
    clearInterval(pollTimer);

    const winnerRole = myColor === 'w' ? 'player2' : 'player1';
    if (mode === 'ai') {
        if (!matchId) {
            apiPost('create', { game_type: 'chess', mode: 'ai', ai_difficulty: difficulty }).then(res => {
                matchId = res.match.match_id;
                apiPost('finish', { match_id: matchId, winner: winnerRole });
            });
        } else {
            apiPost('finish', { match_id: matchId, winner: winnerRole });
        }
    } else if (mode === 'pvp') {
        apiPost('resign', { match_id: matchId });
    }
    reportResultUi(winnerRole, null);
}

// ---------------- PvP polling ----------------
function pollPvpState() {
    if (gameOver) return;
    apiGet('state', { match_id: matchId }).then(res => {
        if (!res.match) return;
        const m = res.match;
        if (m.status === 'finished') {
            clearInterval(pollTimer);
            gameOver = true;
            game.load(m.board_state);
            renderBoard();
            let winnerRole = m.winner;
            reportResultUi(winnerRole, null);
            return;
        }
        if (m.board_state !== game.fen()) {
            game.load(m.board_state);
            selectedSquare = null;
            renderBoard();
            updateStatus();
        }
        if (!document.getElementById('opponentLabel').dataset.set && (m.player2_name || m.player1_name)) {
            const oppName = myColor === 'w' ? m.player2_name : m.player1_name;
            if (oppName) {
                document.getElementById('opponentLabel').textContent = I18N.opponentPrefix + oppName;
                document.getElementById('opponentLabel').dataset.set = '1';
            }
        }
    });
}

// ---------------- Simple minimax AI ----------------
const PIECE_VALUE = { p: 1, n: 3, b: 3, r: 5, q: 9, k: 0 };

function evaluateBoard(g) {
    const board = g.board();
    let total = 0;
    for (let r = 0; r < 8; r++) {
        for (let c = 0; c < 8; c++) {
            const sq = board[r][c];
            if (sq) {
                const val = PIECE_VALUE[sq.type];
                total += sq.color === 'w' ? val : -val;
            }
        }
    }
    return total;
}

function minimax(g, depth, alpha, beta, maximizing) {
    if (depth === 0 || g.game_over()) return evaluateBoard(g);
    const moves = g.moves();
    if (maximizing) {
        let best = -Infinity;
        for (const m of moves) {
            g.move(m);
            const val = minimax(g, depth - 1, alpha, beta, false);
            g.undo();
            if (val > best) best = val;
            if (val > alpha) alpha = val;
            if (beta <= alpha) break;
        }
        return best;
    } else {
        let best = Infinity;
        for (const m of moves) {
            g.move(m);
            const val = minimax(g, depth - 1, alpha, beta, true);
            g.undo();
            if (val < best) best = val;
            if (val < beta) beta = val;
            if (beta <= alpha) break;
        }
        return best;
    }
}

function getAiMove(g, diff) {
    const moves = g.moves();
    if (moves.length === 0) return null;

    if (diff === 'easy') {
        const captures = moves.filter(m => m.indexOf('x') !== -1);
        if (captures.length > 0 && Math.random() < 0.5) return captures[Math.floor(Math.random() * captures.length)];
        return moves[Math.floor(Math.random() * moves.length)];
    }

    const depth = diff === 'medium' ? 2 : 3;
    const maximizing = g.turn() === 'w';
    let bestMove = null;
    let bestEval = maximizing ? -Infinity : Infinity;
    const shuffled = moves.slice().sort(() => Math.random() - 0.5);
    for (const m of shuffled) {
        g.move(m);
        const val = minimax(g, depth - 1, -Infinity, Infinity, !maximizing);
        g.undo();
        if (maximizing ? val > bestEval : val < bestEval) {
            bestEval = val;
            bestMove = m;
        }
    }
    return bestMove || moves[0];
}
</script>
@endsection
