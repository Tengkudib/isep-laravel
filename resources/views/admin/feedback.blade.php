@extends('layouts.staff')

@section('title'){{ t('Laporan & Maklum Balas', 'Reports & Feedback') }} - iSEP Admin
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); }
        .filter-tab {
            padding: 8px 16px; border-radius: 999px; font-size: 0.85rem; font-weight: 600;
            background: var(--isep-card-bg); color: var(--isep-text-muted); text-decoration: none;
            border: 1px solid var(--isep-border, #e5e7eb); display: inline-block;
        }
        .filter-tab.active { background: var(--isep-primary); color: white; border-color: var(--isep-primary); }
        .report-status-badge { padding: 4px 12px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
        .report-status-badge.new { background: rgba(107,122,143,0.16); color: #4A5A70; }
        .report-status-badge.in_review { background: rgba(212,175,55,0.2); color: #8a6d1a; }
        .report-status-badge.resolved { background: rgba(40,167,69,0.14); color: #1e7e34; }
        .report-item { padding: var(--isep-sp-4); border-radius: var(--isep-r-lg); background: var(--isep-card-bg); box-shadow: var(--isep-shadow-sm); margin-bottom: 14px; }
        .report-thumb { width: 90px; height: 90px; border-radius: var(--isep-r-md); object-fit: cover; cursor: zoom-in; }
    </style>
@endpush

@section('content')
<div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Dashboard', 'Dashboard') }}</a>
        <h4 class="fw-bold mt-1 mb-0"><i class="fas fa-comment-dots me-2"></i>{!! t('Laporan & Maklum Balas', 'Reports & Feedback') !!}</h4>
    </div>
</div>

<div class="container-fluid p-4">
    @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.feedback', ['status' => 'all']) }}" class="filter-tab {!! $filter_status === 'all' ? 'active' : '' !!}">{{ t('Semua', 'All') }} ({!! $total_count !!})</a>
        <a href="{{ route('admin.feedback', ['status' => 'new']) }}" class="filter-tab {!! $filter_status === 'new' ? 'active' : '' !!}">{!! $status_labels['new'] !!} ({!! $count_map['new'] !!})</a>
        <a href="{{ route('admin.feedback', ['status' => 'in_review']) }}" class="filter-tab {!! $filter_status === 'in_review' ? 'active' : '' !!}">{!! $status_labels['in_review'] !!} ({!! $count_map['in_review'] !!})</a>
        <a href="{{ route('admin.feedback', ['status' => 'resolved']) }}" class="filter-tab {!! $filter_status === 'resolved' ? 'active' : '' !!}">{!! $status_labels['resolved'] !!} ({!! $count_map['resolved'] !!})</a>
    </div>

    @if (count($reports) === 0)
    <div class="card-modern p-4">
        <div class="isep-empty py-4">
            <div class="isep-empty-icon"><i class="fas fa-inbox"></i></div>
            <div class="isep-empty-title">{{ t('Tiada laporan', 'No reports') }}</div>
            <div class="isep-empty-sub">{{ t('Tiada laporan sepadan dengan penapis ini.', 'No reports match this filter.') }}</div>
        </div>
    </div>
    @else
@foreach ($reports as $r)
    <div class="report-item">
        <div class="d-flex gap-3">
            @if ($r['screenshot_path'])
            <a href="{{ asset($r['screenshot_path']) }}" target="_blank"><img src="{{ asset($r['screenshot_path']) }}" class="report-thumb" alt=""></a>
            @else
            <div class="report-thumb d-flex align-items-center justify-content-center" style="background:var(--isep-bg);"><i class="fas fa-file-alt text-muted"></i></div>
            @endif
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <span class="badge bg-secondary">{!! $category_labels[$r['category']] !!}</span>
                        <span class="fw-bold ms-1">{{ $r['title'] }}</span>
                    </div>
                    <span class="report-status-badge {!! $r['status'] !!}">{!! $status_labels[$r['status']] !!}</span>
                </div>
                <small class="text-muted d-block mb-2">
                    {{ $r['submitter_name'] }} ({!! ucfirst($r['submitter_role']) !!}) · {!! date('d M Y, h:i A', strtotime($r['created_at'])) !!}
                </small>
                <p class="small mb-2">{!! nl2br(e($r['description'])) !!}</p>

                <form method="POST" class="d-flex flex-wrap align-items-end gap-2">
                    @csrf
                    <input type="hidden" name="report_id" value="{!! $r['id'] !!}">
                    <div>
                        <label class="form-label small mb-1">{{ t('Status', 'Status') }}</label>
                        <select name="status" class="form-select form-select-sm" style="width:160px;">
                            @foreach ($status_labels as $key => $label)
                            <option value="{!! $key !!}" {!! $r['status'] === $key ? 'selected' : '' !!}>{!! $label !!}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width:220px;">
                        <label class="form-label small mb-1">{{ t('Respons Admin (pilihan)', 'Admin Response (optional)') }}</label>
                        <input type="text" name="admin_notes" class="form-control form-control-sm" value="{{ $r['admin_notes'] ?? '' }}" placeholder="{{ t('Nota untuk pengguna...', 'Note for the user...') }}">
                    </div>
                    <button type="submit" name="update_report" class="btn btn-primary btn-sm">{{ t('Simpan', 'Save') }}</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif
</div>
@endsection
