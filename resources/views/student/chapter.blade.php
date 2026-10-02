@extends('layouts.student')

@section('title'){{ $chapter['title'] }} - iSEP
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family: 'Segoe UI', sans-serif; }
        .exercise-form button[name="submit_exercise"]:disabled { background: #adb5bd; border-color: #adb5bd; color: #fff; opacity: 1; cursor: not-allowed; pointer-events: auto; }
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

    @if ($exercise_error)
    <div class="alert alert-warning border-0 shadow-sm"><i class="fas fa-triangle-exclamation me-2"></i>{{ $exercise_error }}</div>
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
                    <div>@php
 echo $n['content']; // konten notes disimpan sebagai HTML terkawal oleh admin 
@endphp</div>

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
            $runs_live = in_array($lang_slug, ['python', 'javascript', 'html'], true);
            $ce = reset($code_examples);
            $default_snippets = [
                'python'     => "name = \"iSEP\"\nprint(\"Hello, \" + name + \"!\")\nfor i in range(3):\n    print(\"Baris\", i)",
                'javascript' => "const name = \"iSEP\";\nconsole.log(\"Hello, \" + name + \"!\");\nfor (let i = 0; i < 3; i++) {\n  console.log(\"Baris\", i);\n}",
                'html'       => "<h2 style=\"color:#13315C;\">Hello, iSEP!</h2>\n<p>Ubah suai HTML ini dan lihat pratonton di sebelah.</p>",
            ];
            $editor_prefill = $ce['code_example'] ?? ($default_snippets[$lang_slug] ?? '# Tulis kod anda di sini');
        
@endphp
        <div class="tab-pane fade" id="tryit-tab">
            <div class="card-modern p-4">
                @if ($lang_slug === 'python')
                    <p class="text-muted">{{ t('Tulis kod Python di bawah dan tekan "Jalankan Kod" - kod ini dijalankan BENAR-BENAR dalam pelayar anda (Pyodide), tiada apa-apa dihantar ke pelayan.', 'Write Python code below and press "Run Code" - this code actually runs in your browser (Pyodide), nothing is sent to the server.') }}</p>
                @elseif ($lang_slug === 'javascript')
                    <p class="text-muted">{{ t('Tulis kod JavaScript di bawah dan tekan "Jalankan Kod" - guna console.log() untuk papar output. Kod dijalankan dalam ruang selamat (sandbox) di pelayar anda sahaja.', 'Write JavaScript code below and press "Run Code" - use console.log() to display output. The code runs in a sandbox in your browser only.') }}</p>
                @elseif ($lang_slug === 'html')
                    <p class="text-muted">{{ t('Ubah suai kod HTML di bawah dan tekan "Jalankan Kod" untuk lihat pratonton langsung di sebelah kanan.', 'Modify the HTML code below and press "Run Code" to see a live preview on the right.') }}</p>
                @else
                    <p class="text-muted">{{ t('Ubah suai kod di bawah dan fahami bagaimana ia berfungsi (simulasi - pelaksana kod sebenar untuk', 'Modify the code below and understand how it works (simulation - real code execution for') }} {{ $chapter['language_name'] }} {{ t('belum disokong dalam pelayar).', 'is not yet supported in the browser).') }}</p>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ t('Editor Kod', 'Code Editor') }}</label>
                        <textarea id="tryitEditor" class="form-control" rows="10" style="font-family:monospace;background:#1e1e2f;color:#e2e2e2;">{{ $editor_prefill }}</textarea>
                        <button type="button" id="tryitRunBtn" class="btn btn-sm btn-primary mt-2" onclick="runTryItCode()"><i class="fas fa-play me-1"></i>{{ t('Jalankan Kod', 'Run Code') }}</button>
                        <span id="tryitLoadingNote" class="text-muted small ms-2" style="display:none;">{{ t('Memuatkan pelaksana Python (Pyodide)... sekali sahaja, mungkin ambil beberapa saat.', 'Loading the Python runtime (Pyodide)... one time only, may take a few seconds.') }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{!! $lang_slug === 'html' ? t('Pratonton', 'Preview') : t('Output', 'Output') !!}</label>
                        <div id="tryitOutput" class="p-3" style="background:#1e1e2f;color:#0f0;border-radius:10px;min-height:220px;font-family:monospace;white-space:pre-wrap;@php
 echo $lang_slug === 'html' ? 'display:none;' : ''; 
@endphp">
                            @if ($runs_live)&gt; {{ t('Tekan "Jalankan Kod" untuk lihat hasilnya di sini.', 'Press "Run Code" to see the result here.') }}@else&gt; {{ t('Output akan dipaparkan di sini selepas API pelaksana kod disambungkan.', 'Output will be displayed here once the code execution API is connected.') }}@endif
                        </div>
                        <iframe id="tryitPreviewFrame" sandbox="allow-scripts" style="width:100%;min-height:220px;border-radius:10px;border:1px solid #dee2e6;background:white;@php
 echo $lang_slug === 'html' ? '' : 'display:none;'; 
@endphp"></iframe>
                        <iframe id="tryitJsRunner" sandbox="allow-scripts" style="display:none;"></iframe>
                    </div>
                </div>
                <form method="POST" action="{{ route('student.chapter', $chapter_id) }}" class="mt-3">
                    @csrf
                    <button type="submit" name="mark_try_it" class="btn btn-outline-primary btn-sm" {!! $progress['try_it_status']==='completed'?'disabled':'' !!}>
                        {!! $progress['try_it_status']==='completed' ? '✅ ' . t('Aktiviti Selesai', 'Activity Complete') : t('Tandakan Selesai', 'Mark as Complete') !!}
                    </button>
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

                <form method="POST" action="{{ route('student.chapter', $chapter_id) }}" class="exercise-form">
                    @csrf
                    <input type="hidden" name="exercise_id" value="{!! $ex['id'] !!}">
                    <textarea name="answer_code" class="form-control mb-2" rows="4" required placeholder="{{ t('Tulis jawapan/kod anda di sini...', 'Write your answer/code here...') }}" style="font-family:monospace;"></textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_exercise" class="btn btn-primary btn-sm" disabled title="{{ t('Tulis jawapan dahulu', 'Write your answer first') }}">{{ t('Hantar Jawapan', 'Submit Answer') }} (+{!! $ex['points'] !!} XP)</button>
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
@endif
<script>
const TRYIT_LANG = {!! json_encode($lang_slug) !!};
let pyodideReadyPromise = null;

// UI text (server-rendered, translated) used by the try-it runner below — execution logic itself is untouched.
const TRYIT_I18N = {
    notSupported: {!! json_encode(t('> Pelaksana kod sebenar belum disokong untuk bahasa ini dalam pelayar.', '> Real code execution is not supported for this language in the browser.')) !!},
    running: {!! json_encode(t('Menjalankan kod...', 'Running code...')) !!},
    errorPrefix: {!! json_encode(t('Ralat: ', 'Error: ')) !!},
    warnPrefix: {!! json_encode(t('Amaran: ', 'Warning: ')) !!},
    noOutputPrint: {!! json_encode(t('(Tiada output - guna print() untuk papar sesuatu)', '(No output - use print() to display something)')) !!},
    pyodideLoadFail: {!! json_encode(t('Gagal memuatkan pelaksana Python: ', 'Failed to load the Python runtime: ')) !!},
    noOutputConsoleLog: {!! json_encode(t('(Tiada output - guna console.log() untuk papar sesuatu)', '(No output - use console.log() to display something)')) !!}
};

function runTryItCode() {
    if (TRYIT_LANG === 'python') runPythonCode();
    else if (TRYIT_LANG === 'javascript') runJsCode();
    else if (TRYIT_LANG === 'html') runHtmlPreview();
    else {
        const out = document.getElementById('tryitOutput');
        if (out) out.textContent = TRYIT_I18N.notSupported;
    }
}

async function runPythonCode() {
    const code = document.getElementById('tryitEditor').value;
    const outEl = document.getElementById('tryitOutput');
    const runBtn = document.getElementById('tryitRunBtn');
    const loadingNote = document.getElementById('tryitLoadingNote');

    runBtn.disabled = true;
    try {
        if (!pyodideReadyPromise) {
            loadingNote.style.display = 'inline';
            pyodideReadyPromise = loadPyodide();
        }
        outEl.textContent = TRYIT_I18N.running;
        const pyodide = await pyodideReadyPromise;
        loadingNote.style.display = 'none';

        let buffer = '';
        pyodide.setStdout({ batched: (s) => { buffer += s + '\n'; } });
        pyodide.setStderr({ batched: (s) => { buffer += s + '\n'; } });

        try {
            await pyodide.runPythonAsync(code);
        } catch (err) {
            buffer += TRYIT_I18N.errorPrefix + (err.message || err);
        }
        outEl.textContent = buffer || TRYIT_I18N.noOutputPrint;
    } catch (err) {
        loadingNote.style.display = 'none';
        outEl.textContent = TRYIT_I18N.pyodideLoadFail + (err.message || err);
    } finally {
        runBtn.disabled = false;
    }
}

function runJsCode() {
    const code = document.getElementById('tryitEditor').value.replace(/<\/script/gi, '<\\/script');
    const outEl = document.getElementById('tryitOutput');
    const frame = document.getElementById('tryitJsRunner');
    outEl.textContent = TRYIT_I18N.running;

    const jsErrorPrefix = JSON.stringify(TRYIT_I18N.errorPrefix);
    const jsWarnPrefix = JSON.stringify(TRYIT_I18N.warnPrefix);
    const doc = '<script>' +
        'const __logs = [];' +
        'function __fmt(a) { try { return typeof a === "object" ? JSON.stringify(a) : String(a); } catch (e) { return String(a); } }' +
        'console.log = function() { __logs.push(Array.prototype.map.call(arguments, __fmt).join(" ")); };' +
        'console.error = function() { __logs.push(' + jsErrorPrefix + ' + Array.prototype.map.call(arguments, __fmt).join(" ")); };' +
        'console.warn = function() { __logs.push(' + jsWarnPrefix + ' + Array.prototype.map.call(arguments, __fmt).join(" ")); };' +
        'window.onerror = function(msg) { __logs.push(' + jsErrorPrefix + ' + msg); parent.postMessage({ type: "tryitJsOutput", logs: __logs }, "*"); };' +
        'try {' + code + '} catch (e) { __logs.push(' + jsErrorPrefix + ' + e.message); }' +
        'parent.postMessage({ type: "tryitJsOutput", logs: __logs }, "*");' +
        '<\/script>';
    frame.srcdoc = doc;
}

window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'tryitJsOutput') {
        const outEl = document.getElementById('tryitOutput');
        outEl.textContent = e.data.logs.length ? e.data.logs.join('\n') : TRYIT_I18N.noOutputConsoleLog;
    }
});

function runHtmlPreview() {
    const code = document.getElementById('tryitEditor').value;
    document.getElementById('tryitPreviewFrame').srcdoc = code;
}
@if ($lang_slug === 'html')
document.addEventListener('DOMContentLoaded', runHtmlPreview);
@endif
</script>

<script>
// Butang "Hantar Jawapan" latihan hanya aktif bila jawapan tidak kosong
document.querySelectorAll('.exercise-form').forEach(function (form) {
    const answer = form.querySelector('textarea[name="answer_code"]');
    const submitBtn = form.querySelector('button[name="submit_exercise"]');
    const sync = function () { submitBtn.disabled = answer.value.trim() === ''; };
    answer.addEventListener('input', sync);
    sync();
});
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
