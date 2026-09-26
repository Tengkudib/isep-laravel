@extends('layouts.student')

@section('title'){{ $language['name'] }} - iSEP
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family: 'Segoe UI', sans-serif; }
        .topbar {
            background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%);
            color: white; padding: 28px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
        }
        .roadmap-item {
            display: flex; align-items: center; gap: 16px;
            background: white; border-radius: var(--isep-r-xl); padding: 18px 22px;
            box-shadow: var(--isep-shadow-sm); margin-bottom: 14px;
            border-left: 5px solid #dee2e6;
            transition: transform var(--isep-duration) var(--isep-ease);
        }
        .roadmap-item.completed { border-left-color: #198754; }
        .roadmap-item.in_progress { border-left-color: #ffc107; }
        .roadmap-item.locked { opacity: 0.6; }
        .roadmap-item:not(.locked):hover { transform: translateX(4px); }
        .step-icon {
            width: 48px; height: 48px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0; color: white;
        }
        .step-icon.completed { background: #198754; }
        .step-icon.in_progress { background: #ffc107; color: #212529; }
        .step-icon.not_started { background: #6c757d; }
        .step-icon.locked { background: #adb5bd; }
        .progress { height: 6px; border-radius: var(--isep-r-lg); width: 200px; }
        .back-link { color: white; text-decoration: none; }

        /* ---------- Laluan Heksagon ---------- */
        .path-overview {
            max-width: 420px; margin: 0 auto var(--isep-sp-5); background: var(--isep-card-bg);
            border-radius: var(--isep-r-xl); padding: var(--isep-sp-4) var(--isep-sp-5);
            box-shadow: var(--isep-shadow-sm); display: flex; align-items: center; gap: var(--isep-sp-4);
        }
        .path-overview .ring {
            position: relative; width: 52px; height: 52px; flex-shrink: 0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: conic-gradient(var(--isep-primary) calc(var(--pct,0) * 1%), var(--isep-border, #e5e7eb) 0);
        }
        .path-overview .ring::before {
            content: ''; position: absolute; inset: 5px; border-radius: 50%; background: var(--isep-card-bg);
        }
        .path-overview .ring span { position: relative; font-size: var(--isep-fs-xs); font-weight: 800; color: var(--isep-text); }
        .path-overview .label { font-size: var(--isep-fs-sm); font-weight: 700; color: var(--isep-text); margin-bottom: 2px; }
        .path-overview .sub { font-size: var(--isep-fs-xs); color: var(--isep-text-muted, #6c757d); }

        .hex-path { max-width: 420px; margin: 10px auto 0; padding-bottom: 20px; }
        .hex-row {
            position: relative; display: flex; flex-direction: column; align-items: center; width: 100%;
            opacity: 0; animation: hexRowIn 0.5s var(--isep-ease) forwards;
            animation-delay: calc(var(--hex-i, 0) * 90ms);
        }
        @keyframes hexRowIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) { .hex-row { animation: none; opacity: 1; } }

        /* Laluan melengkung SVG antara nod (gantikan bar lurus) */
        .hex-curve { display: block; width: 100%; height: 48px; }
        .hex-curve path { fill: none; stroke: #d7dae2; stroke-width: 8; stroke-linecap: round; }
        .hex-curve path.done { stroke: #1caf68; }
        [data-theme="dark"] .hex-curve path { stroke: #333947; }
        [data-theme="dark"] .hex-curve path.done { stroke: #1caf68; }

        .hex-offset { display: flex; flex-direction: column; align-items: center; margin-bottom: 4px; position: relative; z-index: 1; }
        .hex-offset.align-start { align-self: flex-start; margin-left: 8%; }
        .hex-offset.align-end { align-self: flex-end; margin-right: 8%; }

        /* Nod heksagon 3D "chunky" - lapisan tapak lebih gelap disorong ke bawah untuk kesan timbul */
        .hex-node {
            position: relative; width: 86px; height: 98px;
            clip-path: polygon(25% 3%, 75% 3%, 100% 50%, 75% 97%, 25% 97%, 0% 50%);
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; color: white; font-weight: 800; font-size: 1.4rem;
            filter: drop-shadow(0 8px 10px rgba(0,0,0,0.25));
            transition: transform 130ms var(--isep-ease), filter 130ms var(--isep-ease);
        }
        .hex-node::before {
            content: ''; position: absolute; inset: 0; z-index: -1; clip-path: inherit;
            background: var(--hex-base, #4b5563); transform: translateY(9px);
        }
        .hex-node:hover:not(.locked) { transform: translateY(4px); }
        .hex-node:active:not(.locked) { transform: translateY(8px); }

        .hex-node.completed { background: linear-gradient(160deg, #34d281, #17a668); --hex-base: #0e8552; }
        .hex-node.current {
            background: linear-gradient(160deg, var(--isep-primary), var(--isep-secondary)); --hex-base: var(--isep-primary-dark);
            animation: hexPulseGlow 2.4s ease-in-out infinite;
        }
        @keyframes hexPulseGlow {
            0%, 100% { filter: drop-shadow(0 8px 10px rgba(0,0,0,0.25)) drop-shadow(0 0 5px rgba(11,37,69,0.55)); }
            50% { filter: drop-shadow(0 8px 10px rgba(0,0,0,0.25)) drop-shadow(0 0 12px rgba(11,37,69,0.15)); }
        }
        @media (prefers-reduced-motion: reduce) {
            .hex-node.current { animation: none; filter: drop-shadow(0 8px 10px rgba(0,0,0,0.25)) drop-shadow(0 0 6px rgba(11,37,69,0.4)); }
        }
        .hex-node.locked {
            background: linear-gradient(160deg, #8b93a1, #6b7280); --hex-base: #454b56; color: rgba(255,255,255,0.85) !important;
            cursor: not-allowed;
        }
        .hex-node.trophy { background: linear-gradient(160deg, #D4AF37, #B8941F); --hex-base: #8a6d1f; }

        /* Kad "Mula" terapung di bawah nod semasa - macam prom Duolingo */
        .hex-start-card {
            position: relative; margin-top: 16px; background: var(--isep-card-bg);
            border: 1px solid rgba(11,37,69,0.12); border-radius: var(--isep-r-xl);
            padding: var(--isep-sp-4) var(--isep-sp-5); text-align: center; width: 210px;
            box-shadow: var(--isep-shadow-md);
        }
        .hex-start-card::before {
            content: ''; position: absolute; top: -8px; left: 50%; width: 15px; height: 15px;
            transform: translateX(-50%) rotate(45deg); background: var(--isep-card-bg);
            border-left: 1px solid rgba(11,37,69,0.12); border-top: 1px solid rgba(11,37,69,0.12);
        }
        .hex-start-card .title { font-weight: 700; font-size: var(--isep-fs-sm); color: var(--isep-text); margin-bottom: 10px; }
        .hex-start-card .btn-start {
            display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%;
            background: linear-gradient(135deg, var(--isep-primary), var(--isep-secondary)); color: white !important;
            font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; font-size: 0.8rem;
            padding: 11px 16px; border-radius: var(--isep-r-lg); text-decoration: none;
            box-shadow: 0 4px 0 0 var(--isep-primary-dark); transition: transform 130ms var(--isep-ease), box-shadow 130ms var(--isep-ease);
        }
        .hex-start-card .btn-start:hover { transform: translateY(2px); box-shadow: 0 2px 0 0 var(--isep-primary-dark); }
        .hex-start-card .btn-start:active { transform: translateY(4px); box-shadow: 0 0 0 0 var(--isep-primary-dark); }
        [data-theme="dark"] .hex-start-card { background: #1a1d27; border-color: rgba(255,255,255,0.08); }
        [data-theme="dark"] .hex-start-card::before { background: #1a1d27; border-color: rgba(255,255,255,0.08); }
        [data-theme="dark"] .hex-start-card .title { color: #ffffff !important; }

        /* Kapsyen ringkas untuk nod selesai/berkunci */
        .hex-caption { text-align: center; margin-top: 8px; max-width: 170px; }
        .hex-caption .title { font-weight: 600; font-size: var(--isep-fs-sm); color: var(--isep-text); }
        .hex-caption .sub { font-size: var(--isep-fs-xs); color: var(--isep-text-muted, #6c757d); display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 2px; }
        [data-theme="dark"] .hex-caption .title { color: #ffffff !important; }
        [data-theme="dark"] .hex-caption .sub { color: #e9ecf5 !important; }
        [data-theme="dark"] h5 { color: #ffffff !important; }
        [data-theme="dark"] .hex-node,
        [data-theme="dark"] .hex-node:hover { color: #ffffff !important; }
        [data-theme="dark"] .back-link { color: #ffffff !important; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <a href="{{ route('student.dashboard') }}" class="back-link small"><i class="fas fa-arrow-left me-1"></i> {{ t('Kembali ke Dashboard', 'Back to Dashboard') }}</a>
    <div class="d-flex align-items-center gap-3 mt-2">
        <div style="width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
            <i class="{{ $language['icon'] }}"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-0">{{ $language['name'] }}</h3>
            <small>{{ $language['difficulty'] }} · {!! count($chapters) !!} {{ t('bab', 'chapters') }}</small>
        </div>
    </div>
</div>

<div class="container-fluid p-4" style="max-width:800px;">
    <h5 class="fw-bold mb-3 text-center">{{ t('Laluan Pembelajaran', 'Learning Path') }}</h5>

    @php
        $completed_count = count(array_filter($progress_map, fn($p) => $p['chapter_status'] === 'completed'));
        $total_count = count($chapters);
        $overall_pct = $total_count > 0 ? round($completed_count / $total_count * 100) : 0;
    
@endphp
    <div class="path-overview">
        <div class="ring" style="--pct: {!! $overall_pct !!};"><span>{!! $overall_pct !!}%</span></div>
        <div>
            <div class="label">{!! $completed_count !!} {{ t('daripada', 'of') }} {!! $total_count !!} {{ t('bab selesai', 'chapters completed') }}</div>
            <div class="sub">{!! $completed_count === $total_count && $total_count > 0 ? t('Kursus selesai sepenuhnya!', 'Course fully completed!') : t('Teruskan usaha anda!', 'Keep up the momentum!') !!}</div>
        </div>
    </div>

    @php
        // Titik-x nod (dalam peratus, skala SVG 0-100) ikut sisi jajaran, untuk lengkung laluan
        $align_x = fn($align) => $align === 'align-start' ? 18 : 82;
        $curve = function ($fromAlign, $toAlign, $done) use ($align_x) {
            $x1 = $align_x($fromAlign); $x2 = $align_x($toAlign);
            $doneClass = $done ? 'done' : '';
            return '<svg class="hex-curve" viewBox="0 0 100 60" preserveAspectRatio="none">'
                . '<path class="' . $doneClass . '" d="M ' . $x1 . ' 0 C ' . $x1 . ' 32, ' . $x2 . ' 28, ' . $x2 . ' 60" />'
                . '</svg>';
        };
    
@endphp
    <div class="hex-path">
        @foreach ($chapters as $i => $chapter)
@php
            $p = $progress_map[$chapter['id']] ?? null;
            $status = $p['chapter_status'] ?? 'not_started';
            $unlocked = ($p['unlock_status'] ?? 'locked') === 'unlocked';
            $pct = $p['completion_percentage'] ?? 0;

            $node_class = $status === 'completed' ? 'completed' : ($unlocked ? 'current' : 'locked');
            $node_icon = $status === 'completed' ? '<i class="fas fa-check"></i>' : ($unlocked ? $chapter['chapter_number'] : '<i class="fas fa-lock"></i>');
            $align = $i % 2 === 0 ? 'align-start' : 'align-end';
            $href = $unlocked ? route('student.chapter', $chapter['id']) : '#';
            $chapter_label = t('Bab', 'Chapter') . ' ' . $chapter['chapter_number'] . ': ' . htmlspecialchars($chapter['title']);
@endphp
        <div class="hex-row" style="--hex-i: {!! $i !!};">
            @php
 if ($i > 0):
                $prev_align = ($i - 1) % 2 === 0 ? 'align-start' : 'align-end';
                $prev_completed = ($chapters[$i - 1] && ($progress_map[$chapters[$i-1]['id']]['chapter_status'] ?? '') === 'completed');
                echo $curve($prev_align, $align, $prev_completed);
            endif; 
@endphp
            <div class="hex-offset {!! $align !!}">
                <a href="{!! $href !!}" class="hex-node {!! $node_class !!}" title="{!! $chapter_label !!}">{!! $node_icon !!}</a>
                @if ($node_class === 'current')
                <div class="hex-start-card">
                    <div class="title">{!! $chapter_label !!}</div>
                    <a href="{!! $href !!}" class="btn-start"><i class="fas fa-play"></i> {!! $pct > 0 ? t('Sambung', 'Continue') : t('Mula', 'Start') !!}</a>
                </div>
                @elseif ($node_class === 'completed')
                <div class="hex-caption">
                    <div class="title">{!! $chapter_label !!}</div>
                    <div class="sub"><i class="fas fa-check-circle" style="color:#22c55e;"></i> {{ t('Selesai', 'Completed') }}</div>
                </div>
                @else
                <div class="hex-caption">
                    <div class="title">{!! $chapter_label !!}</div>
                    <div class="sub"><i class="fas fa-lock"></i> {{ t('Berkunci', 'Locked') }}</div>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        <!-- Sijil (bila semua bab selesai) -->
        @php
        $all_completed = count(array_filter($progress_map, fn($p) => $p['chapter_status'] === 'completed')) === count($chapters);
        @endphp
        @if ($all_completed)
        @php
            $last_align = (count($chapters) - 1) % 2 === 0 ? 'align-start' : 'align-end';
            $trophy_align = count($chapters) % 2 === 0 ? 'align-start' : 'align-end';
            $trophy_href = $cert ? route('student.certificate', $cert['certificate_code']) : '#';
        @endphp
        <div class="hex-row" style="--hex-i: {!! count($chapters) !!};">
            {!! $curve($last_align, $trophy_align, true) !!}
            <div class="hex-offset {!! $trophy_align !!}">
                <a href="{!! $trophy_href !!}" class="hex-node trophy"><i class="fas fa-certificate"></i></a>
                <div class="hex-caption">
                    <div class="title">🎉 {{ t('Sijil Kursus', 'Course Certificate') }}</div>
                    <div class="sub">{{ t('Semua bab selesai!', 'All chapters complete!') }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
