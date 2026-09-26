<style>
    .report-card { max-width: 720px; margin: 0 auto; }
    .cat-pill-select { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: var(--isep-sp-4); }
    .cat-pill-select input { display: none; }
    .cat-pill-select label {
        padding: 10px 16px; border-radius: var(--isep-r-lg); border: 2px solid var(--isep-border, #e5e7eb);
        cursor: pointer; font-size: var(--isep-fs-sm); font-weight: 600; color: var(--isep-text-muted);
        transition: all var(--isep-duration) var(--isep-ease);
    }
    .cat-pill-select input:checked + label {
        border-color: var(--isep-primary); background: rgba(19,49,92,0.08); color: var(--isep-primary);
    }
    .report-status-badge { padding: 4px 12px; border-radius: 999px; font-size: var(--isep-fs-xs); font-weight: 700; }
    .report-status-badge.new { background: rgba(107,122,143,0.16); color: #4A5A70; }
    .report-status-badge.in_review { background: rgba(212,175,55,0.2); color: #8a6d1a; }
    .report-status-badge.resolved { background: rgba(40,167,69,0.14); color: #1e7e34; }
    .my-report-row { padding: var(--isep-sp-4); border-bottom: 1px solid var(--isep-border, #e9ecef); }
    .my-report-row:last-child { border-bottom: none; }
    .my-report-row .thumb { width: 60px; height: 60px; border-radius: var(--isep-r-md); object-fit: cover; flex-shrink: 0; }
</style>

<div class="topbar">
    <a href="{{ $back_url }}" class="back-link small text-white text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Kembali ke Dashboard', 'Back to Dashboard') }}</a>
    <h3 class="fw-bold mt-2 mb-0 text-white"><i class="fas fa-comment-dots me-2"></i>{!! t('Laporan & Maklum Balas', 'Reports & Feedback') !!}</h3>
    <p class="mb-0 text-white opacity-75">{{ t('Laporkan bug, beri cadangan, atau kongsi maklum balas tentang kursus.', 'Report a bug, suggest an idea, or share feedback about a course.') }}</p>
</div>

<div class="container-fluid p-4">
    <div class="report-card">
        @if ($error)<div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>@endif
        @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

        <div class="card-modern p-4 mb-4">
            <h5 class="fw-bold mb-3">{{ t('Hantar Laporan Baharu', 'Submit a New Report') }}</h5>
            <form method="POST" action="{{ url()->current() }}" enctype="multipart/form-data">
                @csrf

                <label class="form-label fw-semibold small">{{ t('Kategori', 'Category') }}</label>
                <div class="cat-pill-select">
                    @foreach ($category_labels as $key => $label)
                    <input type="radio" name="category" id="cat_{!! $key !!}" value="{!! $key !!}" {!! $key === 'bug' ? 'checked' : '' !!}>
                    <label for="cat_{!! $key !!}">{!! $label !!}</label>
                    @endforeach
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">{{ t('Tajuk', 'Title') }}</label>
                    <input type="text" name="title" class="form-control" maxlength="200" required placeholder="{{ t('Ringkaskan isu/maklum balas anda', 'Summarize your issue/feedback') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">{{ t('Penerangan', 'Description') }}</label>
                    <textarea name="description" class="form-control" rows="5" required placeholder="{{ t('Terangkan dengan lebih lanjut...', 'Describe in more detail...') }}"></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small">{{ t('Lampirkan Screenshot (pilihan)', 'Attach Screenshot (optional)') }}</label>
                    <input type="file" name="screenshot" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                    <small class="text-muted">{{ t('JPG, PNG, GIF atau WEBP. Maksimum 10MB.', 'JPG, PNG, GIF or WEBP. Maximum 10MB.') }}</small>
                </div>

                <button type="submit" name="submit_report" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-2"></i>{{ t('Hantar Laporan', 'Submit Report') }}</button>
            </form>
        </div>

        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">{{ t('Laporan Saya', 'My Reports') }}</h5>
            @if (count($my_reports) === 0)
            <div class="isep-empty py-4">
                <div class="isep-empty-icon"><i class="fas fa-inbox"></i></div>
                <div class="isep-empty-title">{{ t('Belum ada laporan', 'No reports yet') }}</div>
                <div class="isep-empty-sub">{{ t('Laporan yang anda hantar akan dipaparkan di sini.', 'Reports you submit will appear here.') }}</div>
            </div>
            @else
@foreach ($my_reports as $r)
            <div class="my-report-row d-flex align-items-start gap-3">
                @if ($r['screenshot_path'])
                <img src="{{ asset($r['screenshot_path']) }}" class="thumb" alt="">
                @else
                <div class="thumb d-flex align-items-center justify-content-center" style="background:var(--isep-bg);"><i class="fas fa-file-alt text-muted"></i></div>
                @endif
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div class="fw-semibold">{{ $r['title'] }}</div>
                        <span class="report-status-badge {!! $r['status'] !!}">{!! $status_labels[$r['status']] !!}</span>
                    </div>
                    <small class="text-muted d-block mb-1">{!! $category_labels[$r['category']] !!} · {!! date('d M Y, h:i A', strtotime($r['created_at'])) !!}</small>
                    <p class="small mb-1">{!! nl2br(e($r['description'])) !!}</p>
                    @if ($r['admin_notes'])
                    <div class="small p-2 mt-2" style="background:var(--isep-bg); border-radius:var(--isep-r-md); border-left:3px solid var(--isep-secondary);">
                        <strong>{{ t('Respons Admin', 'Admin Response') }}:</strong> {!! nl2br(e($r['admin_notes'])) !!}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
@endif
        </div>
    </div>
</div>
