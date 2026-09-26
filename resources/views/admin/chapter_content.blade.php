@extends('layouts.staff')

@section('title'){{ t('Kandungan Bab - iSEP Admin', 'Chapter Content - iSEP Admin') }}
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); margin-bottom:20px; }
        .nav-tabs .nav-link.active { font-weight:600; color:#0d6efd; }
        .item-row { padding:10px; border-radius:var(--isep-r-md); }
        .item-row:hover { background:#FAF7F0; }
        .item-row:hover .fw-semibold, .item-row:hover .text-muted { color:#0B2545 !important; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <a href="{{ route('manage.chapters', $chapter['language_id']) }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Senarai Bab', 'Chapter List') }}</a>
    <h4 class="fw-bold mt-1 mb-0">{{ $chapter['lang_name'] }} — {{ t('Bab', 'Chapter') }} {!! $chapter['chapter_number'] !!}: {{ $chapter['title'] }}</h4>
</div>

<div class="container-fluid p-4">
    @if ($error)<div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>@endif
    @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-notes">📖 {{ t('Nota', 'Notes') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-video">🎥 Video</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-code">💻 {{ t('Contoh Kod', 'Code Example') }}</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-exercise">✍️ {{ t('Latihan', 'Exercises') }} ({!! count($exercises) !!})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-quiz">🧠 {{ t('Kuiz', 'Quiz') }} ({!! count($quizzes) !!})</button></li>
    </ul>

    <div class="tab-content">

        <!-- NOTES / TIPS -->
        <div class="tab-pane fade show active" id="t-notes">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{!! t('Nota & Tip Sedia Ada', 'Existing Notes & Tips') !!}</h6>
                        @foreach ($content_list as $c)
@continue(!in_array($c['content_type'], ['notes','tip','common_error']))
                        <div class="item-row border-bottom d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-secondary mb-1">{!! $c['content_type'] !!}</span>
                                <div class="fw-semibold small">{{ $c['title'] }}</div>
                                <div class="small text-muted">{!! mb_strimwidth(strip_tags($c['content'] ?? ''), 0, 100, '...') !!}</div>
                                @if (!empty($c['file_path']))
                                <a href="{{ asset($c['file_path']) }}" target="_blank" class="small">
                                    <i class="fas fa-paperclip me-1"></i>{{ $c['file_name'] }}
                                    <span class="badge bg-light text-dark border">{!! strtoupper($c['file_type']) !!}</span>
                                </a>
                                @endif
                            </div>
                            <form method="POST">@csrf<input type="hidden" name="id" value="{!! $c['id'] !!}"><button name="delete_content" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Tambah Nota / Tip', 'Add Note / Tip') }}</h6>
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <select name="content_type" class="form-select mb-2">
                                <option value="notes">📖 {{ t('Nota Konsep', 'Concept Notes') }}</option>
                                <option value="tip">💡 {{ t('Tip Pengaturcaraan', 'Programming Tip') }}</option>
                                <option value="common_error">⚠️ {{ t('Kesilapan Biasa', 'Common Error') }}</option>
                            </select>
                            <input type="text" name="title" class="form-control mb-2" placeholder="{{ t('Tajuk', 'Title') }}">
                            <textarea name="content" class="form-control mb-2" rows="4" placeholder="{{ t('Kandungan (boleh guna HTML asas untuk nota)', 'Content (basic HTML allowed for notes)') }}"></textarea>
                            <label class="form-label small fw-semibold">{{ t('Lampiran Fail (pilihan)', 'File Attachment (optional)') }}</label>
                            <input type="file" name="note_file" class="form-control mb-1" accept=".pdf,.txt,.pptx,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp">
                            <small class="text-muted d-block mb-2">{{ t('Format dibenarkan: PDF, TXT, PPTX, DOC, DOCX, JPG, PNG, GIF, WEBP. Maks 100MB.', 'Allowed formats: PDF, TXT, PPTX, DOC, DOCX, JPG, PNG, GIF, WEBP. Max 100MB.') }}</small>
                            <button type="submit" name="add_content" class="btn btn-primary w-100">{{ t('Tambah', 'Add') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIDEO -->
        <div class="tab-pane fade" id="t-video">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Video Sedia Ada', 'Existing Videos') }}</h6>
                        @foreach ($content_list as $c)
@continue($c['content_type'] !== 'video')
                        <div class="item-row border-bottom d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small">{{ $c['title'] }}</div>
                                <div class="small text-muted">{{ $c['video_url'] }} · {{ $c['video_duration'] }}</div>
                            </div>
                            <form method="POST">@csrf<input type="hidden" name="id" value="{!! $c['id'] !!}"><button name="delete_content" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Tambah Video', 'Add Video') }}</h6>
                        <form method="POST">
                            @csrf
                            <input type="text" name="video_title" class="form-control mb-2" placeholder="{{ t('Tajuk Video', 'Video Title') }}" required>
                            <input type="url" name="video_url" class="form-control mb-2" placeholder="{{ t('URL (contoh: https://youtube.com/embed/...)', 'URL (e.g.: https://youtube.com/embed/...)') }}" required>
                            <input type="text" name="video_duration" class="form-control mb-2" placeholder="{{ t('Durasi (contoh: 12:30)', 'Duration (e.g.: 12:30)') }}">
                            <button type="submit" name="add_video" class="btn btn-primary w-100">{{ t('Tambah Video', 'Add Video') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- CODE EXAMPLE -->
        <div class="tab-pane fade" id="t-code">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Contoh Kod Sedia Ada', 'Existing Code Examples') }}</h6>
                        @foreach ($content_list as $c)
@continue($c['content_type'] !== 'code_example')
                        <div class="item-row border-bottom d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small">{{ $c['title'] }}</div>
                                <pre class="small bg-dark text-light p-2 rounded">{{ mb_strimwidth($c['code_example'] ?? '', 0, 150, '...') }}</pre>
                            </div>
                            <form method="POST">@csrf<input type="hidden" name="id" value="{!! $c['id'] !!}"><button name="delete_content" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Tambah Contoh Kod', 'Add Code Example') }}</h6>
                        <form method="POST">
                            @csrf
                            <input type="text" name="code_title" class="form-control mb-2" placeholder="{{ t('Tajuk', 'Title') }}" required>
                            <textarea name="code_example" class="form-control mb-2" rows="5" style="font-family:monospace;" placeholder="{{ t('Tulis kod di sini...', 'Write code here...') }}" required></textarea>
                            <button type="submit" name="add_code" class="btn btn-primary w-100">{{ t('Tambah Kod', 'Add Code') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- EXERCISE -->
        <div class="tab-pane fade" id="t-exercise">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Latihan Sedia Ada', 'Existing Exercises') }}</h6>
                        @foreach ($exercises as $ex)
                        <div class="item-row border-bottom d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-info mb-1">{!! $ex['difficulty'] !!}</span>
                                <div class="fw-semibold small">{{ $ex['question'] }}</div>
                            </div>
                            <form method="POST">@csrf<input type="hidden" name="id" value="{!! $ex['id'] !!}"><button name="delete_exercise" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Tambah Latihan', 'Add Exercise') }}</h6>
                        <form method="POST">
                            @csrf
                            <textarea name="ex_question" class="form-control mb-2" rows="2" placeholder="{{ t('Soalan', 'Question') }}" required></textarea>
                            <textarea name="ex_instruction" class="form-control mb-2" rows="2" placeholder="{{ t('Arahan', 'Instruction') }}"></textarea>
                            <select name="ex_difficulty" class="form-select mb-2">
                                <option>Beginner</option><option>Intermediate</option><option>Advanced</option>
                            </select>
                            <input type="text" name="ex_sample_input" class="form-control mb-2" placeholder="{{ t('Input Sampel (opsyenal)', 'Sample Input (optional)') }}">
                            <input type="text" name="ex_expected_output" class="form-control mb-2" placeholder="{{ t('Output Dijangka', 'Expected Output') }}">
                            <textarea name="ex_hint" class="form-control mb-2" rows="2" placeholder="{{ t('Petunjuk', 'Hint') }}"></textarea>
                            <input type="number" name="ex_points" class="form-control mb-2" value="20" placeholder="{{ t('Mata XP', 'XP Points') }}">
                            <button type="submit" name="add_exercise" class="btn btn-primary w-100">{{ t('Tambah Latihan', 'Add Exercise') }}</button>
                        </form>
                    </div>

                    <div class="card-modern p-4 mt-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-file-csv me-2"></i>{{ t('Tambah Banyak Latihan (CSV)', 'Bulk Add Exercises (CSV)') }}</h6>
                        <p class="small text-muted mb-2">{{ t('Muat naik satu fail CSV untuk tambah banyak latihan sekali gus.', 'Upload one CSV file to add many exercises at once.') }}</p>
                        <a href="{{ route('manage.csv_template') }}" class="small d-inline-block mb-3"><i class="fas fa-download me-1"></i>{{ t('Muat Turun Templat CSV', 'Download CSV Template') }}</a>
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="exercise_csv" class="form-control mb-2" accept=".csv" required>
                            <small class="text-muted d-block mb-2">{{ t('Lajur: question, instruction, difficulty, sample_input, expected_output, hint, points', 'Columns: question, instruction, difficulty, sample_input, expected_output, hint, points') }}</small>
                            <button type="submit" name="bulk_add_exercises" class="btn btn-outline-primary w-100"><i class="fas fa-upload me-2"></i>{!! t('Muat Naik & Tambah', 'Upload & Add') !!}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUIZ -->
        <div class="tab-pane fade" id="t-quiz">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Soalan Kuiz Sedia Ada', 'Existing Quiz Questions') }}</h6>
                        @foreach ($quizzes as $q)
                        <div class="item-row border-bottom d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small">{{ $q['question'] }}</div>
                                <small class="text-success">{{ t('Jawapan', 'Answer') }}: {{ $q['correct_answer'] }}</small>
                            </div>
                            <form method="POST">@csrf<input type="hidden" name="id" value="{!! $q['id'] !!}"><button name="delete_quiz" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-modern p-4">
                        <h6 class="fw-bold mb-3">{{ t('Tambah Soalan Kuiz', 'Add Quiz Question') }}</h6>
                        <form method="POST">
                            @csrf
                            <textarea name="quiz_question" class="form-control mb-2" rows="2" placeholder="{{ t('Soalan', 'Question') }}" required></textarea>
                            <select name="quiz_type" class="form-select mb-2" onchange="toggleQuizFields(this.value)">
                                <option value="multiple_choice">{{ t('Pilihan Berganda', 'Multiple Choice') }}</option>
                                <option value="true_false">{{ t('Betul/Salah', 'True/False') }}</option>
                                <option value="fill_blank">{{ t('Isi Tempat Kosong', 'Fill in the Blank') }}</option>
                            </select>
                            <div id="mc-options">
                                <input type="text" name="option_a" class="form-control mb-2" placeholder="{{ t('Pilihan A', 'Option A') }}">
                                <input type="text" name="option_b" class="form-control mb-2" placeholder="{{ t('Pilihan B', 'Option B') }}">
                                <input type="text" name="option_c" class="form-control mb-2" placeholder="{{ t('Pilihan C', 'Option C') }}">
                                <input type="text" name="option_d" class="form-control mb-2" placeholder="{{ t('Pilihan D', 'Option D') }}">
                            </div>
                            <input type="text" name="correct_answer" class="form-control mb-2" placeholder="{{ t('Jawapan Betul (mesti sepadan dengan salah satu pilihan di atas)', 'Correct Answer (must match one of the options above)') }}" required>
                            <textarea name="explanation" class="form-control mb-2" rows="2" placeholder="{{ t('Penjelasan', 'Explanation') }}"></textarea>
                            <input type="number" name="quiz_points" class="form-control mb-2" value="10" placeholder="{{ t('Mata', 'Points') }}">
                            <button type="submit" name="add_quiz" class="btn btn-primary w-100">{{ t('Tambah Soalan', 'Add Question') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
function toggleQuizFields(type) {
    document.getElementById('mc-options').style.display = (type === 'multiple_choice') ? 'block' : 'none';
}
</script>
@endsection
