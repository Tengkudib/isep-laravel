@extends('layouts.student')

@section('title'){{ $chapter['title'] }} - iSEP
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family: 'Segoe UI', sans-serif; }
        .topbar {
            background: linear-gradient(135deg, #0B2545 0%, #13315C 55%, #D4AF37 100%);
            color: white; padding: 24px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
        }
        .nav-tabs .nav-link { color: #495057; font-weight: 600; border: none; transition: all var(--isep-duration) var(--isep-ease); }
        .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background: none; }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-md); }
        .info-box { border-radius: var(--isep-r-lg); padding: 14px 18px; margin-bottom: 12px; }
        .info-box.tip { background: #e7f5ff; border-left: 4px solid #0d6efd; }
        .info-box.common_error { background: #fff3cd; border-left: 4px solid #ffc107; }
        .file-preview-box { background: #f8f9fa; }
        pre { background: #1e1e2f; color: #e2e2e2; border-radius: var(--isep-r-lg); padding: 16px; }
    </style>
@endpush

@push('styles_after')
<style>
        /* ---------- Quiz start stage: liquid-glass card + curtain reveal ---------- */
        .quiz-stage { position: relative; overflow: hidden; border-radius: var(--isep-r-xl); min-height: 260px; }

        .quiz-content { transition: filter 0.6s ease; }
        .quiz-content.locked { filter: blur(12px); pointer-events: none; user-select: none; }

        .quiz-curtain {
            position: absolute; top: 0; bottom: 0; width: 50%;
            background: linear-gradient(160deg, var(--isep-primary) 0%, var(--isep-secondary) 100%);
            z-index: 5; box-shadow: inset 0 0 70px rgba(0,0,0,0.28);
            transition: transform 0.9s cubic-bezier(.77,0,.18,1);
        }
        .quiz-curtain::before {
            content: ''; position: absolute; inset: 0;
            background-image: repeating-linear-gradient(90deg, rgba(255,255,255,0.10) 0px, rgba(255,255,255,0.10) 3px, transparent 3px, transparent 28px);
            mix-blend-mode: overlay;
        }
        .quiz-curtain-left { left: 0; border-radius: 0 var(--isep-r-xl) var(--isep-r-xl) 0; }
        .quiz-curtain-right { right: 0; border-radius: var(--isep-r-xl) 0 0 var(--isep-r-xl); }
        .quiz-curtain-left.opened { transform: translateX(-100%); }
        .quiz-curtain-right.opened { transform: translateX(100%); }

        .quiz-start-overlay {
            position: absolute; inset: 0; z-index: 10; padding: 20px;
            display: flex; align-items: center; justify-content: center;
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .quiz-start-overlay.hidden-overlay { opacity: 0; transform: scale(0.85); pointer-events: none; }

        .quiz-start-card {
            width: 100%; max-width: 440px; text-align: center; padding: 42px 36px;
            border-radius: var(--isep-r-xl); color: #1f2333;
            background: linear-gradient(160deg, rgba(255,255,255,0.68), rgba(255,255,255,0.38));
            border: 1px solid rgba(255,255,255,0.55);
            backdrop-filter: blur(22px) saturate(180%);
            -webkit-backdrop-filter: blur(22px) saturate(180%);
            box-shadow: var(--isep-shadow-xl), inset 0 1px 0 rgba(255,255,255,0.6);
        }
        [data-theme="dark"] .quiz-start-card {
            color: #E5E7EB;
            background: linear-gradient(160deg, rgba(40,42,60,0.7), rgba(28,30,44,0.5));
            border-color: rgba(255,255,255,0.12);
        }
        .quiz-start-icon {
            width: 64px; height: 64px; margin: 0 auto 16px; border-radius: var(--isep-r-xl);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: white;
            background: linear-gradient(135deg, var(--isep-primary), var(--isep-secondary));
            box-shadow: var(--isep-shadow-md);
        }
        .quiz-start-sub { opacity: 0.75; font-size: 0.9rem; }
        .quiz-kpi-row { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin: 22px 0; }
        .quiz-kpi {
            min-width: 96px; padding: 12px 16px; border-radius: var(--isep-r-xl);
            background: rgba(255,255,255,0.42); border: 1px solid rgba(255,255,255,0.55);
        }
        [data-theme="dark"] .quiz-kpi { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.12); }
        .quiz-kpi-value { font-size: 1.25rem; font-weight: 800; color: var(--isep-primary); }
        .quiz-kpi-label { font-size: 0.68rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }
        .quiz-start-note { font-size: 0.78rem; opacity: 0.7; margin-bottom: 24px; }
        .btn-quiz-start {
            border: none; color: white; padding: 13px 34px; border-radius: var(--isep-r-xl); font-weight: 700;
            background: linear-gradient(135deg, var(--isep-secondary), var(--isep-primary));
            box-shadow: var(--isep-shadow-md);
            transition: transform var(--isep-duration) var(--isep-ease), box-shadow var(--isep-duration) var(--isep-ease);
        }
        .btn-quiz-start:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(212,175,55,0.45); color: white; }

        /* ---- Cuba Sendiri ---- */
        .tryit-editor { font-family: Consolas, 'Courier New', monospace; font-size: 0.9rem; background: #1e1e2f; color: #e2e2e2; border-radius: 10px; tab-size: 4; line-height: 1.5; }
        .tryit-editor:focus { background: #1e1e2f; color: #e2e2e2; }
        .tryit-output { background: #1e1e2f; color: #7CFC9A; border-radius: 10px; min-height: 300px; max-height: 420px; overflow: auto; font-family: Consolas, 'Courier New', monospace; font-size: 0.9rem; white-space: pre-wrap; }
        .tryit-output.has-error { color: #ff9b9b; }
        .tryit-preview { width: 100%; min-height: 300px; border-radius: 10px; border: 1px solid #dee2e6; background: white; }
        .tryit-sql-table { border-collapse: collapse; color: #e2e2e2; font-size: 0.85rem; white-space: nowrap; }
        .tryit-sql-table th, .tryit-sql-table td { border: 1px solid #3a3a55; padding: 4px 10px; }
        .tryit-sql-table th { background: #2a2a44; color: #D4AF37; }
        .tryit-challenge { border-left: 4px solid #D4AF37; background: rgba(212,175,55,0.10); border-radius: 10px; padding: 12px 16px; }
        .tryit-tutor { border-top: 1px dashed rgba(107,122,143,0.4); padding-top: 16px; }
        .tryit-tutor-output { background: rgba(19,49,92,0.06); border-radius: 10px; padding: 14px 16px; max-height: 480px; overflow: auto; }
        .tryit-tutor-output.has-error { color: #b02a37; }
        [data-theme="dark"] .tryit-tutor-output { background: rgba(255,255,255,0.05); }
        [data-theme="dark"] .tryit-tutor-output.has-error { color: #ff9b9b; }
        .tryit-md { line-height: 1.6; }
        .tryit-md-h { font-weight: 700; margin-top: 6px; }
        .tryit-md-li { padding-left: 12px; }
        .tryit-md-gap { height: 8px; }
        .tryit-md code { background: rgba(107,122,143,0.18); padding: 1px 5px; border-radius: 4px; }
        .tryit-md-code { background: #1e1e2f; color: #e2e2e2; padding: 10px 12px; border-radius: 8px; margin: 6px 0; white-space: pre; overflow-x: auto; font-size: 0.85rem; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <a href="{{ route('student.language', $chapter['language_slug']) }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Kembali', 'Back') }}</a>
    <h4 class="fw-bold mt-2 mb-0">{{ t('Bab', 'Chapter') }} {!! $chapter['chapter_number'] !!} — {{ $chapter['title'] }}</h4>
    <small>{{ $chapter['language_name'] }} · {{ t('Markah kuiz minimum', 'Minimum quiz score') }}: {!! $chapter['minimum_quiz_score'] !!}%</small>
</div>

<div class="container-fluid p-4" style="max-width:900px;">

    @if ($message)
    <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i>{{ $message }}</div>
    @endif

    @if ($quiz_result)
    <div class="alert {!! $quiz_result['pass'] ? 'alert-success' : 'alert-warning' !!} border-0 shadow-sm">
        <h5 class="fw-bold">{!! $quiz_result['pass'] ? '🎉 ' . t('Kuiz Lulus!', 'Quiz Passed!') : '❌ ' . t('Belum Lulus', 'Not Yet Passed') !!}</h5>
        <p class="mb-0">{{ t('Skor anda', 'Your score') }}: {!! $quiz_result['score'] !!}/{!! $quiz_result['total'] !!} ({!! $quiz_result['percentage'] !!}%)</p>
        @if (!$quiz_result['pass'])
        <p class="mb-0 small">{{ t('Diperlukan', 'Required') }}: {!! $chapter['minimum_quiz_score'] !!}%. {{ t('Kaji semula nota dan cuba lagi.', 'Review the notes and try again.') }}</p>
        @endif
    </div>
    @endif

    <ul class="nav nav-tabs mb-4" id="chapterTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#notes-tab">📖 {{ t('Nota', 'Notes') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#video-tab">🎥 Video</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tryit-tab">🧪 {{ t('Cuba Sendiri', 'Try It Yourself') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#exercise-tab">✍️ {{ t('Latihan', 'Exercise') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#quiz-tab">🧠 {{ t('Kuiz', 'Quiz') }}</button></li>
    </ul>

    <div class="tab-content">
        <!-- NOTES -->
        <div class="tab-pane fade show active" id="notes-tab">
            <div class="card-modern p-4">
                @foreach ($notes as $n)
                    <h5 class="fw-bold">{{ $n['title'] }}</h5>
                    {{-- Nota ialah HTML asas daripada admin/pensyarah; safe_html() buang skrip & atribut berbahaya --}}
                    <div>{!! safe_html($n['content']) !!}</div>

                    @if (!empty($n['file_path']))
@php
                        $img_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
@endphp
                        <div class="mt-3 mb-3">
                        @if (in_array($n['file_type'], $img_types))
                            <img src="{{ asset($n['file_path']) }}" alt="{{ $n['file_name'] }}" class="img-fluid rounded shadow-sm" style="max-height:400px;">
                        @elseif ($n['file_type'] === 'pdf')
                            <div class="p-3 border rounded d-flex align-items-center justify-content-between file-preview-box">
                                <span><i class="fas fa-file-pdf text-danger me-2"></i>{{ $n['file_name'] }}</span>
                                <a href="{{ asset($n['file_path']) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ t('Buka PDF', 'Open PDF') }}</a>
                            </div>
                        @else
                            <div class="p-3 border rounded d-flex align-items-center justify-content-between file-preview-box">
                                <span>
                                    <i class="fas {!! $n['file_type']==='pptx' ? 'fa-file-powerpoint text-warning' : (in_array($n['file_type'], ['doc','docx']) ? 'fa-file-word text-primary' : 'fa-file-lines text-secondary') !!} me-2"></i>
                                    {{ $n['file_name'] }}
                                </span>
                                <a href="{{ asset($n['file_path']) }}" download class="btn btn-sm btn-outline-primary">{{ t('Muat Turun', 'Download') }}</a>
                            </div>
                        @endif
                        </div>
                    @endif
                @endforeach

                @foreach ($tips as $t)
                <div class="info-box {!! $t['content_type'] !!}">
                    <strong>{!! $t['content_type'] === 'tip' ? '💡 ' . t('Tip:', 'Tip:') : '⚠️ ' . t('Kesilapan Biasa:', 'Common Error:') !!}</strong>
                    {{ $t['content'] }}
                </div>
                @endforeach

                @foreach ($code_examples as $ce)
                    <p class="fw-semibold mt-3">💻 {{ $ce['title'] }}</p>
                    <pre><code>{{ $ce['code_example'] }}</code></pre>
                @endforeach

                <form method="POST" action="{{ route('student.chapter', $chapter_id) }}" class="mt-3">
                    @csrf
                    <button type="submit" name="mark_notes" class="btn btn-outline-primary btn-sm" {!! $progress['notes_status']==='completed'?'disabled':'' !!}>
                        {!! $progress['notes_status']==='completed' ? '✅ ' . t('Nota Selesai', 'Notes Complete') : t('Tandakan Selesai', 'Mark as Complete') !!}
                    </button>
                </form>
            </div>
        </div>

        <!-- VIDEO -->
        <div class="tab-pane fade" id="video-tab">
            <div class="card-modern p-4">
                @if (count($videos) === 0)
                    <p class="text-muted">{{ t('Tiada video untuk bab ini.', 'No video for this chapter.') }}</p>
                @endif
                @foreach ($videos as $v)
                    <h5 class="fw-bold">{{ $v['title'] }}</h5>
                    <p class="text-muted">{{ $v['video_duration'] ?? '' }}</p>
                    @if ($v['video_url'])
                    <div class="ratio ratio-16x9 mb-3">
                        <iframe src="{{ youtube_embed_url($v['video_url']) }}" allowfullscreen></iframe>
                    </div>
                    @endif
                @endforeach
                <form method="POST" action="{{ route('student.chapter', $chapter_id) }}">
                    @csrf
                    <button type="submit" name="mark_video" class="btn btn-outline-primary btn-sm" {!! $progress['video_status']==='completed'?'disabled':'' !!}>
                        {!! $progress['video_status']==='completed' ? '✅ ' . t('Video Selesai', 'Video Complete') : t('Tandakan Selesai Menonton', 'Mark as Watched') !!}
                    </button>
                </form>
            </div>
        </div>

        <!-- TRY IT YOURSELF -->
        @php
            $lang_slug = $chapter['language_slug'] ?? '';
            $ce = reset($code_examples);
            $default_snippets = [
                'python'     => "name = \"iSEP\"\nprint(\"Hello, \" + name + \"!\")\nfor i in range(3):\n    print(\"Baris\", i)",
                'javascript' => "const name = \"iSEP\";\nconsole.log(\"Hello, \" + name + \"!\");\nfor (let i = 0; i < 3; i++) {\n  console.log(\"Baris\", i);\n}",
                'html'       => "<h2 style=\"color:#13315C;\">Hello, iSEP!</h2>\n<p>Ubah suai HTML ini dan lihat pratonton di sebelah.</p>",
                'java'       => "public class Main {\n    public static void main(String[] args) {\n        String name = \"iSEP\";\n        System.out.println(\"Hello, \" + name + \"!\");\n        for (int i = 0; i < 3; i++) {\n            System.out.println(\"Baris \" + i);\n        }\n    }\n}",
                'php'        => "<?php\n\$name = \"iSEP\";\necho \"Hello, \$name!\\n\";\nfor (\$i = 0; \$i < 3; \$i++) {\n    echo \"Baris \$i\\n\";\n}",
                'mysql'      => "CREATE TABLE pelajar (\n    id INT AUTO_INCREMENT PRIMARY KEY,\n    nama VARCHAR(50),\n    markah INT\n);\n\nINSERT INTO pelajar (nama, markah) VALUES\n    ('Ali', 85), ('Siti', 92), ('John', 78);\n\nSELECT nama, markah FROM pelajar WHERE markah > 80 ORDER BY markah DESC;",
            ];
            $editor_prefill = $ce['code_example'] ?? ($default_snippets[$lang_slug] ?? '');
            $tryit_intro = [
                'python'     => t('Kod Python dijalankan BENAR-BENAR dalam pelayar anda (Pyodide). Guna print() untuk papar output.', 'Python code actually runs in your browser (Pyodide). Use print() to display output.'),
                'javascript' => t('Kod JavaScript dijalankan dalam ruang selamat (sandbox) di pelayar anda. Guna console.log() untuk papar output.', 'JavaScript code runs in a sandbox in your browser. Use console.log() to display output.'),
                'html'       => t('Ubah suai HTML dan tekan "Jalankan Kod" untuk lihat pratonton langsung.', 'Edit the HTML and press "Run Code" to see a live preview.'),
                'java'       => t('Kod Java dikompil dan dijalankan di pelayan (JDK 17). Kelas utama anda perlu ada kaedah main().', 'Java code is compiled and run on the server (JDK 17). Your main class needs a main() method.'),
                'php'        => t('Kod PHP dijalankan di pelayan (PHP 8.3). Mulakan dengan <?php dan guna echo untuk papar output.', 'PHP code runs on the server (PHP 8.3). Start with <?php and use echo to display output.'),
                'mysql'      => t('SQL dijalankan dalam pangkalan data latihan di pelayar anda - setiap larian bermula dengan pangkalan data kosong, jadi sertakan CREATE TABLE dan INSERT. (Enjin SQLite, serasi dengan asas MySQL.)', 'SQL runs in a practice database in your browser - every run starts empty, so include CREATE TABLE and INSERT. (SQLite engine, compatible with MySQL basics.)'),
            ][$lang_slug] ?? '';
        @endphp
        <div class="tab-pane fade" id="tryit-tab">
            <div class="card-modern p-4">
                <p class="text-muted mb-3">{{ $tryit_intro }}</p>

                <!-- Cabaran semasa daripada AI Tutor -->
                <div id="tryitChallengeBox" class="tryit-challenge mb-3" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong><i class="fas fa-bullseye me-1"></i>{{ t('Cabaran Anda', 'Your Challenge') }}</strong>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="clearChallenge()">{{ t('Tutup', 'Dismiss') }}</button>
                    </div>
                    <div id="tryitChallengeText" class="tryit-md"></div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                            <span>{{ t('Editor Kod', 'Code Editor') }} <span class="badge bg-light text-dark border ms-1">{{ $chapter['language_name'] }}</span></span>
                            <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="resetTryItCode()" title="{{ t('Kembalikan kod asal', 'Restore original code') }}"><i class="fas fa-rotate-left me-1"></i>{{ t('Set Semula', 'Reset') }}</button>
                        </label>
                        <textarea id="tryitEditor" class="form-control tryit-editor" rows="14" spellcheck="false">{{ $editor_prefill }}</textarea>
                        @if (in_array($lang_slug, ['java', 'php'], true))
                        <details class="mt-2">
                            <summary class="small text-muted">{{ t('Input program (stdin) - pilihan', 'Program input (stdin) - optional') }}</summary>
                            <textarea id="tryitStdin" class="form-control form-control-sm mt-1 tryit-editor" rows="2" spellcheck="false" placeholder="{{ t('Satu nilai setiap baris, untuk Scanner / fgets(STDIN)', 'One value per line, for Scanner / fgets(STDIN)') }}"></textarea>
                        </details>
                        @endif
                        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                            <button type="button" id="tryitRunBtn" class="btn btn-sm btn-primary" onclick="runTryItCode()"><i class="fas fa-play me-1"></i>{{ t('Jalankan Kod', 'Run Code') }}</button>
                            <small class="text-muted">Ctrl + Enter</small>
                            <span id="tryitLoadingNote" class="text-muted small" style="display:none;"></span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">{{ $lang_slug === 'html' ? t('Pratonton', 'Preview') : t('Output', 'Output') }}</label>
                        <div id="tryitOutput" class="tryit-output p-3" @if ($lang_slug === 'html') style="display:none;" @endif>&gt; {{ t('Tekan "Jalankan Kod" untuk lihat hasilnya di sini.', 'Press "Run Code" to see the result here.') }}</div>
                        <iframe id="tryitPreviewFrame" sandbox="allow-scripts" class="tryit-preview" @if ($lang_slug !== 'html') style="display:none;" @endif></iframe>
                        <iframe id="tryitJsRunner" sandbox="allow-scripts" style="display:none;"></iframe>
                    </div>
                </div>

                <!-- AI Tutor: mengajar pelajar memahami & menulis kod -->
                <div class="tryit-tutor mt-4">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <strong class="me-2"><i class="fas fa-robot me-1"></i>{{ t('Belajar dengan AI Tutor', 'Learn with the AI Tutor') }}</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-assist="explain" onclick="askTutor('explain')">💡 {{ t('Terangkan Kod', 'Explain Code') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-assist="challenge" onclick="askTutor('challenge')">🎯 {{ t('Beri Saya Cabaran', 'Give Me a Challenge') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-assist="review" onclick="askTutor('review')">✅ {{ t('Semak Kod Saya', 'Check My Code') }}</button>
                    </div>
                    <small class="text-muted d-block mb-2">{{ t('Terangkan: fahami kod baris demi baris. Cabaran: dapatkan latihan kecil untuk tajuk ini. Semak: dapatkan maklum balas & petunjuk (bukan jawapan penuh).', 'Explain: understand the code line by line. Challenge: get a small exercise for this topic. Check: get feedback & hints (not the full answer).') }}</small>
                    <div id="tryitTutorOutput" class="tryit-md tryit-tutor-output" style="display:none;"></div>
                </div>

                <form method="POST" action="{{ route('student.chapter', $chapter_id) }}" class="mt-3">
                    @csrf
                    <button type="submit" id="tryitMarkBtn" name="mark_try_it" class="btn btn-outline-primary btn-sm" {!! $progress['try_it_status']==='completed' ? 'disabled' : 'disabled data-needs-run="1"' !!}>
                        {!! $progress['try_it_status']==='completed' ? '✅ ' . t('Aktiviti Selesai', 'Activity Complete') : t('Tandakan Selesai', 'Mark as Complete') !!}
                    </button>
                    @if ($progress['try_it_status'] !== 'completed')
                    <small id="tryitMarkHint" class="text-muted ms-2">{{ t('Jalankan kod anda sekurang-kurangnya sekali untuk menandakan selesai.', 'Run your code at least once to mark this complete.') }}</small>
                    @endif
                </form>
            </div>
        </div>

        <!-- EXERCISE -->
        <div class="tab-pane fade" id="exercise-tab">
            @if (count($exercises) === 0)
            <div class="card-modern p-4"><p class="text-muted mb-0">{{ t('Tiada latihan untuk bab ini.', 'No exercises for this chapter.') }}</p></div>
            @endif
            @foreach ($exercises as $ex)
            <div class="card-modern p-4 mb-3">
                <span class="badge bg-{!! $ex['difficulty']==='Beginner'?'success':($ex['difficulty']==='Intermediate'?'warning':'danger') !!} mb-2">{!! $ex['difficulty'] !!}</span>
                <h6 class="fw-bold">{{ $ex['question'] }}</h6>
                <p class="text-muted small">{{ $ex['instruction'] }}</p>
                @if ($ex['sample_input'])<p class="small"><strong>{{ t('Input Sampel:', 'Sample Input:') }}</strong> {{ $ex['sample_input'] }}</p>@endif
                @if ($ex['expected_output'])<p class="small"><strong>{{ t('Output Dijangka:', 'Expected Output:') }}</strong> {{ $ex['expected_output'] }}</p>@endif

                <form method="POST" action="{{ route('student.chapter', $chapter_id) }}">
                    @csrf
                    <input type="hidden" name="exercise_id" value="{!! $ex['id'] !!}">
                    <textarea name="answer_code" class="form-control mb-2" rows="4" placeholder="{{ t('Tulis jawapan/kod anda di sini...', 'Write your answer/code here...') }}" style="font-family:monospace;"></textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_exercise" class="btn btn-primary btn-sm">{{ t('Hantar Jawapan', 'Submit Answer') }} (+{!! $ex['points'] !!} XP)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#hint-{!! $ex['id'] !!}">💡 {{ t('Tunjuk Petunjuk', 'Show Hint') }}</button>
                    </div>
                    <div class="collapse mt-2" id="hint-{!! $ex['id'] !!}">
                        <div class="alert alert-info small mb-0">{{ $ex['hint'] }}</div>
                    </div>
                </form>
            </div>
            @endforeach
        </div>

        <!-- QUIZ -->
        <div class="tab-pane fade" id="quiz-tab">
            @if (count($quizzes) === 0)
            <div class="card-modern p-4">
                <p class="text-muted mb-0">{{ t('Tiada kuiz untuk bab ini.', 'No quiz for this chapter.') }}</p>
            </div>
            @else
            <div class="quiz-stage" id="quizStage">

                <!-- Curtain panels: hide questions until "Mula Kuiz" is pressed -->
                <div class="quiz-curtain quiz-curtain-left" id="quizCurtainLeft"></div>
                <div class="quiz-curtain quiz-curtain-right" id="quizCurtainRight"></div>

                <!-- Liquid-glass start card -->
                <div class="quiz-start-overlay" id="quizStartOverlay">
                    <div class="quiz-start-card">
                        <div class="quiz-start-icon"><i class="fas fa-brain"></i></div>
                        <h4 class="fw-bold mb-1">{{ t('Kuiz Bab', 'Chapter Quiz') }} {!! $chapter['chapter_number'] !!}</h4>
                        <p class="quiz-start-sub mb-0">{{ $chapter['title'] }}</p>

                        <div class="quiz-kpi-row">
                            <div class="quiz-kpi">
                                <div class="quiz-kpi-value">{!! count($quizzes) !!}</div>
                                <div class="quiz-kpi-label"><i class="fas fa-list-ol me-1"></i>{{ t('Soalan', 'Questions') }}</div>
                            </div>
                            <div class="quiz-kpi">
                                <div class="quiz-kpi-value">{!! (int)$chapter['time_limit_minutes'] !!} min</div>
                                <div class="quiz-kpi-label"><i class="fas fa-clock me-1"></i>{{ t('Had Masa', 'Time Limit') }}</div>
                            </div>
                            <div class="quiz-kpi">
                                <div class="quiz-kpi-value">{!! (int)$chapter['minimum_quiz_score'] !!}%</div>
                                <div class="quiz-kpi-label"><i class="fas fa-check-circle me-1"></i>{{ t('Markah Lulus', 'Passing Score') }}</div>
                            </div>
                        </div>

                        <p class="quiz-start-note mb-0"><i class="fas fa-circle-info me-1"></i>{{ t('Masa akan mula dikira sebaik sahaja anda tekan mula. Kuiz dihantar automatik jika masa tamat.', 'The timer starts as soon as you press start. The quiz is submitted automatically if time runs out.') }}</p>

                        <button type="button" id="quizStartBtn" class="btn btn-quiz-start">
                            <i class="fas fa-play me-2"></i>{{ t('Mula Kuiz', 'Start Quiz') }}
                        </button>
                    </div>
                </div>

                <!-- Actual quiz content: blurred + locked until start -->
                <div class="card-modern p-4 quiz-content locked" id="quizContent">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">{{ t('Had masa', 'Time limit') }}: {!! (int)$chapter['time_limit_minutes'] !!} {{ t('minit', 'minutes') }}</span>
                        <span id="quizTimer" class="badge bg-danger fs-6">--:--</span>
                    </div>
                    <form method="POST" action="{{ route('student.chapter', $chapter_id) }}" id="quizForm">
                        @csrf
                        <input type="hidden" name="submit_quiz" value="1">
                        @foreach ($quizzes as $i => $q)
                        <div class="mb-4">
                            <p class="fw-semibold">{{ t('Soalan', 'Question') }} {!! $i+1 !!}: {{ $q['question'] }}</p>
                            @if ($q['question_type'] === 'multiple_choice')
                                @foreach (['a'=>'option_a','b'=>'option_b','c'=>'option_c','d'=>'option_d'] as $key => $col)
                                    @if ($q[$col])
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="q{!! $q['id'] !!}" value="{{ $q[$col] }}" required>
                                        <label class="form-check-label">{{ $q[$col] }}</label>
                                    </div>
                                    @endif
                                @endforeach
                            @elseif ($q['question_type'] === 'true_false')
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q{!! $q['id'] !!}" value="True" required>
                                    <label class="form-check-label">{{ t('Betul', 'True') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q{!! $q['id'] !!}" value="False">
                                    <label class="form-check-label">{{ t('Salah', 'False') }}</label>
                                </div>
                            @else
                                <input type="text" name="q{!! $q['id'] !!}" class="form-control" placeholder="{{ t('Jawapan anda', 'Your answer') }}" required>
                            @endif
                        </div>
                        @endforeach
                        <button type="submit" name="submit_quiz" id="quizSubmitBtn" class="btn btn-primary">{{ t('Hantar Kuiz', 'Submit Quiz') }}</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>



@if ($lang_slug === 'python')
<script src="https://cdn.jsdelivr.net/pyodide/v0.26.2/full/pyodide.js"></script>
@elseif ($lang_slug === 'mysql')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sql.js/1.10.3/sql-wasm.js"></script>
@endif
<script>
const TRYIT_LANG = {!! json_encode($lang_slug) !!};
const TRYIT_CHAPTER_ID = {{ (int) $chapter_id }};
const TRYIT_CSRF = {!! json_encode(csrf_token()) !!};
const TRYIT_RUN_URL = {!! json_encode(route('student.code.run', [], false)) !!};
const TRYIT_ASSIST_URL = {!! json_encode(route('student.code.assist', [], false)) !!};
const TRYIT_ORIGINAL_CODE = document.getElementById('tryitEditor').value;
let pyodideReadyPromise = null;
let sqlReadyPromise = null;
let tryitLastOutput = '';
let tryitChallenge = '';

const TRYIT_I18N = {
    running: {!! json_encode(t('Menjalankan kod...', 'Running code...')) !!},
    errorPrefix: {!! json_encode(t('Ralat: ', 'Error: ')) !!},
    warnPrefix: {!! json_encode(t('Amaran: ', 'Warning: ')) !!},
    noOutputPrint: {!! json_encode(t('(Tiada output - guna print() untuk papar sesuatu)', '(No output - use print() to display something)')) !!},
    noOutputConsoleLog: {!! json_encode(t('(Tiada output - guna console.log() untuk papar sesuatu)', '(No output - use console.log() to display something)')) !!},
    noOutputGeneric: {!! json_encode(t('(Program selesai tanpa output)', '(Program finished with no output)')) !!},
    loadingPython: {!! json_encode(t('Memuatkan pelaksana Python... sekali sahaja, mungkin ambil beberapa saat.', 'Loading the Python runtime... one time only, may take a few seconds.')) !!},
    loadingSql: {!! json_encode(t('Memuatkan pangkalan data latihan...', 'Loading the practice database...')) !!},
    runtimeLoadFail: {!! json_encode(t('Gagal memuatkan pelaksana kod: ', 'Failed to load the code runtime: ')) !!},
    compileError: {!! json_encode(t('Ralat kompilasi:', 'Compilation error:')) !!},
    connError: {!! json_encode(t('Ralat sambungan. Sila cuba lagi.', 'Connection error. Please try again.')) !!},
    sqlOk: {!! json_encode(t('Pernyataan berjaya dijalankan (tiada baris untuk dipaparkan).', 'Statements ran successfully (no rows to display).')) !!},
    sqlRows: {!! json_encode(t('baris', 'row(s)')) !!},
    thinking: {!! json_encode(t('AI Tutor sedang berfikir...', 'The AI Tutor is thinking...')) !!},
    confirmReset: {!! json_encode(t('Kembalikan kod asal? Perubahan anda akan hilang.', 'Restore the original code? Your changes will be lost.')) !!},
    challengeLoaded: {!! json_encode(t('Cabaran telah ditambah di atas editor. Tulis penyelesaian anda, jalankan, kemudian tekan "Semak Kod Saya".', 'The challenge has been added above the editor. Write your solution, run it, then press "Check My Code".')) !!}
};

// ---------------- Editor: Tab = 4 ruang, Ctrl+Enter = jalankan ----------------
document.getElementById('tryitEditor').addEventListener('keydown', function (e) {
    if (e.key === 'Tab' && !e.shiftKey) {
        e.preventDefault();
        const s = this.selectionStart, end = this.selectionEnd;
        this.value = this.value.slice(0, s) + '    ' + this.value.slice(end);
        this.selectionStart = this.selectionEnd = s + 4;
    } else if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        runTryItCode();
    }
});

function resetTryItCode() {
    if (!confirm(TRYIT_I18N.confirmReset)) return;
    document.getElementById('tryitEditor').value = TRYIT_ORIGINAL_CODE;
}

function setOutput(text, isError) {
    const out = document.getElementById('tryitOutput');
    out.textContent = text;
    out.classList.toggle('has-error', !!isError);
    tryitLastOutput = text;
}

// Butang "Tandakan Selesai" hanya aktif selepas pelajar menjalankan kod sekurang-kurangnya sekali
function markRanOnce() {
    const btn = document.getElementById('tryitMarkBtn');
    if (btn && btn.dataset.needsRun) {
        btn.disabled = false;
        delete btn.dataset.needsRun;
        const hint = document.getElementById('tryitMarkHint');
        if (hint) hint.remove();
    }
}

function runTryItCode() {
    const runners = { python: runPythonCode, javascript: runJsCode, html: runHtmlPreview, mysql: runSqlCode, java: runServerCode, php: runServerCode };
    const fn = runners[TRYIT_LANG];
    if (fn) { fn(); markRanOnce(); }
}

// Buang baris dalaman Pyodide daripada traceback - pelajar hanya perlu nampak baris kod mereka sendiri
function cleanPythonTraceback(msg) {
    const lines = msg.split('\n');
    const first = lines.findIndex((l) => l.includes('File "<exec>"'));
    return first === -1 ? msg : 'Traceback (most recent call last):\n' + lines.slice(first).join('\n');
}

async function runPythonCode() {
    const code = document.getElementById('tryitEditor').value;
    const runBtn = document.getElementById('tryitRunBtn');
    const loadingNote = document.getElementById('tryitLoadingNote');

    runBtn.disabled = true;
    try {
        if (!pyodideReadyPromise) {
            loadingNote.textContent = TRYIT_I18N.loadingPython;
            loadingNote.style.display = 'inline';
            pyodideReadyPromise = loadPyodide();
        }
        setOutput(TRYIT_I18N.running);
        const pyodide = await pyodideReadyPromise;
        loadingNote.style.display = 'none';

        let buffer = '';
        let failed = false;
        pyodide.setStdout({ batched: (s) => { buffer += s + '\n'; } });
        pyodide.setStderr({ batched: (s) => { buffer += s + '\n'; } });

        try {
            await pyodide.runPythonAsync(code);
        } catch (err) {
            failed = true;
            buffer += TRYIT_I18N.errorPrefix + cleanPythonTraceback(String(err.message || err));
        }
        setOutput(buffer || TRYIT_I18N.noOutputPrint, failed);
    } catch (err) {
        loadingNote.style.display = 'none';
        pyodideReadyPromise = null;
        setOutput(TRYIT_I18N.runtimeLoadFail + (err.message || err), true);
    } finally {
        runBtn.disabled = false;
    }
}

function runJsCode() {
    const code = document.getElementById('tryitEditor').value.replace(/<\/script/gi, '<\\/script');
    const frame = document.getElementById('tryitJsRunner');
    setOutput(TRYIT_I18N.running);

    const jsErrorPrefix = JSON.stringify(TRYIT_I18N.errorPrefix);
    const jsWarnPrefix = JSON.stringify(TRYIT_I18N.warnPrefix);
    const doc = '<script>' +
        'const __logs = []; let __err = false;' +
        'function __fmt(a) { try { return typeof a === "object" ? JSON.stringify(a) : String(a); } catch (e) { return String(a); } }' +
        'console.log = function() { __logs.push(Array.prototype.map.call(arguments, __fmt).join(" ")); };' +
        'console.error = function() { __logs.push(' + jsErrorPrefix + ' + Array.prototype.map.call(arguments, __fmt).join(" ")); };' +
        'console.warn = function() { __logs.push(' + jsWarnPrefix + ' + Array.prototype.map.call(arguments, __fmt).join(" ")); };' +
        'window.onerror = function(msg) { __logs.push(' + jsErrorPrefix + ' + msg); parent.postMessage({ type: "tryitJsOutput", logs: __logs, error: true }, "*"); };' +
        'try {' + code + '} catch (e) { __err = true; __logs.push(' + jsErrorPrefix + ' + e.message); }' +
        'parent.postMessage({ type: "tryitJsOutput", logs: __logs, error: __err }, "*");' +
        '<\/script>';
    frame.srcdoc = doc;
}

window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'tryitJsOutput') {
        setOutput(e.data.logs.length ? e.data.logs.join('\n') : TRYIT_I18N.noOutputConsoleLog, e.data.error);
    }
});

function runHtmlPreview() {
    const code = document.getElementById('tryitEditor').value;
    document.getElementById('tryitPreviewFrame').srcdoc = code;
    tryitLastOutput = '(HTML preview rendered)';
}
@if ($lang_slug === 'html')
document.addEventListener('DOMContentLoaded', runHtmlPreview);
@endif

// ---------------- MySQL: pangkalan data latihan SQLite (sql.js) dalam pelayar ----------------
function mysqlToSqlite(sql) {
    return sql
        // INT AUTO_INCREMENT PRIMARY KEY -> INTEGER PRIMARY KEY AUTOINCREMENT
        .replace(/\b(?:BIG|SMALL|TINY|MEDIUM)?INT(?:EGER)?(?:\s*\(\s*\d+\s*\))?(?:\s+UNSIGNED)?(?:\s+NOT\s+NULL)?\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/gi, 'INTEGER PRIMARY KEY AUTOINCREMENT')
        .replace(/\s+AUTO_INCREMENT\b(?!\s*=)/gi, '')
        .replace(/\)\s*(?:ENGINE|DEFAULT\s+CHARSET|AUTO_INCREMENT)\s*=[^;]*;/gi, ');')
        .replace(/\s+UNSIGNED\b/gi, '')
        .replace(/`/g, '"');
}

function renderSqlResults(results) {
    const out = document.getElementById('tryitOutput');
    out.classList.remove('has-error');
    if (!results.length) { setOutput(TRYIT_I18N.sqlOk); return; }
    out.innerHTML = '';
    const summary = [];
    for (const res of results) {
        const table = document.createElement('table');
        table.className = 'tryit-sql-table';
        const head = table.createTHead().insertRow();
        res.columns.forEach((c) => { const th = document.createElement('th'); th.textContent = c; head.appendChild(th); });
        const body = table.createTBody();
        res.values.forEach((row) => {
            const tr = body.insertRow();
            row.forEach((v) => { tr.insertCell().textContent = v === null ? 'NULL' : v; });
        });
        const caption = document.createElement('div');
        caption.className = 'small text-muted mt-2';
        caption.textContent = res.values.length + ' ' + TRYIT_I18N.sqlRows;
        out.appendChild(table);
        out.appendChild(caption);
        summary.push(res.columns.join(' | ') + '\n' + res.values.map((r) => r.join(' | ')).join('\n'));
    }
    tryitLastOutput = summary.join('\n\n');
}

async function runSqlCode() {
    const runBtn = document.getElementById('tryitRunBtn');
    const loadingNote = document.getElementById('tryitLoadingNote');
    runBtn.disabled = true;
    try {
        if (!sqlReadyPromise) {
            loadingNote.textContent = TRYIT_I18N.loadingSql;
            loadingNote.style.display = 'inline';
            sqlReadyPromise = initSqlJs({ locateFile: (f) => 'https://cdnjs.cloudflare.com/ajax/libs/sql.js/1.10.3/' + f });
        }
        setOutput(TRYIT_I18N.running);
        const SQL = await sqlReadyPromise;
        loadingNote.style.display = 'none';
        const db = new SQL.Database();
        try {
            renderSqlResults(db.exec(mysqlToSqlite(document.getElementById('tryitEditor').value)));
        } catch (err) {
            setOutput(TRYIT_I18N.errorPrefix + (err.message || err), true);
        } finally {
            db.close();
        }
    } catch (err) {
        loadingNote.style.display = 'none';
        sqlReadyPromise = null;
        setOutput(TRYIT_I18N.runtimeLoadFail + (err.message || err), true);
    } finally {
        runBtn.disabled = false;
    }
}

// ---------------- Java & PHP: dijalankan di pelayan ----------------
function postForm(url, params) {
    const body = new URLSearchParams({ _token: TRYIT_CSRF, ...params });
    return fetch(url, { method: 'POST', body, headers: { 'Accept': 'application/json' } })
        .then((r) => r.json().catch(() => ({ error: TRYIT_I18N.connError + ' (HTTP ' + r.status + ')' })));
}

function runServerCode() {
    const runBtn = document.getElementById('tryitRunBtn');
    const stdinEl = document.getElementById('tryitStdin');
    runBtn.disabled = true;
    setOutput(TRYIT_I18N.running);
    postForm(TRYIT_RUN_URL, {
        language: TRYIT_LANG,
        code: document.getElementById('tryitEditor').value,
        stdin: stdinEl ? stdinEl.value : '',
    }).then((res) => {
        if (res.error) { setOutput(res.error, true); return; }
        if (res.compile_output) { setOutput(TRYIT_I18N.compileError + '\n' + res.compile_output, true); return; }
        let text = res.stdout || '';
        if (res.stderr) text += (text ? '\n' : '') + res.stderr;
        if (!res.ok && res.status) text += (text ? '\n' : '') + '[' + res.status + ']';
        setOutput(text || TRYIT_I18N.noOutputGeneric, !res.ok);
    }).catch(() => setOutput(TRYIT_I18N.connError, true))
      .finally(() => { runBtn.disabled = false; });
}

// ---------------- AI Tutor: terangkan, beri cabaran, semak ----------------
function escapeHtmlTryit(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

// Penukar Markdown ringkas (teks di-escape dahulu, jadi selamat daripada HTML berbahaya)
function renderMarkdown(md) {
    const blocks = [];
    let html = escapeHtmlTryit(md).replace(/```[\w+-]*\n?([\s\S]*?)```/g, (_, code) => {
        blocks.push('<pre class="tryit-md-code">' + code.replace(/\n$/, '') + '</pre>');
        return '\u0000' + (blocks.length - 1) + '\u0000';
    });
    html = html
        .replace(/`([^`\n]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
        .replace(/^#{1,6}\s+(.+)$/gm, '<div class="tryit-md-h">$1</div>')
        .replace(/^\s*[-*]\s+(.+)$/gm, '<div class="tryit-md-li">• $1</div>')
        .replace(/^\s*(\d+)\.\s+(.+)$/gm, '<div class="tryit-md-li">$1. $2</div>')
        .replace(/\n{2,}/g, '<div class="tryit-md-gap"></div>')
        .replace(/\n/g, '<br>');
    return html.replace(/\u0000(\d+)\u0000/g, (_, i) => blocks[+i]);
}

function setChallenge(text) {
    tryitChallenge = text;
    document.getElementById('tryitChallengeText').innerHTML = renderMarkdown(text);
    document.getElementById('tryitChallengeBox').style.display = text ? 'block' : 'none';
}
function clearChallenge() { setChallenge(''); }

function askTutor(mode) {
    const out = document.getElementById('tryitTutorOutput');
    const buttons = document.querySelectorAll('[data-assist]');
    buttons.forEach((b) => { b.disabled = true; });
    out.style.display = 'block';
    out.classList.remove('has-error');
    out.textContent = TRYIT_I18N.thinking;

    postForm(TRYIT_ASSIST_URL, {
        mode,
        chapter_id: String(TRYIT_CHAPTER_ID),
        code: document.getElementById('tryitEditor').value,
        output: tryitLastOutput,
        challenge: tryitChallenge,
    }).then((res) => {
        if (res.error) { out.classList.add('has-error'); out.textContent = res.error; return; }
        if (mode === 'challenge') {
            setChallenge(res.reply);
            out.textContent = TRYIT_I18N.challengeLoaded;
            document.getElementById('tryitChallengeBox').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            out.innerHTML = renderMarkdown(res.reply);
        }
    }).catch(() => { out.classList.add('has-error'); out.textContent = TRYIT_I18N.connError; })
      .finally(() => { buttons.forEach((b) => { b.disabled = false; }); });
}
</script>

@if (count($quizzes) > 0)
<script>
(function () {
    const timeLimitMinutes = {!! (int)$chapter['time_limit_minutes'] !!};
    let remaining = timeLimitMinutes * 60;
    const timerEl = document.getElementById('quizTimer');
    const form = document.getElementById('quizForm');
    const startBtn = document.getElementById('quizStartBtn');
    const overlay = document.getElementById('quizStartOverlay');
    const curtainLeft = document.getElementById('quizCurtainLeft');
    const curtainRight = document.getElementById('quizCurtainRight');
    const content = document.getElementById('quizContent');
    let autoSubmitted = false;
    let started = false;

    function render() {
        const m = Math.floor(remaining / 60).toString().padStart(2, '0');
        const s = (remaining % 60).toString().padStart(2, '0');
        timerEl.textContent = m + ':' + s;
        if (remaining <= 60) timerEl.classList.add('blink');
    }

    render();

    function startQuiz() {
        if (started) return;
        started = true;

        overlay.classList.add('hidden-overlay');
        curtainLeft.classList.add('opened');
        curtainRight.classList.add('opened');
        content.classList.remove('locked');

        const interval = setInterval(function () {
            remaining--;
            render();
            if (remaining <= 0 && !autoSubmitted) {
                autoSubmitted = true;
                clearInterval(interval);
                timerEl.textContent = '00:00';
                alert({!! json_encode(t('Masa tamat! Kuiz akan dihantar secara automatik.', 'Time is up! The quiz will be submitted automatically.')) !!});
                form.submit();
            }
        }, 1000);
    }

    if (startBtn) startBtn.addEventListener('click', startQuiz);
})();
</script>
<style>
#quizTimer.blink { animation: blinkAnim 1s infinite; }
@keyframes blinkAnim { 50% { opacity: 0.4; } }
</style>
@endif
@endsection
