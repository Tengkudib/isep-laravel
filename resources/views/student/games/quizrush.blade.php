@php
$i18n = [
    'correct' => t('Betul!', 'Correct!'),
    'wrong' => t('Salah', 'Wrong'),
    'timeUp' => t('Masa tamat!', "Time's up!"),
    'score' => t('Markah', 'Score'),
    'newBest' => t('Rekod peribadi baharu!', 'New personal best!'),
    'yourAnswer' => t('Jawapan anda', 'Your answer'),
    'answer' => t('Jawapan betul', 'Correct answer'),
    'noAnswered' => t('Tiada soalan dijawab.', 'No questions answered.'),
    'error' => t('Ralat. Sila cuba lagi.', 'Something went wrong. Please try again.'),
];
@endphp
@extends('layouts.student')

@section('title'){{ t('Kuiz Koding Pantas', 'Quick Code Quiz') }} - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/game-engine.css') }}?v=1">
<style>
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #0EA5E9 60%, #D4AF37 100%); }
        .qr-stats { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin: 18px 0 24px; }
        .qr-stat { min-width: 120px; padding: 12px 18px; border-radius: 14px; background: rgba(14,165,233,0.08); text-align: center; }
        .qr-stat b { display: block; font-size: 1.5rem; color: var(--isep-primary); }
        .qr-hud { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .qr-timer { font-size: 2rem; font-weight: 900; font-variant-numeric: tabular-nums; color: var(--isep-primary); }
        .qr-timer.low { color: #dc3545; animation: qrBlink 1s steps(2) infinite; }
        @keyframes qrBlink { 50% { opacity: .45; } }
        .qr-bar { height: 8px; border-radius: 99px; background: rgba(107,122,143,0.2); overflow: hidden; margin-bottom: 18px; }
        .qr-bar span { display: block; height: 100%; background: linear-gradient(90deg, #0EA5E9, #D4AF37); transition: width 1s linear; }
        .qr-score { font-weight: 800; font-size: 1.1rem; }
        .qr-question { font-size: 1.15rem; font-weight: 700; min-height: 3.2em; }
        .qr-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .qr-option { text-align: left; border-radius: 14px; padding: 14px 16px; font-weight: 600; border: 2px solid rgba(14,165,233,0.35); background: var(--isep-card-bg, #fff); transition: transform .12s ease, border-color .12s ease; }
        .qr-option:hover:not(:disabled) { transform: translateY(-2px); border-color: #0EA5E9; }
        .qr-option.correct { background: #198754; border-color: #198754; color: #fff; }
        .qr-option.wrong { background: #dc3545; border-color: #dc3545; color: #fff; }
        .qr-flash { height: 1.6em; text-align: center; font-weight: 800; margin-top: 12px; }
        .qr-review { max-height: 360px; overflow: auto; text-align: left; }
        .qr-review-item { padding: 10px 12px; border-radius: 10px; margin-bottom: 8px; background: rgba(107,122,143,0.06); border-left: 4px solid #198754; font-size: .88rem; }
        .qr-review-item.bad { border-left-color: #dc3545; }
        [data-theme="dark"] .qr-stat b, [data-theme="dark"] .qr-timer { color: #F5D061; }
        @media (max-width: 575.98px) { .qr-options { grid-template-columns: 1fr; } }
        @media (prefers-reduced-motion: reduce) { .qr-timer.low { animation: none; } }
    </style>
@endpush

@section('content')
<div class="topbar">
    <a href="{{ route('student.games') }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {!! t('Permainan', 'Games') !!}</a>
    <h4 class="fw-bold mt-1 mb-0"><i class="fas fa-stopwatch me-2"></i>{!! t('Kuiz Koding Pantas', 'Quick Code Quiz') !!}</h4>
    <p class="mb-0 mt-1 opacity-75 small">{!! t('Jawab seberapa banyak soalan koding dalam 60 saat!', 'Answer as many coding questions as you can in 60 seconds!') !!}</p>
</div>

<div class="container-fluid p-4" style="max-width: 760px;">
    <div id="qrStart" class="card-modern p-4 text-center">
        <div style="font-size:3.5rem;">⏱️</div>
        <h5 class="fw-bold">{!! t('Sedia untuk cabaran 60 saat?', 'Ready for the 60-second challenge?') !!}</h5>
        <p class="text-muted small mb-0">{!! t('Soalan diambil daripada kuiz semua kursus iSEP. Setiap jawapan betul = 2 XP (maksimum 30 XP). Satu pusingan sahaja sehari!', 'Questions come from the quizzes of every iSEP course. Each correct answer = 2 XP (max 30 XP). One round per day only!') !!}</p>
        <div class="qr-stats">
            <div class="qr-stat"><b id="qrBest">-</b><small class="text-muted">{!! t('Rekod terbaik', 'Best score') !!}</small></div>
            <div class="qr-stat"><b id="qrRunsLeft">-</b><small class="text-muted">{!! t('Pusingan tinggal hari ini', 'Rounds left today') !!}</small></div>
        </div>
        <p class="text-muted small" id="qrComeBack" style="display:none;"><i class="fas fa-moon me-1"></i>{!! t('Anda sudah bermain hari ini. Datang semula esok untuk pusingan baharu!', 'You have already played today. Come back tomorrow for a new round!') !!}</p>
        <button class="btn btn-primary btn-lg px-5" id="qrStartBtn" onclick="startQuiz()"><i class="fas fa-play me-2"></i>{!! t('Mula', 'Start') !!}</button>
    </div>

    <div id="qrPlay" class="card-modern p-4" style="display:none;">
        <div class="qr-hud">
            <div class="qr-score">✅ <span id="qrScore">0</span></div>
            <div class="qr-timer" id="qrTimer" aria-live="off">60</div>
            <div class="text-muted small">#<span id="qrNum">1</span></div>
        </div>
        <div class="qr-bar"><span id="qrBar" style="width:100%"></span></div>
        <div class="qr-question" id="qrQuestion"></div>
        <div class="qr-options mt-3" id="qrOptions"></div>
        <div class="qr-flash" id="qrFlash" aria-live="polite"></div>
    </div>
</div>

<canvas id="confettiCanvas" style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:999; display:none;"></canvas>

<div class="result-overlay" id="resultOverlay">
    <div class="result-card" id="resultCard" role="dialog" aria-modal="true" aria-labelledby="resultTitle">
        <div class="result-icon" id="resultIcon" style="background:linear-gradient(135deg,#0EA5E9,#D4AF37);"><i class="fas fa-stopwatch"></i></div>
        <div class="result-title" id="resultTitle"></div>
        <div class="result-sub" id="resultSub"></div>
        <div class="result-xp-badge" id="resultXpBadge" style="display:none;"><i class="fas fa-bolt me-1"></i><span id="resultXpText"></span></div>
        <div class="qr-review mb-3" id="qrReview"></div>
        <div class="result-actions">
            <a href="{{ route('student.games') }}" class="btn btn-outline-secondary flex-fill">{!! t('Kembali', 'Back') !!}</a>
            <a href="{{ route('student.dashboard') }}" class="btn btn-primary flex-fill">{!! t('Ke Dashboard', 'To Dashboard') !!}</a>
        </div>
    </div>
</div>

<script>
const CSRF = {!! json_encode(csrf_token()) !!};
const API = {!! json_encode(route('student.games.api', [], false)) !!};
const T = {!! json_encode($i18n) !!};
let quiz = null, idx = 0, score = 0, answers = {}, timer = null, remaining = 0, finished = false;

function api(action, params, post) {
    if (post) {
        return fetch(API, { method: 'POST', body: new URLSearchParams({ action, _token: CSRF, ...params }), headers: { Accept: 'application/json' } }).then((r) => r.json());
    }
    return fetch(API + '?' + new URLSearchParams({ action, ...params }), { headers: { Accept: 'application/json' } }).then((r) => r.json());
}
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

api('quiz_stats', {}).then((s) => {
    document.getElementById('qrBest').textContent = s.best ?? 0;
    document.getElementById('qrRunsLeft').textContent = s.runs_left ?? 0;
    if (!s.runs_left) {
        document.getElementById('qrStartBtn').disabled = true;
        document.getElementById('qrComeBack').style.display = 'block';
    }
});

function startQuiz() {
    document.getElementById('qrStartBtn').disabled = true;
    api('quiz_start', {}, true).then((res) => {
        if (!res.questions) { alert(res.error || T.error); document.getElementById('qrStartBtn').disabled = false; return; }
        quiz = res; idx = 0; score = 0; answers = {}; remaining = res.seconds;
        document.getElementById('qrStart').style.display = 'none';
        document.getElementById('qrPlay').style.display = 'block';
        showQuestion();
        tick();
        timer = setInterval(tick, 1000);
    });
}

function tick() {
    const t = document.getElementById('qrTimer');
    t.textContent = remaining;
    t.classList.toggle('low', remaining <= 10);
    document.getElementById('qrBar').style.width = (remaining / quiz.seconds * 100) + '%';
    if (remaining <= 0) { finish(); return; }
    remaining--;
}

function showQuestion() {
    if (idx >= quiz.questions.length) { finish(); return; }
    const q = quiz.questions[idx];
    document.getElementById('qrNum').textContent = idx + 1;
    document.getElementById('qrQuestion').textContent = q.text;
    const box = document.getElementById('qrOptions');
    box.innerHTML = '';
    q.options.forEach((opt) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'qr-option';
        b.textContent = opt;
        b.onclick = () => choose(q, opt, b);
        box.appendChild(b);
    });
    const first = box.querySelector('button');
    if (first) first.focus();
}

function choose(q, opt, btn) {
    if (finished) return;
    answers[q.id] = opt;
    document.querySelectorAll('.qr-option').forEach((b) => { b.disabled = true; });
    btn.classList.add('chosen');
    idx++;
    document.getElementById('qrFlash').textContent = '';
    setTimeout(showQuestion, 180);
}

function finish() {
    if (finished) return;
    finished = true;
    clearInterval(timer);
    document.querySelectorAll('.qr-option').forEach((b) => { b.disabled = true; });
    document.getElementById('qrFlash').textContent = '⏰ ' + T.timeUp;
    api('quiz_finish', { match_id: quiz.match_id, answers: JSON.stringify(answers) }, true).then((res) => {
        if (res.error) { alert(res.error); return; }
        document.getElementById('qrScore').textContent = res.score;
        document.getElementById('resultTitle').textContent = T.score + ': ' + res.score + ' / ' + res.answered;
        document.getElementById('resultSub').textContent = res.score >= res.best && res.score > 0 ? '🏆 ' + T.newBest : '';
        if (res.xp_message) {
            document.getElementById('resultXpText').textContent = res.xp_message;
            document.getElementById('resultXpBadge').style.display = 'inline-block';
        }
        const review = document.getElementById('qrReview');
        review.innerHTML = res.review.length ? res.review.map((r) =>
            `<div class="qr-review-item ${r.ok ? '' : 'bad'}">${r.ok ? '✅' : '❌'} ${esc(r.question)}
             <div class="small mt-1">${T.yourAnswer}: <b>${esc(r.given)}</b>${r.ok ? '' : ` · ${T.answer}: <b>${esc(r.correct)}</b>`}</div></div>`
        ).join('') : `<p class="text-muted small text-center">${T.noAnswered}</p>`;
        document.getElementById('resultOverlay').classList.add('show');
    });
}
</script>
@endsection
