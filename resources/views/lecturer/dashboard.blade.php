@extends('layouts.staff')

@section('title'){{ t('Dashboard Lecturer - iSEP', 'Lecturer Dashboard - iSEP') }}
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 24px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
        .stat-card { padding:20px; text-align:center; }
        .stat-card h3 { font-weight:700; margin-bottom:2px; }
        .course-row { padding:16px; border-radius:var(--isep-r-lg); transition: background var(--isep-duration) var(--isep-ease); }
        .course-row:hover { background:#FAF7F0; }
        .course-row:hover .fw-semibold, .course-row:hover .text-muted { color:#0B2545 !important; }
        .progress { height:6px; border-radius:var(--isep-r-lg); }
    </style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <h4 class="fw-bold mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>{{ t('Dashboard Lecturer', 'Lecturer Dashboard') }}</h4>
        <small>{{ t('Selamat kembali', 'Welcome back') }}, {{ $lecturer['name'] }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('lecturer.reports') }}" class="btn btn-light text-primary fw-semibold"><i class="fas fa-chart-bar me-1"></i>{{ t('Laporan Penuh', 'Full Report') }}</a>
        <a href="{{ route('logout') }}" data-logout class="btn btn-outline-light"><i class="fas fa-sign-out-alt me-1"></i>{{ t('Log Keluar', 'Log Out') }}</a>
    </div>
</div>

<div class="container-fluid p-4">
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-primary">{!! $stats['courses'] !!}</h3><small class="text-muted">{{ t('Kursus Diajar', 'Courses Taught') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-info">{!! $stats['total_chapters'] !!}</h3><small class="text-muted">{{ t('Jumlah Bab', 'Total Chapters') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-success">{!! $stats['enrolled_students'] !!}</h3><small class="text-muted">{{ t('Pelajar Berdaftar', 'Enrolled Students') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-warning">{!! $stats['avg_quiz_score'] !!}%</h3><small class="text-muted">{{ t('Purata Skor Kuiz', 'Avg Quiz Score') }}</small></div></div>
    </div>

    <div class="card-modern p-4">
        <h5 class="fw-bold mb-3">{{ t('Kursus Saya', 'My Courses') }}</h5>
        @if (count($my_languages) === 0)
        <div class="isep-empty">
            <div class="isep-empty-icon"><i class="fas fa-chalkboard"></i></div>
            <div class="isep-empty-title">{{ t('Belum ada kursus ditetapkan', 'No courses assigned yet') }}</div>
            <div class="isep-empty-sub">{{ t('Sila hubungi admin untuk menetapkan kursus kepada anda.', 'Please contact the admin to assign courses to you.') }}</div>
        </div>
        @endif
        @foreach ($course_progress as $cp)
@php
 $lang = $cp['lang'];
@endphp
        <div class="course-row border-bottom d-flex align-items-center gap-3">
            <div style="width:44px;height:44px;border-radius:var(--isep-r-lg);background:{{ $lang['color_theme'] }}22;color:{{ $lang['color_theme'] }};display:flex;align-items:center;justify-content:center;">
                <i class="{{ $lang['icon'] }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ $lang['name'] }}</div>
                <small class="text-muted">{!! $cp['enrolled'] !!} {{ t('pelajar berdaftar', 'students enrolled') }} · {!! $cp['completed'] !!} {{ t('selesai kursus', 'completed course') }} · {{ t('Purata kuiz', 'Avg quiz') }} {!! $cp['avg_score'] !!}%</small>
            </div>
            <a href="{{ route('manage.chapters', $lang['id']) }}" class="btn btn-sm btn-outline-primary">{{ t('Urus Kandungan', 'Manage Content') }}</a>
        </div>
        @endforeach
    </div>
</div>
@endsection
