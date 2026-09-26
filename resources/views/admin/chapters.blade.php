@extends('layouts.staff')

@section('title'){{ t('Urus Bab', 'Manage Chapters') }} - {{ $language['name'] }} - iSEP Admin
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); }
        .chapter-row { display:flex; align-items:center; gap:14px; padding:14px; border-radius:var(--isep-r-lg); }
        .chapter-row:hover { background:#FAF7F0; }
        .chapter-row:hover .fw-semibold, .chapter-row:hover .text-muted { color:#0B2545 !important; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <a href="{!! $back_url !!}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {!! $back_label !!}</a>
    <h4 class="fw-bold mt-1 mb-0">{{ $language['name'] }} — {{ t('Senarai Bab', 'Chapter List') }}</h4>
</div>

<div class="container-fluid p-4">
    @if ($error)<div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>@endif
    @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3">{{ t('Bab Sedia Ada', 'Existing Chapters') }}</h5>
                @if (count($chapters) === 0)
                    <div class="isep-empty py-3">
                        <div class="isep-empty-icon"><i class="fas fa-book"></i></div>
                        <div class="isep-empty-title">{{ t('Belum ada bab', 'No chapters yet') }}</div>
                        <div class="isep-empty-sub">{{ t('Tambah bab pertama di sebelah kanan untuk mula membina kandungan.', 'Add the first chapter on the right to start building content.') }}</div>
                    </div>
                @endif
                @foreach ($chapters as $c)
                <div class="chapter-row border-bottom">
                    <span class="badge bg-primary">{{ t('Bab', 'Chapter') }} {!! $c['chapter_number'] !!}</span>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $c['title'] }}</div>
                        <small class="text-muted">{{ t('Markah minimum kuiz', 'Minimum quiz score') }}: {!! $c['minimum_quiz_score'] !!}%</small>
                    </div>
                    <a href="{{ route('manage.content', $c['id']) }}" class="btn btn-sm btn-outline-primary">{{ t('Urus Kandungan', 'Manage Content') }}</a>
                    <form method="POST" onsubmit="return confirm('{!! t('Padam bab ini beserta semua kandungan, latihan & kuiz?', 'Delete this chapter along with all content, exercises & quizzes?') !!}');">
                        @csrf
                        <input type="hidden" name="id" value="{!! $c['id'] !!}">
                        <button type="submit" name="delete_chapter" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3">{{ t('Tambah Bab Baharu', 'Add New Chapter') }}</h5>
                <form method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Nombor Bab', 'Chapter Number') }}</label>
                        <input type="number" name="chapter_number" class="form-control" min="1" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Tajuk Bab', 'Chapter Title') }}</label>
                        <input type="text" name="title" class="form-control" placeholder="{{ t('Contoh: Functions', 'Example: Functions') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Objektif Pembelajaran', 'Learning Objective') }}</label>
                        <textarea name="learning_objective" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Markah Kuiz Minimum (%)', 'Minimum Quiz Score (%)') }}</label>
                        <input type="number" name="minimum_quiz_score" class="form-control" value="70" min="0" max="100">
                    </div>
                    <button type="submit" name="add_chapter" class="btn btn-primary w-100">{{ t('Tambah Bab', 'Add Chapter') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
