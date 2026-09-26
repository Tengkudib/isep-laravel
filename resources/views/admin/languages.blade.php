@extends('layouts.staff')

@section('title'){{ t('Urus Bahasa - iSEP Admin', 'Manage Languages - iSEP Admin') }}
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); }
        .lang-row { display:flex; align-items:center; gap:14px; padding:14px; border-radius:var(--isep-r-lg); }
        .lang-row:hover { background:#FAF7F0; }
        .lang-row:hover .fw-semibold, .lang-row:hover .text-muted { color:#0B2545 !important; }
        .icon-preset-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:8px; }
        .icon-preset-option input { position:absolute; opacity:0; pointer-events:none; }
        .icon-preset-box {
            display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px;
            padding:10px 4px; border-radius:var(--isep-r-md); border:2px solid var(--isep-border,#e5e7eb);
            cursor:pointer; font-size:0.72rem; text-align:center; color:var(--isep-text-muted); transition:all var(--isep-duration) var(--isep-ease);
        }
        .icon-preset-box i { font-size:1.3rem; }
        .icon-preset-option input:checked + .icon-preset-box { border-color:#13315C; background:rgba(19,49,92,0.08); color:#13315C; font-weight:600; }
        .edit-panel { background:var(--isep-bg); border-radius:var(--isep-r-lg); padding:16px; margin-top:10px; width:100%; }
    </style>
@endpush

@section('content')
<div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Dashboard', 'Dashboard') }}</a>
        <h4 class="fw-bold mt-1 mb-0">{{ t('Urus Bahasa Pengaturcaraan', 'Manage Programming Languages') }}</h4>
    </div>
</div>

<div class="container-fluid p-4">
    @if ($error)<div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>@endif
    @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3">{{ t('Senarai Bahasa', 'Language List') }}</h5>
                @foreach ($languages as $lang)
                <div class="lang-row border-bottom flex-wrap">
                    <div style="width:44px;height:44px;border-radius:12px;background:{{ $lang['color_theme'] }}22;color:{{ $lang['color_theme'] }};display:flex;align-items:center;justify-content:center;">
                        <i class="{{ $lang['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $lang['name'] }}</div>
                        <small class="text-muted">{!! $lang['chapter_count'] !!} {{ t('bab', 'chapters') }} · {{ $lang['difficulty'] }}</small>
                        <div class="small mt-1 text-muted">
                            <i class="fas fa-chalkboard-teacher me-1"></i>
                            {!! $lang['lecturer_names'] ? htmlspecialchars($lang['lecturer_names']) : '<span class="fst-italic">' . t('Tiada lecturer', 'No lecturer') . '</span>' !!}
                        </div>
                    </div>
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="fas fa-user-edit me-1"></i>{{ t('Lecturer', 'Lecturers') }}</button>
                        <div class="dropdown-menu p-3" style="min-width:230px;">
                            <form method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{!! $lang['id'] !!}">
                                <p class="small text-muted mb-2">{{ t('Pilih satu atau lebih lecturer.', 'Select one or more lecturers.') }}</p>
                                @if (count($all_lecturers) === 0)
                                <p class="small text-muted fst-italic">{{ t('Tiada lecturer berdaftar.', 'No lecturers registered.') }}</p>
                                @endif
                                @foreach ($all_lecturers as $lc)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="lecturer_ids[]" value="{!! $lc['id'] !!}" id="lec_{!! $lang['id'] !!}_{!! $lc['id'] !!}"
                                        {!! in_array($lc['id'], $lang_lecturer_map[$lang['id']] ?? []) ? 'checked' : '' !!}>
                                    <label class="form-check-label" for="lec_{!! $lang['id'] !!}_{!! $lc['id'] !!}">{{ $lc['name'] }}</label>
                                </div>
                                @endforeach
                                <button type="submit" name="update_lecturers" class="btn btn-primary btn-sm w-100 mt-2">{{ t('Simpan', 'Save') }}</button>
                            </form>
                        </div>
                    </div>
                    <a href="{{ route('manage.chapters', $lang['id']) }}" class="btn btn-sm btn-outline-primary">{{ t('Urus Bab', 'Manage Chapters') }}</a>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editPanel{!! $lang['id'] !!}"><i class="fas fa-pen me-1"></i>{{ t('Edit', 'Edit') }}</button>
                    <form method="POST" onsubmit="return confirm('{!! t('Padam bahasa ini? Semua bab & data berkaitan turut dipadam.', 'Delete this language? All chapters & related data will also be deleted.') !!}');">
                        @csrf
                        <input type="hidden" name="id" value="{!! $lang['id'] !!}">
                        <button type="submit" name="delete_language" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>

                    <div class="collapse w-100" id="editPanel{!! $lang['id'] !!}">
                        <div class="edit-panel">
                            <form method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{!! $lang['id'] !!}">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">{{ t('Nama', 'Name') }}</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $lang['name'] }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">{{ t('Slug', 'Slug') }}</label>
                                        <input type="text" name="slug" class="form-control form-control-sm" value="{{ $lang['slug'] }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">{{ t('Penerangan', 'Description') }}</label>
                                        <textarea name="description" class="form-control form-control-sm" rows="2">{{ $lang['description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">{{ t('Pilih Ikon', 'Choose Icon') }}</label>
                                        @include('admin.partials.icon_picker', ['group_id' => 'edit' . $lang['id'], 'selected_icon' => $lang['icon']])
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">{{ t('Tahap Kesukaran', 'Difficulty') }}</label>
                                        <select name="difficulty" class="form-select form-select-sm">
                                            @foreach (['Beginner', 'Intermediate', 'Advanced'] as $diff)
                                            <option {!! $lang['difficulty'] === $diff ? 'selected' : '' !!}>{!! $diff !!}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">{{ t('Warna Tema', 'Theme Color') }}</label>
                                        <input type="color" name="color_theme" class="form-control form-control-color" value="{{ $lang['color_theme'] ?: '#0d6efd' }}">
                                    </div>
                                </div>
                                <button type="submit" name="edit_language" class="btn btn-primary btn-sm mt-3">{{ t('Simpan Perubahan', 'Save Changes') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3">{{ t('Tambah Bahasa Baharu', 'Add New Language') }}</h5>
                <form method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Nama', 'Name') }}</label>
                        <input type="text" name="name" class="form-control" placeholder="{{ t('Contoh: HTML', 'Example: HTML') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Slug (untuk URL)', 'Slug (for URL)') }}</label>
                        <input type="text" name="slug" class="form-control" placeholder="{{ t('contoh: html', 'example: html') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Penerangan', 'Description') }}</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Pilih Ikon', 'Choose Icon') }}</label>
                        @include('admin.partials.icon_picker', ['group_id' => 'new', 'selected_icon' => 'fa-solid fa-code'])
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Tahap Kesukaran', 'Difficulty Level') }}</label>
                        <select name="difficulty" class="form-select">
                            <option>Beginner</option><option>Intermediate</option><option>Advanced</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Warna Tema (hex)', 'Theme Color (hex)') }}</label>
                        <input type="color" name="color_theme" class="form-control form-control-color" value="#0d6efd">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">{{ t('Tetapkan Lecturer', 'Assign Lecturers') }}</label>
                        @if (count($all_lecturers) === 0)
                        <p class="small text-muted fst-italic mb-0">{{ t('Tiada lecturer berdaftar - admin akan urus sendiri.', 'No lecturers registered - admin will manage it.') }}</p>
                        @else
                        <div class="dropdown">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle w-100 text-start" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="fas fa-user-edit me-1"></i>{{ t('Pilih Lecturer', 'Select Lecturers') }}</button>
                            <div class="dropdown-menu p-3 w-100">
                                @foreach ($all_lecturers as $lc)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="lecturer_ids[]" value="{!! $lc['id'] !!}" id="new_lec_{!! $lc['id'] !!}">
                                    <label class="form-check-label" for="new_lec_{!! $lc['id'] !!}">{{ $lc['name'] }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <button type="submit" name="add_language" class="btn btn-primary w-100">{{ t('Tambah Bahasa', 'Add Language') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
