const GAME = (function () {
    const LADDERS = { 4: 14, 9: 31, 21: 42, 28: 84, 36: 44, 51: 67, 71: 91, 80: 99 };
    const SNAKES = { 17: 7, 47: 26, 54: 34, 62: 19, 64: 60, 87: 24, 93: 73, 95: 75, 98: 78 };
    const AI_ACCURACY = { easy: 0.35, medium: 0.55, hard: 0.75 };
    const DICE = ['', '⚀', '⚁', '⚂', '⚃', '⚄', '⚅'];
    const T = () => GE.T;

    function initial() { return { pos: [0, 0], turn: 'player1', roll: 0, event: '' }; }
    function parse(s) {
        try {
            const o = JSON.parse(s);
            return { pos: o.pos || [0, 0], turn: o.turn || 'player1', roll: o.roll || 0, event: o.event || '' };
        } catch (e) { return initial(); }
    }
    const serialize = (st) => JSON.stringify(st);
    const turn = (st) => st.turn;
    function winner(st) {
        if (st.pos[0] === 100) return 'player1';
        if (st.pos[1] === 100) return 'player2';
        return null;
    }

    function center(n) {
        const rb = Math.floor((n - 1) / 10);
        const idx = (n - 1) % 10;
        const col = rb % 2 === 0 ? idx : 9 - idx;
        return { x: col * 10 + 5, y: (9 - rb) * 10 + 5 };
    }
    function cellNumber(r, c) {
        const rb = 9 - r;
        return rb * 10 + (rb % 2 === 0 ? c + 1 : 10 - c);
    }

    function boardSvg() {
        let svg = '<svg class="sl-overlay" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true"><defs>'
            + '<linearGradient id="slSnake" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#16a34a"/><stop offset="1" stop-color="#65a30d"/></linearGradient></defs>';
        Object.entries(LADDERS).forEach(([from, to]) => {
            const a = center(+from), b = center(+to);
            const dx = b.x - a.x, dy = b.y - a.y, len = Math.hypot(dx, dy);
            const ox = (-dy / len) * 1.6, oy = (dx / len) * 1.6;
            svg += `<g class="sl-ladder"><line x1="${a.x + ox}" y1="${a.y + oy}" x2="${b.x + ox}" y2="${b.y + oy}"/><line x1="${a.x - ox}" y1="${a.y - oy}" x2="${b.x - ox}" y2="${b.y - oy}"/>`;
            const rungs = Math.max(2, Math.round(len / 4));
            for (let i = 1; i < rungs; i++) {
                const t = i / rungs, x = a.x + dx * t, y = a.y + dy * t;
                svg += `<line class="rung" x1="${x + ox}" y1="${y + oy}" x2="${x - ox}" y2="${y - oy}"/>`;
            }
            svg += '</g>';
        });
        Object.entries(SNAKES).forEach(([from, to]) => {
            const h = center(+from), t = center(+to);
            const mx = (h.x + t.x) / 2, my = (h.y + t.y) / 2;
            const bend = (h.x < t.x ? 9 : -9);
            svg += `<path class="sl-snake" d="M${h.x},${h.y} Q${mx + bend},${my - 6} ${mx},${my} T${t.x},${t.y}"/>`
                + `<circle class="sl-snake-head" cx="${h.x}" cy="${h.y}" r="2.2"/><circle cx="${h.x - 0.7}" cy="${h.y - 0.6}" r="0.45" fill="#fff"/><circle cx="${h.x + 0.7}" cy="${h.y - 0.6}" r="0.45" fill="#fff"/>`;
        });
        return svg + '</svg>';
    }

    function tokenStyle(n, idx) {
        if (n === 0) return `left:${idx === 0 ? -4 : -4}%; top:${idx === 0 ? 89 : 97}%;`;
        const c = center(n);
        return `left:${c.x + (idx === 0 ? -1.8 : 1.8)}%; top:${c.y + (idx === 0 ? -1.5 : 1.5)}%;`;
    }

    function render(el, st, api) {
        let cells = '';
        for (let r = 0; r < 10; r++) for (let c = 0; c < 10; c++) {
            const n = cellNumber(r, c);
            const cls = LADDERS[n] ? ' ladder' : SNAKES[n] ? ' snake' : (n === 100 ? ' goal' : '');
            cells += `<div class="sl-cell${cls}${(r + c) % 2 ? ' alt' : ''}"><span>${n === 100 ? '🏁' : n}</span></div>`;
        }
        const myIdx = api.myRole === 'player1' ? 0 : 1;
        const names = api.mode === 'ai' ? [T().you || 'Anda', T().computer || 'Komputer'] : (api.myRole === 'player1' ? [T().you || 'Anda', T().opponent || 'Lawan'] : [T().opponent || 'Lawan', T().you || 'Anda']);
        el.innerHTML = `
            <div class="sl-layout">
                <div class="sl-board-wrap"><div class="sl-board">${cells}</div>${boardSvg()}
                    <div class="sl-tokens">
                        <div class="sl-token p1" style="${tokenStyle(st.pos[0], 0)}" title="${names[0]}"></div>
                        <div class="sl-token p2" style="${tokenStyle(st.pos[1], 1)}" title="${names[1]}"></div>
                    </div>
                </div>
                <div class="sl-side">
                    <div class="sl-player p1${st.turn === 'player1' ? ' active' : ''}"><span class="sl-dot p1"></span>${names[0]}<b>${st.pos[0] || T().start || 'Mula'}</b></div>
                    <div class="sl-player p2${st.turn === 'player2' ? ' active' : ''}"><span class="sl-dot p2"></span>${names[1]}<b>${st.pos[1] || T().start || 'Mula'}</b></div>
                    <div class="sl-dice" id="slDice" aria-live="polite">${st.roll ? DICE[st.roll] : '🎲'}</div>
                    <button type="button" class="btn btn-primary w-100" id="slRoll" ${api.myTurn ? '' : 'disabled'}><i class="fas fa-dice me-1"></i>${T().roll || 'Baling Dadu'}</button>
                    <div class="sl-event">${st.event || ''}</div>
                    <div class="sl-legend small text-muted"><span>🪜 ${T().ladderHint || ''}</span><span>🐍 ${T().snakeHint || ''}</span></div>
                </div>
            </div>`;
        api.setStatus('');
        const btn = el.querySelector('#slRoll');
        btn.onclick = () => {
            btn.disabled = true;
            api.setBusy(true);
            takeTurn(st, myIdx, (s) => GE_render(el, s, api), (q) => GE.askQuestion(q)).then((next) => {
                api.setBusy(false);
                api.play(next);
            });
        };
    }

    let GE_render = (el, s, api) => render(el, s, Object.assign({}, api, { myTurn: false }));

    function rollDice(dieEl) {
        return new Promise((resolve) => {
            const value = 1 + Math.floor(Math.random() * 6);
            let ticks = 0;
            const iv = setInterval(() => {
                if (dieEl) dieEl.textContent = DICE[1 + Math.floor(Math.random() * 6)];
                if (++ticks >= 8) { clearInterval(iv); if (dieEl) dieEl.textContent = DICE[value]; resolve(value); }
            }, 70);
        });
    }

    function takeTurn(st, idx, show, ask) {
        const who = idx === 0 ? 'player1' : 'player2';
        return rollDice(document.getElementById('slDice')).then((roll) => {
            const s = { pos: st.pos.slice(), turn: st.turn, roll, event: '' };
            let target = s.pos[idx] + roll;
            let note = (T().rolled || 'Dadu: %d').replace('%d', roll);
            if (target > 100) { target = 100 - (target - 100); note += ' · ' + (T().bounce || 'melantun'); }
            s.pos[idx] = target;
            show(s);
            const finish = (extra) => {
                s.event = note + (extra ? ' · ' + extra : '');
                s.turn = winner(s) ? who : (who === 'player1' ? 'player2' : 'player1');
                return new Promise((r) => setTimeout(() => r(s), 350));
            };
            if (LADDERS[target]) {
                return new Promise((r) => setTimeout(r, 400)).then(() => ask((T().ladderQ || 'Tangga ke %d!').replace('%d', LADDERS[target]))).then((ok) => {
                    if (ok) { s.pos[idx] = LADDERS[target]; return finish('🪜 ' + (T().climbed || 'naik tangga ke %d').replace('%d', LADDERS[target])); }
                    return finish('🪜 ' + (T().missedLadder || 'jawapan salah, tidak naik'));
                });
            }
            if (SNAKES[target]) {
                return new Promise((r) => setTimeout(r, 400)).then(() => ask((T().snakeQ || 'Ular! Jawab untuk elak turun ke %d').replace('%d', SNAKES[target]))).then((ok) => {
                    if (ok) return finish('🐍 ' + (T().dodged || 'berjaya elak ular'));
                    s.pos[idx] = SNAKES[target];
                    return finish('🐍 ' + (T().bitten || 'digigit ular, turun ke %d').replace('%d', SNAKES[target]));
                });
            }
            return finish('');
        });
    }

    function aiMove(st, diff, helpers) {
        const idx = st.turn === 'player1' ? 0 : 1;
        const acc = AI_ACCURACY[diff] || 0.5;
        const fakeAsk = () => new Promise((r) => setTimeout(() => r(Math.random() < acc), 700));
        return takeTurn(st, idx, (s) => helpers.render(s), fakeAsk).then((s) => {
            if (s.event) s.event = (T().computer || 'Komputer') + ': ' + s.event;
            return s;
        });
    }

    return { initial, parse, serialize, turn, winner, render, aiMove };
})();
