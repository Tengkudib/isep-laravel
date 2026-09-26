@extends('layouts.staff')

@section('title'){{ t('Laporan - iSEP Lecturer', 'Reports - iSEP Lecturer') }}
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 24px 24px; }
        .card-modern { margin-bottom:20px; }
        .bar-track { background:#e9ecef; border-radius:var(--isep-r-md); height:14px; overflow:hidden; }
        .bar-fill { background:linear-gradient(90deg,#0d6efd,#6d28d9); height:100%; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <a href="{{ route('lecturer.dashboard') }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Dashboard Lecturer', 'Lecturer Dashboard') }}</a>
    <h4 class="fw-bold mt-1 mb-0"><i class="fas fa-chart-bar me-2"></i>{{ t('Laporan Statistik Kursus', 'Course Statistics Report') }}</h4>
</div>

<div class="container-fluid p-4">

    <!-- Student Performance Report -->
    <div class="card-modern p-4">
        <h5 class="fw-bold mb-3">{{ t('Laporan Prestasi Pelajar', 'Student Performance Report') }}</h5>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ t('Nama', 'Name') }}</th><th>ID</th><th>{{ t('Kursus', 'Course') }}</th><th>{{ t('Bab Selesai', 'Chapters Done') }}</th><th>{{ t('Purata Kuiz', 'Avg Quiz') }}</th><th>{{ t('Sijil', 'Certificate') }}</th><th>{{ t('Tarikh Daftar', 'Enrolled Date') }}</th></tr></thead>
                <tbody>
                @if (count($student_report) === 0)
                <tr><td colspan="7">
                    <div class="isep-empty py-3">
                        <div class="isep-empty-icon"><i class="fas fa-user-graduate"></i></div>
                        <div class="isep-empty-title">{{ t('Belum ada pelajar berdaftar', 'No students enrolled yet') }}</div>
                        <div class="isep-empty-sub">{{ t('Pelajar yang mendaftar kursus anda akan dipaparkan di sini.', 'Students who enroll in your courses will appear here.') }}</div>
                    </div>
                </td></tr>
                @endif
                @foreach ($student_report as $r)
                <tr>
                    <td class="fw-semibold">{{ $r['name'] }}</td>
                    <td>{{ $r['student_id'] ?? '-' }}</td>
                    <td>{{ $r['lang_name'] }}</td>
                    <td>{!! $r['chapters_done'] !!} / {!! $r['total_chapters'] !!}</td>
                    <td>{!! round($r['quiz_avg'] ?? 0) !!}%</td>
                    <td>{!! $r['has_certificate'] > 0 ? '🎓 ' . t('Ada', 'Yes') : '—' !!}</td>
                    <td><small class="text-muted">{!! date('d M Y', strtotime($r['enrolled_at'])) !!}</small></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <!-- Difficult chapters -->
        <div class="col-lg-6">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i>{{ t('Bab Paling Sukar', 'Most Difficult Chapters') }}</h5>
                @if (count($difficult_chapters) === 0)
                <div class="isep-empty py-3">
                    <div class="isep-empty-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="isep-empty-title">{{ t('Belum ada data kuiz', 'No quiz data yet') }}</div>
                    <div class="isep-empty-sub">{{ t('Statistik akan muncul apabila pelajar mula menjawab kuiz.', 'Statistics will appear once students start answering quizzes.') }}</div>
                </div>
                @endif
                @foreach ($difficult_chapters as $d)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">{{ $d['title'] }} <span class="text-muted">({{ $d['lang_name'] }})</span></span>
                        <span>{!! round($d['avg_pct']) !!}%</span>
                    </div>
                    <div class="bar-track"><div class="bar-fill" style="width:{!! round($d['avg_pct']) !!}%; background:#dc3545;"></div></div>
                    <small class="text-muted">{!! $d['attempts'] !!} {{ t('percubaan kuiz', 'quiz attempts') }}</small>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Funnel -->
        <div class="col-lg-6">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-filter me-2"></i>{{ t('Kadar Penyelesaian Bab', 'Chapter Completion Rate') }}</h5>
                @foreach ($funnel as $f)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ t('Bab', 'Chapter') }} {!! $f['chapter_number'] !!}: {{ $f['title'] }}</span>
                        <span>{!! $f['completed_count'] !!} {{ t('pelajar', 'students') }}</span>
                    </div>
                    <div class="bar-track"><div class="bar-fill" style="width:{!! min(100, $f['completed_count'] * 10) !!}%"></div></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
