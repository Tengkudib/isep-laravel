@extends('layouts.student')

@section('title')Dam Haji - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { background: #FAF7F0; font-family: 'Segoe UI', sans-serif; }
        .topbar {
            background: linear-gradient(135deg, #13315C 0%, #0B2545 55%, #D4AF37 100%);
            color: white; padding: 24px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
        }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-md); }
        .mode-btn { border-radius: var(--isep-r-xl); padding: 28px 20px; text-align: center; cursor: pointer; transition: all var(--isep-duration) var(--isep-ease); border: 2px solid transparent; background: var(--isep-card-bg); }
        .mode-btn:hover { border-color: var(--isep-primary); transform: translateY(-3px); }
        .mode-btn i { font-size: 2rem; margin-bottom: 10px; color: var(--isep-secondary); }
        .diff-btn { border-radius: var(--isep-r-lg); padding: 14px; font-weight: 700; }
        .lobby-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: var(--isep-r-lg); background: var(--isep-bg); margin-bottom: 8px; }

        .board-wrap { display: flex; justify-content: center; }
        .dam-board {
            display: grid; grid-template-columns: repeat(8, minmax(34px, 62px)); grid-template-rows: repeat(8, minmax(34px, 62px));
            border: 4px solid #4a3f6b; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .sq { display: flex; align-items: center; justify-content: center; position: relative; }
        .sq.light { background: #EDEBF5; }
        .sq.dark { background: #0B2545; cursor: pointer; }
        .sq.selected { outline: 4px solid #D4AF37; outline-offset: -4px; }
        .sq.legal::after { content: ''; position: absolute; width: 26%; height: 26%; border-radius: 50%; background: rgba(40,167,69,0.6); }
        .sq.legal-capture::after { content: ''; position: absolute; width: 30%; height: 30%; border-radius: 50%; background: rgba(255,107,107,0.75); }
        .piece {
            width: 76%; height: 76%; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: clamp(0.8rem, 2vw, 1.1rem); box-shadow: 0 3px 6px rgba(0,0,0,0.35), inset 0 2px 4px rgba(255,255,255,0.25);
        }
        .piece.p1 { background: linear-gradient(160deg, #fff8ec, #e8dcc0); color: #0B2545; border: 2px solid #cbb98f; }
        .piece.p2 { background: linear-gradient(160deg, #3b3f4c, #14161c); color: #D4AF37; border: 2px solid #000; }
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
    <h4 class="fw-bold mt-1 mb-0"><i class="fas fa-circle me-2"></i>Dam Haji</h4>
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
            <div id="damBoard" class="dam-board"></div>
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
    yourTurn: {!! json_encode(t('Giliran anda', 'Your turn')) !!},
    computerTurn: {!! json_encode(t('Giliran Komputer', "Computer's turn")) !!},
    opponentTurn: {!! json_encode(t('Giliran lawan', "Opponent's turn")) !!},
    piecesStatusTemplate: {!! json_encode(t('Buah anda: %1 | Buah lawan: %2', 'Your pieces: %1 | Opponent pieces: %2')) !!},
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

// ---------------- Dam Haji engine ----------------
// Board: flat 64-array, row-major (row 0..7, col 0..7). 0=empty, 1=P1 man, 2=P1 king, 3=P2 man, 4=P2 king.
// Player1 (white, always the human) starts at rows 5-7 (bottom), moves/captures toward row 0.
// Player2 (black, computer or PvP opponent) starts at rows 0-2 (top), moves/captures toward row 7.
// Men may only move/capture in their forward direction; only a promoted King can move/capture backward too.
const DIAGONALS = [[-1, -1], [-1, 1], [1, -1], [1, 1]];

function initialBoard() {
    const b = new Array(64).fill(0);
    for (let r = 0; r < 8; r++) {
        for (let c = 0; c < 8; c++) {
            if ((r + c) % 2 === 1) {
                if (r <= 2) b[r * 8 + c] = 3;      // player2 (black) at top
                else if (r >= 5) b[r * 8 + c] = 1; // player1 (white) at bottom
            }
        }
    }
    return b;
}
function idx(r, c) { return r * 8 + c; }
function inBounds(r, c) { return r >= 0 && r < 8 && c >= 0 && c < 8; }
function isP1(v) { return v === 1 || v === 2; }
function isP2(v) { return v === 3 || v === 4; }
function isKing(v) { return v === 2 || v === 4; }

function getPieceMoves(board, r, c) {
    const val = board[idx(r, c)];
    if (val === 0) return { moves: [], captures: [] };
    const mine1 = isP1(val);
    const king = isKing(val);
    const forwardDirs = mine1 ? [[-1, -1], [-1, 1]] : [[1, -1], [1, 1]];
    const moveDirs = king ? DIAGONALS : forwardDirs;
    const captureDirs = king ? DIAGONALS : forwardDirs; // men cannot move/capture backward until promoted
    const moves = [], captures = [];

    for (const [dr, dc] of moveDirs) {
        const nr = r + dr, nc = c + dc;
        if (inBounds(nr, nc) && board[idx(nr, nc)] === 0) moves.push({ from: [r, c], to: [nr, nc] });
    }
    for (const [dr, dc] of captureDirs) {
        const mr = r + dr, mc = c + dc;
        const nr = r + 2 * dr, nc = c + 2 * dc;
        if (inBounds(nr, nc) && inBounds(mr, mc)) {
            const midVal = board[idx(mr, mc)];
            const landVal = board[idx(nr, nc)];
            const enemy = mine1 ? isP2(midVal) : isP1(midVal);
            if (enemy && landVal === 0) captures.push({ from: [r, c], to: [nr, nc], captured: [mr, mc] });
        }
    }
    return { moves, captures };
}

function getAllMovesForSide(board, p1Turn) {
    let allCaptures = [], allMoves = [];
    for (let r = 0; r < 8; r++) {
        for (let c = 0; c < 8; c++) {
            const val = board[idx(r, c)];
            if (val === 0) continue;
            if (p1Turn && !isP1(val)) continue;
            if (!p1Turn && !isP2(val)) continue;
            const { moves, captures } = getPieceMoves(board, r, c);
            allMoves.push(...moves);
            allCaptures.push(...captures);
        }
    }
    return allCaptures.length > 0 ? { list: allCaptures, mandatory: true } : { list: allMoves, mandatory: false };
}

function applyMove(board, move) {
    const nb = board.slice();
    let val = nb[idx(move.from[0], move.from[1])];
    nb[idx(move.from[0], move.from[1])] = 0;
    if (move.captured) nb[idx(move.captured[0], move.captured[1])] = 0;
    if (val === 1 && move.to[0] === 0) val = 2; // player1 (white) promotes reaching the top row
    if (val === 3 && move.to[0] === 7) val = 4; // player2 (black) promotes reaching the bottom row
    nb[idx(move.to[0], move.to[1])] = val;
    return nb;
}

function getFullTurnOptions(board, p1Turn) {
    const { list, mandatory } = getAllMovesForSide(board, p1Turn);
    if (!mandatory) return list.map(m => [m]);
    const sequences = [];
    function expand(curBoard, move, chain) {
        const nb = applyMove(curBoard, move);
        const newChain = chain.concat([move]);
        const further = getPieceMoves(nb, move.to[0], move.to[1]).captures;
        if (further.length === 0) sequences.push(newChain);
        else for (const nc of further) expand(nb, nc, newChain);
    }
    for (const cap of list) expand(board, cap, []);
    return sequences;
}

function countPieces(board) {
    let p1 = 0, p2 = 0;
    for (const v of board) { if (isP1(v)) p1++; else if (isP2(v)) p2++; }
    return { p1, p2 };
}

// ---------------- AI (minimax) ----------------
function evalBoard(board) {
    let score = 0;
    for (const v of board) {
        if (v === 1) score += 1; else if (v === 2) score += 1.6;
        else if (v === 3) score -= 1; else if (v === 4) score -= 1.6;
    }
    return score;
}
function minimaxDam(board, depth, alpha, beta, p1Turn) {
    const options = getFullTurnOptions(board, p1Turn);
    if (depth === 0 || options.length === 0) return evalBoard(board);
    if (p1Turn) {
        let best = -Infinity;
        for (const seq of options) {
            let b2 = board;
            for (const mv of seq) b2 = applyMove(b2, mv);
            const val = minimaxDam(b2, depth - 1, alpha, beta, false);
            if (val > best) best = val;
            if (val > alpha) alpha = val;
            if (beta <= alpha) break;
        }
        return best;
    } else {
        let best = Infinity;
        for (const seq of options) {
            let b2 = board;
            for (const mv of seq) b2 = applyMove(b2, mv);
            const val = minimaxDam(b2, depth - 1, alpha, beta, true);
            if (val < best) best = val;
            if (val < beta) beta = val;
            if (beta <= alpha) break;
        }
        return best;
    }
}
function getAiDamMove(board, p1Turn, diff) {
    const options = getFullTurnOptions(board, p1Turn);
    if (options.length === 0) return null;
    if (diff === 'easy') return options[Math.floor(Math.random() * options.length)];
    const depth = diff === 'medium' ? 2 : 4;
    let best = null, bestVal = p1Turn ? -Infinity : Infinity;
    const shuffled = options.slice().sort(() => Math.random() - 0.5);
    for (const seq of shuffled) {
        let b2 = board;
        for (const mv of seq) b2 = applyMove(b2, mv);
        const val = minimaxDam(b2, depth - 1, -Infinity, Infinity, !p1Turn);
        if (p1Turn ? val > bestVal : val < bestVal) { bestVal = val; best = seq; }
    }
    return best || options[0];
}

// ---------------- Game state ----------------
let board = initialBoard();
let mode = null;         // 'ai' | 'pvp'
let difficulty = null;
let matchId = null;
let myIsP1 = true;       // true = I play as player1 (top, moves down)
let p1TurnNow = true;    // whose turn it currently is
let selected = null;     // [r,c] of selected piece
let currentTargets = []; // moves/captures available for selected piece this step
let mandatoryThisTurn = false;
let chainActive = false; // true while mid multi-jump (must continue with the same piece)
let movesSinceCapture = 0; // no-progress counter -> forces a draw so games can't hang forever
const DRAW_AFTER_MOVES_WITHOUT_CAPTURE = 40;
let pollTimer = null, waitTimer = null;
let gameOver = false;

function apiPost(action, params) {
    const body = new URLSearchParams({ action, _token: CSRF_TOKEN, ...params });
    return fetch(GAME_API, { method: 'POST', body, headers: { 'Accept': 'application/json' } }).then(r => r.json());
}
function apiGet(action, params) {
    const qs = new URLSearchParams({ action, ...params });
    return fetch(GAME_API + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } }).then(r => r.json());
}
function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

// ---------------- Panel navigation ----------------
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
    apiGet('lobby', { game_type: 'dam' }).then(res => {
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

// ---------------- Start / join ----------------
function startAiGame(diff) {
    mode = 'ai'; difficulty = diff; matchId = null; myIsP1 = true;
    board = initialBoard(); p1TurnNow = true; gameOver = false; selected = null;
    movesSinceCapture = 0; chainActive = false;
    document.getElementById('opponentLabel').innerHTML = `${I18N.opponentComputer} <span class="badge bg-secondary">${diff === 'easy' ? I18N.diffEasy : diff === 'medium' ? I18N.diffMedium : I18N.diffHard}</span>`;
    document.getElementById('resignBtn').style.display = 'inline-block';
    enterGamePanel();
    renderBoard();
    updateStatus();
}
function createPvpMatch() {
    apiPost('create', { game_type: 'dam', mode: 'pvp' }).then(res => {
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
            mode = 'pvp'; myIsP1 = true;
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
        mode = 'pvp'; myIsP1 = false;
        loadPvpMatch(res.match);
    });
}
function loadPvpMatch(m) {
    board = JSON.parse(m.board_state);
    p1TurnNow = m.turn === 'player1';
    gameOver = false; selected = null;
    movesSinceCapture = 0; chainActive = false;
    const oppName = myIsP1 ? m.player2_name : m.player1_name;
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
}
function backToSelection() {
    clearInterval(pollTimer); clearInterval(waitTimer);
    document.getElementById('gamePanel').style.display = 'none';
    document.getElementById('selectionPanel').style.display = 'block';
    backToModeChoice();
}

// ---------------- Rendering ----------------
function renderBoard() {
    const container = document.getElementById('damBoard');
    let html = '';
    for (let r = 0; r < 8; r++) {
        for (let c = 0; c < 8; c++) {
            const isDark = (r + c) % 2 === 1;
            let cls = 'sq ' + (isDark ? 'dark' : 'light');
            if (selected && selected[0] === r && selected[1] === c) cls += ' selected';
            const target = currentTargets.find(t => t.to[0] === r && t.to[1] === c);
            if (target) cls += target.captured ? ' legal-capture' : ' legal';

            let inner = '';
            const val = board[idx(r, c)];
            if (val !== 0) {
                const side = isP1(val) ? 'p1' : 'p2';
                const kingIcon = isKing(val) ? '<i class="fas fa-crown"></i>' : '';
                inner = `<div class="piece ${side}">${kingIcon}</div>`;
            }
            html += `<div class="${cls}" onclick="onSquareClick(${r},${c})">${inner}</div>`;
        }
    }
    container.innerHTML = html;
}

function myTurnNow() { return (myIsP1 && p1TurnNow) || (!myIsP1 && !p1TurnNow); }

function onSquareClick(r, c) {
    if (gameOver) return;
    if (!myTurnNow()) return;

    const val = board[idx(r, c)];
    const mine = p1TurnNow ? isP1(val) : isP2(val);

    if (selected === null) {
        if (!mine) return;
        const { list, mandatory } = getAllMovesForSide(board, p1TurnNow);
        mandatoryThisTurn = mandatory;
        const pieceOptions = list.filter(m => m.from[0] === r && m.from[1] === c);
        if (pieceOptions.length === 0) return; // this piece has no legal move (mandatory capture elsewhere)
        selected = [r, c];
        currentTargets = pieceOptions;
        renderBoard();
        return;
    }

    if (selected[0] === r && selected[1] === c) {
        if (chainActive) return; // mid multi-jump: cannot cancel, must continue capturing
        selected = null; currentTargets = [];
        renderBoard();
        return;
    }

    const chosen = currentTargets.find(t => t.to[0] === r && t.to[1] === c);
    if (!chosen) {
        if (chainActive) return; // mid multi-jump: only the highlighted continuations are valid clicks
        // try reselect another own piece
        if (mine) {
            const { list, mandatory } = getAllMovesForSide(board, p1TurnNow);
            mandatoryThisTurn = mandatory;
            const pieceOptions = list.filter(m => m.from[0] === r && m.from[1] === c);
            if (pieceOptions.length > 0) {
                selected = [r, c];
                currentTargets = pieceOptions;
                renderBoard();
                return;
            }
        }
        selected = null; currentTargets = [];
        renderBoard();
        return;
    }

    board = applyMove(board, chosen);

    if (chosen.captured) {
        movesSinceCapture = 0;
        const further = getPieceMoves(board, chosen.to[0], chosen.to[1]).captures;
        if (further.length > 0) {
            chainActive = true;
            selected = [chosen.to[0], chosen.to[1]];
            currentTargets = further;
            renderBoard();
            return; // continue chain capture, same turn
        }
    } else {
        movesSinceCapture++;
    }

    chainActive = false;

    // turn ends
    selected = null; currentTargets = [];
    p1TurnNow = !p1TurnNow;
    renderBoard();
    updateStatus();
    afterTurnEnds();
}

function afterTurnEnds() {
    if (checkGameOverAndReport()) return;

    if (mode === 'pvp') {
        const nextTurn = p1TurnNow ? 'player1' : 'player2';
        apiPost('move', { match_id: matchId, board_state: JSON.stringify(board), next_turn: nextTurn }).then(res => {
            if (res.error) alert(res.error);
        });
    } else if (mode === 'ai' && !p1TurnNow) {
        document.getElementById('turnPill').textContent = I18N.computerThinking;
        document.getElementById('turnPill').className = 'turn-pill bg-secondary text-white';
        setTimeout(makeAiTurn, 500);
    }
}

function makeAiTurn() {
    const seq = getAiDamMove(board, p1TurnNow, difficulty);
    if (seq) {
        const hadCapture = seq.some(mv => mv.captured);
        for (const mv of seq) board = applyMove(board, mv);
        movesSinceCapture = hadCapture ? 0 : movesSinceCapture + 1;
    }
    p1TurnNow = !p1TurnNow;
    renderBoard();
    updateStatus();
    checkGameOverAndReport();
}

function updateStatus() {
    const pill = document.getElementById('turnPill');
    const { p1, p2 } = countPieces(board);
    document.getElementById('gameStatus').textContent = I18N.piecesStatusTemplate.replace('%1', myIsP1 ? p1 : p2).replace('%2', myIsP1 ? p2 : p1);
    if (myTurnNow()) { pill.textContent = I18N.yourTurn; pill.className = 'turn-pill bg-primary text-white'; }
    else { pill.textContent = mode === 'ai' ? I18N.computerTurn : I18N.opponentTurn; pill.className = 'turn-pill bg-secondary text-white'; }
}

function checkGameOverAndReport() {
    if (gameOver) return true;
    const { p1, p2 } = countPieces(board);

    let winnerRole = null;
    if (p1 === 0) {
        winnerRole = 'player2';
    } else if (p2 === 0) {
        winnerRole = 'player1';
    } else {
        // Only the side whose turn it currently is can lose by having no legal moves -
        // checking both sides unconditionally (regardless of whose turn it is) was the
        // earlier bug: it could end the game on a purely temporary, irrelevant block.
        const toMoveOptions = getFullTurnOptions(board, p1TurnNow);
        if (toMoveOptions.length === 0) {
            winnerRole = p1TurnNow ? 'player2' : 'player1';
        } else if (movesSinceCapture >= DRAW_AFTER_MOVES_WITHOUT_CAPTURE) {
            // Without this, two pieces (often kings) can shuffle back and forth forever
            // and the game never reaches a conclusion - so "finish" (and XP) never fires.
            winnerRole = 'draw';
        } else {
            return false;
        }
    }

    gameOver = true;
    updateStatus();

    if (mode === 'ai') {
        if (!matchId) {
            apiPost('create', { game_type: 'dam', mode: 'ai', ai_difficulty: difficulty }).then(res => {
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
        apiPost('finish', { match_id: matchId, winner: winnerRole }).then(() => reportResultUi(winnerRole, null));
        clearInterval(pollTimer);
    }
    return true;
}

function reportResultUi(winnerRole, xpMessage, celebrationColors) {
    const iWon = winnerRole === 'draw' ? null : (myIsP1 && winnerRole === 'player1') || (!myIsP1 && winnerRole === 'player2');
    let msg = winnerRole === 'draw' ? I18N.resultDraw : (iWon ? I18N.resultWin : I18N.resultLose);
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
        url += '?xp_gained=' + encodeURIComponent(lastXpAmount) + '&xp_game=' + encodeURIComponent('Dam Haji');
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
    const winnerRole = myIsP1 ? 'player2' : 'player1';
    if (mode === 'ai') {
        if (!matchId) {
            apiPost('create', { game_type: 'dam', mode: 'ai', ai_difficulty: difficulty }).then(res => {
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
            board = JSON.parse(m.board_state);
            selected = null; currentTargets = [];
            renderBoard();
            reportResultUi(m.winner, null);
            return;
        }
        const serverBoardStr = m.board_state;
        if (serverBoardStr !== JSON.stringify(board)) {
            board = JSON.parse(serverBoardStr);
            p1TurnNow = m.turn === 'player1';
            selected = null; currentTargets = [];
            renderBoard();
            updateStatus();
        }
        if (!document.getElementById('opponentLabel').dataset.set) {
            const oppName = myIsP1 ? m.player2_name : m.player1_name;
            if (oppName) {
                document.getElementById('opponentLabel').textContent = I18N.opponentPrefix + oppName;
                document.getElementById('opponentLabel').dataset.set = '1';
            }
        }
    });
}
</script>
@endsection
