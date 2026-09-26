@extends('layouts.staff')

@section('title'){{ t('Analitik Lecturer & Subjek', 'Lecturer & Subject Analytics') }} - iSEP Admin
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); }
        .rank-badge { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; color:white; flex-shrink:0; }
        .rank-badge.r1 { background:linear-gradient(135deg,#D4AF37,#B8941F); }
        .rank-badge.r2 { background:linear-gradient(135deg,#9CA3AF,#6B7280); }
        .rank-badge.r3 { background:linear-gradient(135deg,#B08D57,#8B6B3D); }
        .rank-badge.rn { background:linear-gradient(135deg,#13315C,#0B2545); }
    </style>
@endpush

@section('content')
<div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-white small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> {{ t('Dashboard', 'Dashboard') }}</a>
        <h4 class="fw-bold mt-1 mb-0"><i class="fas fa-chart-pie me-2"></i>{!! t('Analitik Lecturer & Subjek', 'Lecturer & Subject Analytics') !!}</h4>
    </div>
</div>

<div class="container-fluid p-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-1">{{ t('Lecturer Paling Aktif', 'Most Active Lecturers') }}</h5>
                <p class="small text-muted mb-3">{{ t('Disusun mengikut jumlah aktiviti (latihan + kuiz + nota) yang ditawarkan.', 'Ranked by total activities (exercises + quizzes + notes) offered.') }}</p>
                @if (count($lecturer_stats) === 0)
                <div class="isep-empty py-4">
                    <div class="isep-empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="isep-empty-title">{{ t('Tiada lecturer', 'No lecturers') }}</div>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr>
                            <th></th>
                            <th>{{ t('Lecturer', 'Lecturer') }}</th>
                            <th class="text-center">{{ t('Kursus', 'Courses') }}</th>
                            <th class="text-center">{{ t('Bab', 'Chapters') }}</th>
                            <th class="text-center">{{ t('Aktiviti', 'Activities') }}</th>
                            <th class="text-center">{{ t('Pelajar', 'Students') }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach ($lecturer_stats as $i => $ls)
@php
                            $rank = $i + 1;
                            $rankClass = $rank === 1 ? 'r1' : ($rank === 2 ? 'r2' : ($rank === 3 ? 'r3' : 'rn'));
@endphp
                        <tr>
                            <td><div class="rank-badge {!! $rankClass !!}">{!! $rank !!}</div></td>
                            <td class="fw-semibold">{{ $ls['name'] }}</td>
                            <td class="text-center">{!! $ls['course_count'] !!}</td>
                            <td class="text-center">{!! $ls['chapter_count'] !!}</td>
                            <td class="text-center fw-bold">{!! $ls['activity_score'] !!}</td>
                            <td class="text-center">{!! $ls['enrolled_count'] !!}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-1">{{ t('Subjek Paling Popular', 'Most Popular Subjects') }}</h5>
                <p class="small text-muted mb-3">{!! sprintf(t('Melayakkan diri jika sekurang-kurangnya %d pelajar berdaftar DAN ada aktiviti (latihan/kuiz) ditawarkan.', 'Qualifies with at least %d enrolled students AND some activity (exercises/quizzes) offered.'), $MIN_STUDENTS) !!}</p>
                @if (count($qualified_subjects) === 0)
                <div class="isep-empty py-4">
                    <div class="isep-empty-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="isep-empty-title">{{ t('Belum ada subjek melayakkan diri', 'No subject qualifies yet') }}</div>
                    <div class="isep-empty-sub">{!! sprintf(t('Tiada subjek dengan sekurang-kurangnya %d pelajar berdaftar buat masa ini.', 'No subject has at least %d enrolled students yet.'), $MIN_STUDENTS) !!}</div>
                </div>
                @else
                @foreach ($qualified_subjects as $i => $s)
                @php
                    $rank = $i + 1;
                    $rankClass = $rank === 1 ? 'r1' : ($rank === 2 ? 'r2' : ($rank === 3 ? 'r3' : 'rn'));
                @endphp
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <div class="rank-badge {!! $rankClass !!}">{!! $rank !!}</div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $s['name'] }}</div>
                        <small class="text-muted">{!! $s['chapter_count'] !!} {{ t('bab', 'chapters') }} · {!! $s['exercise_count'] !!} {{ t('latihan', 'exercises') }} · {!! $s['quiz_count'] !!} {{ t('kuiz', 'quizzes') }}</small>
                    </div>
                    <div class="fw-bold" style="color:#D4AF37;">{!! $s['enrolled_count'] !!} <span class="small text-muted fw-normal">{{ t('pelajar', 'students') }}</span></div>
                </div>
                @endforeach
@endif
            </div>
        </div>
    </div>
</div>
@endsection
