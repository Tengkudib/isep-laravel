const GAME = (function () {
    const LINES = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
    const MARK = { player1: 'X', player2: 'O' };

    function winningLine(b) {
        for (const l of LINES) {
            if (b[l[0]] !== '.' && b[l[0]] === b[l[1]] && b[l[1]] === b[l[2]]) return l;
        }
        return null;
    }
    function winner(b) {
        const l = winningLine(b);
        if (l) return b[l[0]] === 'X' ? 'player1' : 'player2';
        return b.includes('.') ? null : 'draw';
    }
    function turn(b) {
        const x = b.split('X').length - 1;
        const o = b.split('O').length - 1;
        return x === o ? 'player1' : 'player2';
    }
    function place(b, i) {
        return b.slice(0, i) + MARK[turn(b)] + b.slice(i + 1);
    }
    function empty(b) {
        const out = [];
        for (let i = 0; i < 9; i++) if (b[i] === '.') out.push(i);
        return out;
    }

    function minimax(b, me) {
        const w = winner(b);
        const left = b.split('.').length - 1;
        if (w === me) return { score: 10 + left };
        if (w === 'draw') return { score: 0 };
        if (w) return { score: -10 - left };
        const moves = empty(b);
        const myMove = turn(b) === me;
        let best = { score: myMove ? -Infinity : Infinity, move: moves[0] };
        for (const m of moves) {
            const r = minimax(place(b, m), me);
            if (myMove ? r.score > best.score : r.score < best.score) best = { score: r.score, move: m };
        }
        return best;
    }
    function findWinningMove(b, role) {
        for (const m of empty(b)) {
            const nb = b.slice(0, m) + MARK[role] + b.slice(m + 1);
            if (winner(nb) === role) return m;
        }
        return null;
    }

    function aiMove(b, diff) {
        const moves = empty(b);
        const me = turn(b);
        const them = me === 'player1' ? 'player2' : 'player1';
        let move;
        if (diff === 'hard') {
            move = minimax(b, me).move;
        } else if (diff === 'medium') {
            move = findWinningMove(b, me);
            if (move === null) move = findWinningMove(b, them);
            if (move === null && b[4] === '.') move = 4;
            if (move === null) move = moves[Math.floor(Math.random() * moves.length)];
        } else {
            move = findWinningMove(b, me);
            if (move === null || Math.random() < 0.5) move = moves[Math.floor(Math.random() * moves.length)];
        }
        return place(b, move);
    }

    function render(el, b, api) {
        const line = winningLine(b) || [];
        el.innerHTML = '<div class="ttt-board" role="grid" aria-label="Tic-Tac-Toe"></div>';
        const grid = el.firstChild;
        for (let i = 0; i < 9; i++) {
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'ttt-cell' + (b[i] === 'X' ? ' x' : b[i] === 'O' ? ' o' : '') + (line.includes(i) ? ' win' : '');
            cell.textContent = b[i] === '.' ? '' : b[i];
            cell.setAttribute('aria-label', (b[i] === '.' ? '' : b[i] + ' ') + (i + 1));
            cell.disabled = !api.myTurn || b[i] !== '.';
            cell.onclick = () => api.play(place(b, i));
            grid.appendChild(cell);
        }
        const mine = MARK[api.myRole];
        api.setStatus(winner(b) ? '' : (GE.T.youAre || '').replace('%s', `<strong class="ttt-tag ${mine.toLowerCase()}">${mine}</strong>`));
    }

    return {
        initial: () => '.........',
        serialize: (b) => b,
        parse: (s) => (typeof s === 'string' && s.length === 9 ? s : '.........'),
        turn, winner, render, aiMove,
    };
})();
