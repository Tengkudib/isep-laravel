const GE = (function () {
    const CFG = window.GAME_CFG;
    const T = CFG.i18n;
    const $ = (id) => document.getElementById(id);

    let state = null;
    let mode = null;
    let difficulty = null;
    let matchId = null;
    let myRole = 'player1';
    let gameOver = false;
    let busy = false;
    let pollTimer = null;
    let waitTimer = null;
    let lobbyTimer = null;
    let lastXp = null;

    const API = CFG.api || 'game_actions.php';
    const HEADERS = { Accept: 'application/json' };

    function apiPost(action, params) {
        const body = new URLSearchParams({ action, [CFG.tokenField || 'csrf_token']: CFG.csrf, ...params });
        return fetch(API, { method: 'POST', body, headers: HEADERS }).then((r) => r.json());
    }
    function apiGet(action, params) {
        const qs = new URLSearchParams({ action, ...(params || {}) });
        return fetch(API + (API.includes('?') ? '&' : '?') + qs.toString(), { headers: HEADERS }).then((r) => r.json());
    }
    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
    function diffLabel(d) {
        return d === 'easy' ? T.diffEasy : d === 'medium' ? T.diffMedium : T.diffHard;
    }

    function show(id, on) { $(id).style.display = on ? (id === 'modeChoice' ? 'flex' : 'block') : 'none'; }

    function showAiDifficulty() { show('modeChoice', false); show('aiDifficultyPanel', true); }
    function backToModeChoice() {
        show('modeChoice', true);
        ['aiDifficultyPanel', 'pvpLobbyPanel', 'waitingPanel'].forEach((id) => show(id, false));
        clearInterval(lobbyTimer);
    }
    function showPvpLobby() {
        show('modeChoice', false);
        show('pvpLobbyPanel', true);
        refreshLobby();
        clearInterval(lobbyTimer);
        lobbyTimer = setInterval(() => {
            if ($('pvpLobbyPanel').style.display === 'none') { clearInterval(lobbyTimer); return; }
            refreshLobby();
        }, 3000);
    }
    function refreshLobby() {
        apiGet('lobby', { game_type: CFG.type }).then((res) => {
            const list = $('lobbyList');
            if (!res.open_matches || res.open_matches.length === 0) {
                list.innerHTML = '<p class="text-muted small text-center py-3">' + T.lobbyEmpty + '</p>';
                return;
            }
            list.innerHTML = res.open_matches.map((m) =>
                `<div class="lobby-row"><span><i class="fas fa-user me-2"></i>${escapeHtml(m.creator_name)}</span>
                 <button class="btn btn-sm btn-primary" onclick="GE.joinPvp(${m.id})">${T.joinBtn}</button></div>`
            ).join('');
        });
    }

    function enterGamePanel() {
        show('selectionPanel', false);
        show('gamePanel', true);
        $('resignBtn').style.display = 'inline-block';
    }
    function backToSelection() {
        clearInterval(pollTimer); clearInterval(waitTimer);
        show('gamePanel', false);
        show('selectionPanel', true);
        backToModeChoice();
    }

    function isMyTurn() {
        return !gameOver && !busy && GAME.turn(state) === myRole;
    }

    function render() {
        GAME.render($('gameBoard'), state, {
            myRole, mode, difficulty,
            myTurn: isMyTurn(),
            play,
            setBusy: (b) => { busy = b; updateTurnPill(); },
            setStatus: (html) => { $('gameStatus').innerHTML = html; },
        });
        updateTurnPill();
    }

    function updateTurnPill() {
        const pill = $('turnPill');
        if (gameOver) {
            pill.textContent = T.gameOver; pill.className = 'turn-pill bg-dark text-white';
        } else if (GAME.turn(state) === myRole) {
            pill.textContent = T.yourTurn; pill.className = 'turn-pill bg-primary text-white';
        } else if (mode === 'ai') {
            pill.textContent = busy ? T.computerThinking : T.computerTurn; pill.className = 'turn-pill bg-secondary text-white';
        } else {
            pill.textContent = T.opponentTurn; pill.className = 'turn-pill bg-secondary text-white';
        }
    }

    function play(newState) {
        if (gameOver) return;
        state = newState;
        render();
        if (mode === 'pvp') {
            apiPost('move', { match_id: matchId, board_state: GAME.serialize(state), next_turn: GAME.turn(state) }).then((res) => {
                if (res.error) alert(res.error);
                checkEnd();
            });
            return;
        }
        if (checkEnd()) return;
        if (GAME.turn(state) !== myRole) aiTurn();
    }

    function aiTurn() {
        busy = true;
        updateTurnPill();
        setTimeout(() => {
            Promise.resolve(GAME.aiMove(state, difficulty, {
                render: (s) => { state = s; render(); },
                setStatus: (html) => { $('gameStatus').innerHTML = html; },
            })).then((next) => {
                busy = false;
                state = next;
                render();
                if (checkEnd()) return;
                if (GAME.turn(state) !== myRole) aiTurn();
            });
        }, 450);
    }

    function startAi(diff) {
        mode = 'ai'; difficulty = diff; matchId = null; myRole = 'player1'; gameOver = false; busy = false;
        state = GAME.initial();
        $('opponentLabel').innerHTML = `${T.opponentComputer} <span class="badge bg-secondary">${diffLabel(diff)}</span>`;
        $('gameStatus').innerHTML = '';
        enterGamePanel();
        render();
        apiPost('create', { game_type: CFG.type, mode: 'ai', ai_difficulty: diff }).then((res) => {
            if (res.match && !gameOver) matchId = res.match.match_id;
        });
    }

    function createPvp() {
        apiPost('create', { game_type: CFG.type, mode: 'pvp' }).then((res) => {
            if (res.error) { alert(res.error); return; }
            matchId = res.match.match_id;
            show('pvpLobbyPanel', false);
            show('waitingPanel', true);
            clearInterval(waitTimer);
            waitTimer = setInterval(() => {
                apiGet('state', { match_id: matchId }).then((r) => {
                    if (r.match && r.match.status === 'ongoing') {
                        clearInterval(waitTimer);
                        myRole = 'player1';
                        loadPvp(r.match);
                    }
                });
            }, 2500);
        });
    }
    function cancelWaiting() {
        clearInterval(waitTimer);
        apiPost('cancel', { match_id: matchId }).then(() => {
            matchId = null;
            show('waitingPanel', false);
            show('pvpLobbyPanel', true);
            refreshLobby();
        });
    }
    function joinPvp(id) {
        apiPost('join', { match_id: id }).then((res) => {
            if (res.error) { alert(res.error); refreshLobby(); return; }
            matchId = id;
            myRole = 'player2';
            loadPvp(res.match);
        });
    }
    function loadPvp(m) {
        mode = 'pvp'; gameOver = false; busy = false;
        clearInterval(lobbyTimer);
        state = GAME.parse(m.board_state);
        const opp = myRole === 'player1' ? m.player2_name : m.player1_name;
        $('opponentLabel').textContent = T.opponentPrefix + (opp || T.waiting);
        $('gameStatus').innerHTML = '';
        enterGamePanel();
        render();
        clearInterval(pollTimer);
        pollTimer = setInterval(pollPvp, 2000);
    }
    function pollPvp() {
        if (gameOver || busy) return;
        apiGet('state', { match_id: matchId }).then((res) => {
            if (!res.match || gameOver || busy) return;
            const m = res.match;
            if (m.board_state !== GAME.serialize(state)) {
                state = GAME.parse(m.board_state);
                render();
            }
            if (m.status === 'finished') {
                clearInterval(pollTimer);
                gameOver = true;
                updateTurnPill();
                reportResult(m.winner || GAME.winner(state) || 'draw', null, null);
            }
        });
    }

    function checkEnd() {
        const winner = GAME.winner(state);
        if (!winner) return false;
        if (gameOver) return true;
        gameOver = true;
        render();
        if (mode === 'ai') {
            const finish = () => apiPost('finish', { match_id: matchId, winner }).then((res) => reportResult(winner, res.xp_message, res.celebration_colors));
            if (matchId) finish();
            else apiPost('create', { game_type: CFG.type, mode: 'ai', ai_difficulty: difficulty }).then((res) => { matchId = res.match.match_id; finish(); });
        } else {
            clearInterval(pollTimer);
            apiPost('finish', { match_id: matchId, winner }).then(() => reportResult(winner, null, null));
        }
        return true;
    }

    function resign() {
        if (gameOver) return;
        if (!confirm(T.confirmResign)) return;
        gameOver = true;
        clearInterval(pollTimer);
        const winner = myRole === 'player1' ? 'player2' : 'player1';
        if (mode === 'ai') {
            if (matchId) apiPost('finish', { match_id: matchId, winner });
        } else {
            apiPost('resign', { match_id: matchId });
        }
        updateTurnPill();
        reportResult(winner, null, null);
    }

    function reportResult(winner, xpMessage, celebration) {
        const iWon = winner === myRole;
        const outcome = winner === 'draw' ? 'draw' : (iWon ? 'win' : 'lose');
        let msg = outcome === 'draw' ? T.resultDraw : (iWon ? T.resultWin : T.resultLose);
        if (xpMessage) msg += ' ' + xpMessage;
        $('gameStatus').innerHTML = '<strong>' + msg + '</strong>';
        showResultModal(outcome, xpMessage);
        if (outcome === 'win' && celebration) playCelebration(celebration);
    }

    function showResultModal(outcome, xpMessage) {
        lastXp = xpMessage ? parseInt(xpMessage, 10) || null : null;
        $('resultCard').className = 'result-card ' + outcome;
        const icon = { win: 'fa-trophy', lose: 'fa-face-frown', draw: 'fa-handshake' }[outcome];
        $('resultIcon').innerHTML = `<i class="fas ${icon}"></i>`;
        $('resultTitle').textContent = outcome === 'win' ? T.modalWinTitle : outcome === 'lose' ? T.modalLoseTitle : T.modalDrawTitle;
        $('resultSub').textContent = outcome === 'win'
            ? (mode === 'ai' ? T.modalWinSub.replace('%s', diffLabel(difficulty)) : T.modalWinSubPvp)
            : outcome === 'lose' ? T.modalLoseSub : T.modalDrawSub;
        if (xpMessage) { $('resultXpText').textContent = xpMessage; $('resultXpBadge').style.display = 'inline-block'; }
        else $('resultXpBadge').style.display = 'none';
        $('resultOverlay').classList.add('show');
    }
    function playAgain() {
        $('resultOverlay').classList.remove('show');
        if (mode === 'ai') startAi(difficulty);
        else backToSelection();
    }
    function goToDashboard() {
        let url = CFG.dashboard || 'dashboard.php';
        if (lastXp) url += (url.includes('?') ? '&' : '?') + 'xp_gained=' + encodeURIComponent(lastXp) + '&xp_game=' + encodeURIComponent(CFG.title);
        window.location.href = url;
    }

    function playCelebration(colorsStr) {
        const colors = colorsStr.split(',');
        const canvas = $('confettiCanvas');
        canvas.style.display = 'block';
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        const pieces = Array.from({ length: 140 }, () => ({
            x: Math.random() * canvas.width, y: Math.random() * -canvas.height, size: 6 + Math.random() * 6,
            color: colors[Math.floor(Math.random() * colors.length)], speedY: 2 + Math.random() * 3,
            speedX: (Math.random() - 0.5) * 2, rotation: Math.random() * 360, rotSpeed: (Math.random() - 0.5) * 10,
        }));
        let frame = 0;
        (function draw() {
            frame++;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pieces.forEach((p) => {
                p.y += p.speedY; p.x += p.speedX; p.rotation += p.rotSpeed;
                ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rotation * Math.PI / 180);
                ctx.fillStyle = p.color; ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size); ctx.restore();
            });
            if (frame < 240) requestAnimationFrame(draw); else canvas.style.display = 'none';
        })();
    }

    function askQuestion(reason) {
        return apiGet('question').then((res) => new Promise((resolve) => {
            if (!res.question) { resolve(false); return; }
            const q = res.question;
            $('questionReason').textContent = reason || '';
            $('questionText').textContent = q.text;
            $('questionFeedback').textContent = '';
            const box = $('questionOptions');
            box.innerHTML = '';
            q.options.forEach((opt) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'btn btn-outline-primary question-option';
                b.textContent = opt;
                b.onclick = () => {
                    const ok = opt === q.answer;
                    box.querySelectorAll('button').forEach((x) => {
                        x.disabled = true;
                        if (x.textContent === q.answer) x.classList.add('correct');
                    });
                    if (!ok) b.classList.add('wrong');
                    $('questionFeedback').textContent = ok ? '✅ ' + T.questionCorrect : '❌ ' + T.questionWrong + q.answer;
                    setTimeout(() => { $('questionOverlay').classList.remove('show'); resolve(ok); }, ok ? 900 : 1800);
                };
                box.appendChild(b);
            });
            $('questionOverlay').classList.add('show');
            setTimeout(() => { const f = box.querySelector('button'); if (f) f.focus(); }, 100);
        }));
    }

    function init() {
        document.querySelectorAll('.mode-btn').forEach((el) => el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); el.click(); }
        }));
    }

    return {
        init, showAiDifficulty, backToModeChoice, showPvpLobby, refreshLobby, startAi, createPvp, cancelWaiting,
        joinPvp, backToSelection, resign, playAgain, goToDashboard, askQuestion, T,
    };
})();
