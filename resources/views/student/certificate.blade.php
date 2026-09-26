@extends('layouts.app')

@section('title'){{ t('Sijil', 'Certificate') }} - iSEP
@endsection
@section('no_fontawesome', '1')

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; padding:40px 20px; }
        .cert-frame {
            max-width: 820px; margin: 0 auto; background: white;
            border: 10px solid #0d6efd; border-radius: var(--isep-r-lg); padding: 60px 50px;
            text-align: center; position: relative;
            background-image: linear-gradient(135deg, rgba(13,110,253,0.04), rgba(109,40,217,0.04));
        }
        .cert-frame::before {
            content: ''; position: absolute; inset: 14px; border: 2px solid #6d28d9; border-radius: var(--isep-r-md); pointer-events: none;
        }
        .cert-title { font-size: 2rem; font-weight: 800; letter-spacing: 2px; color: #0a1128; }
        .cert-name { font-size: 1.8rem; font-weight: 700; color: #0d6efd; margin: 18px 0; font-style: italic; }
        .cert-code { font-family: monospace; color: #6c757d; }
        @media print { .no-print { display: none; } body { background: white; } }
    </style>
@endpush

@section('body')
    <div class="cert-frame">
        <p class="text-uppercase text-muted small mb-1" style="letter-spacing:3px;">Improve Self Education Platform</p>
        <div class="cert-title">CERTIFICATE OF COMPLETION</div>
        <p class="mt-4 mb-0 text-muted">Awarded to</p>
        <div class="cert-name">{{ $cert['student_name'] }}</div>
        <p class="text-muted">for successfully completing the full course of</p>
        <h3 class="fw-bold mb-4">{{ $cert['language_name'] }} Programming</h3>
        <p class="small text-muted mb-4">
            Covering all learning notes, videos, practice exercises, and quizzes for every chapter —
            not based on quiz achievement alone.
        </p>
        <p class="small">Issued on: {!! date('d F Y', strtotime($cert['issued_at'])) !!}</p>
        <p class="cert-code small">Certificate Code: {{ $cert['certificate_code'] }}</p>
        <p class="mb-0" style="color: var(--isep-text-muted); font-size: var(--isep-fs-xs); font-style: italic;">This certificate is computer-generated and does not require a signature.</p>
    </div>

    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>{{ t('Cetak / Muat Turun', 'Print / Download') }}</button>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">{{ t('Kembali ke Dashboard', 'Back to Dashboard') }}</a>
    </div>

    @if ($cosmetics['celebration_colors'])
    <canvas id="confettiCanvas" style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:999;"></canvas>
    <script>
    (function () {
        var colors = {!! json_encode(explode(',', $cosmetics['celebration_colors'])) !!};
        var canvas = document.getElementById('confettiCanvas');
        var ctx = canvas.getContext('2d');
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        resize(); window.addEventListener('resize', resize);

        var pieces = [];
        for (var i = 0; i < 140; i++) {
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

        var frame = 0;
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
    })();
    </script>
    @endif
@endsection
