const GAME = (function () {
    const ROWS = 6, COLS = 7;
    const PIECE = { player1: '1', player2: '2' };

    const at = (b, r, c) => b[r * COLS + c];
    function turn(b) {
        const p1 = b.split('1').length - 1;
        const p2 = b.split('2').length - 1;
        return p1 === p2 ? 'player1' : 'player2';
    }
    function dropRow(b, c) {
        for (let r = ROWS - 1; r >= 0; r--) if (at(b, r, c) === '0') return r;
        return -1;
    }
    function drop(b, c, piece) {
        const r = dropRow(b, c);
        if (r < 0) return null;
        const i = r * COLS + c;
        return b.slice(0, i) + (piece || PIECE[turn(b)]) + b.slice(i + 1);
    }
    function validCols(b) {
        const out = [];
        for (let c = 0; c < COLS; c++) if (at(b, 0, c) === '0') out.push(c);
        return out;
    }
    function winningCells(b) {
        const dirs = [[0, 1], [1, 0], [1, 1], [1, -1]];
        for (let r = 0; r < ROWS; r++) for (let c = 0; c < COLS; c++) {
            const p = at(b, r, c);
            if (p === '0') continue;
            for (const [dr, dc] of dirs) {
                const cells = [];
                for (let k = 0; k < 4; k++) {
                    const rr = r + dr * k, cc = c + dc * k;
                    if (rr < 0 || rr >= ROWS || cc < 0 || cc >= COLS || at(b, rr, cc) !== p) break;
                    cells.push(rr * COLS + cc);
                }
                if (cells.length === 4) return cells;
            }
        }
        return null;
    }
    function winner(b) {
        const w = winningCells(b);
        if (w) return b[w[0]] === '1' ? 'player1' : 'player2';
        return validCols(b).length === 0 ? 'draw' : null;
    }

    function scoreWindow(w, me, them) {
        const mine = w.filter((x) => x === me).length;
        const theirs = w.filter((x) => x === them).length;
        const empties = w.filter((x) => x === '0').length;
        if (mine === 4) return 100;
        if (mine === 3 && empties === 1) return 6;
        if (mine === 2 && empties === 2) return 2;
        if (theirs === 3 && empties === 1) return -8;
        return 0;
    }
    function evaluate(b, me) {
        const them = me === '1' ? '2' : '1';
        let score = 0;
        for (let r = 0; r < ROWS; r++) if (at(b, r, 3) === me) score += 3;
        for (let r = 0; r < ROWS; r++) for (let c = 0; c < COLS; c++) {
            [[0, 1], [1, 0], [1, 1], [1, -1]].forEach(([dr, dc]) => {
                const w = [];
                for (let k = 0; k < 4; k++) {
                    const rr = r + dr * k, cc = c + dc * k;
                    if (rr < 0 || rr >= ROWS || cc < 0 || cc >= COLS) return;
                    w.push(at(b, rr, cc));
                }
                score += scoreWindow(w, me, them);
            });
        }
        return score;
    }
    function minimax(b, depth, alpha, beta, maximizing, me) {
        const w = winner(b);
        const them = me === '1' ? '2' : '1';
        if (w === 'draw') return { score: 0 };
        if (w) return { score: (b[winningCells(b)[0]] === me ? 100000 : -100000) * (depth + 1) };
        if (depth === 0) return { score: evaluate(b, me) };
        const cols = validCols(b).sort((a, c) => Math.abs(3 - a) - Math.abs(3 - c));
        let best = { score: maximizing ? -Infinity : Infinity, col: cols[0] };
        for (const c of cols) {
            const r = minimax(drop(b, c, maximizing ? me : them), depth - 1, alpha, beta, !maximizing, me);
            if (maximizing ? r.score > best.score : r.score < best.score) best = { score: r.score, col: c };
            if (maximizing) alpha = Math.max(alpha, r.score); else beta = Math.min(beta, r.score);
            if (beta <= alpha) break;
        }
        return best;
    }
    function immediate(b, piece) {
        for (const c of validCols(b)) {
            const nb = drop(b, c, piece);
            if (winningCells(nb) && nb[winningCells(nb)[0]] === piece) return c;
        }
        return null;
    }

    function aiMove(b, diff) {
        const me = PIECE[turn(b)];
        const them = me === '1' ? '2' : '1';
        const cols = validCols(b);
        let col;
        if (diff === 'hard') {
            col = minimax(b, 5, -Infinity, Infinity, true, me).col;
        } else if (diff === 'medium') {
            col = immediate(b, me);
            if (col === null) col = immediate(b, them);
            if (col === null) {
                const safe = cols.filter((c) => { const nb = drop(b, c, me); return immediate(nb, them) === null; });
                const pool = safe.length ? safe : cols;
                pool.sort((a, c) => Math.abs(3 - a) - Math.abs(3 - c));
                col = Math.random() < 0.6 ? pool[0] : pool[Math.floor(Math.random() * pool.length)];
            }
        } else {
            col = Math.random() < 0.5 ? immediate(b, me) : null;
            if (col === null) col = cols[Math.floor(Math.random() * cols.length)];
        }
        return drop(b, col, me);
    }

    let lastBoard = null;
    function render(el, b, api) {
        const win = winningCells(b) || [];
        let newIndex = -1;
        if (lastBoard && lastBoard.length === b.length) {
            for (let i = 0; i < b.length; i++) if (lastBoard[i] === '0' && b[i] !== '0') newIndex = i;
        }
        lastBoard = b;
        let html = '<div class="c4-wrap"><div class="c4-board" role="grid" aria-label="Connect Four">';
        for (let r = 0; r < ROWS; r++) for (let c = 0; c < COLS; c++) {
            const i = r * COLS + c;
            const p = b[i];
            html += `<div class="c4-cell" data-col="${c}"><div class="c4-disc ${p === '1' ? 'p1' : p === '2' ? 'p2' : ''}${win.includes(i) ? ' win' : ''}${i === newIndex ? ' drop' : ''}" style="--row:${r};"></div></div>`;
        }
        html += '</div><div class="c4-cols">';
        for (let c = 0; c < COLS; c++) {
            const can = api.myTurn && at(b, 0, c) === '0';
            html += `<button type="button" class="c4-col-btn" data-col="${c}" ${can ? '' : 'disabled'} aria-label="${GE.T.column || 'Column'} ${c + 1}"><i class="fas fa-arrow-down"></i></button>`;
        }
        html += '</div></div>';
        el.innerHTML = html;
        el.querySelectorAll('.c4-col-btn').forEach((btn) => btn.onclick = () => api.play(drop(b, +btn.dataset.col)));
        el.querySelectorAll('.c4-cell').forEach((cell) => cell.onclick = () => {
            const c = +cell.dataset.col;
            if (api.myTurn && at(b, 0, c) === '0') api.play(drop(b, c));
        });
        if (!winner(b)) {
            const cls = api.myRole === 'player1' ? 'p1' : 'p2';
            api.setStatus((GE.T.youAre || '').replace('%s', `<span class="c4-dot ${cls}"></span>`));
        }
    }

    return {
        initial: () => '0'.repeat(42),
        serialize: (b) => b,
        parse: (s) => (typeof s === 'string' && s.length === 42 ? s : '0'.repeat(42)),
        turn, winner, render, aiMove,
    };
})();
