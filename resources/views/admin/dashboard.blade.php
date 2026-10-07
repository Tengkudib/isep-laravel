@extends('layouts.staff')

@section('title'){{ t('Papan Pemuka Admin - iSEP', 'Admin Dashboard - iSEP') }}
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family: 'Segoe UI', sans-serif; }
        .topbar {
            background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%);
            color: white; padding: 24px 32px; border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
            display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;
        }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-sm); }
        .stat-card { padding: 20px; text-align: center; }
        .stat-card h3 { font-weight: 700; margin-bottom: 2px; }
        .badge-risk { background:#dc3545; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <h4 class="fw-bold mb-0"><i class="fas fa-shield-alt me-2"></i>{{ t('Admin iSEP', 'iSEP Admin') }}</h4>
        <small>{{ t('Selamat kembali', 'Welcome back') }}, {{ auth()->user()->name }}</small>
    </div>
    <a href="{{ route('logout') }}" data-logout class="btn btn-light text-primary fw-semibold"><i class="fas fa-sign-out-alt me-1"></i>{{ t('Log Keluar', 'Logout') }}</a>
</div>

<div class="container-fluid p-4">

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-primary">{!! $stats['students'] !!}</h3><small class="text-muted">{{ t('Jumlah Pelajar', 'Total Students') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-success">{!! $stats['active_students'] !!}</h3><small class="text-muted">{{ t('Pelajar Aktif', 'Active Students') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-info">{!! $stats['languages'] !!}</h3><small class="text-muted">{{ t('Bahasa Pengaturcaraan', 'Programming Languages') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-warning">{!! $stats['chapters'] !!}</h3><small class="text-muted">{{ t('Jumlah Bab', 'Total Chapters') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-primary">{!! $stats['chapters_completed'] !!}</h3><small class="text-muted">{{ t('Bab Diselesaikan', 'Chapters Completed') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-success">{!! $stats['quiz_attempts'] !!}</h3><small class="text-muted">{{ t('Percubaan Kuiz', 'Quiz Attempts') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-info">{!! $stats['avg_quiz_score'] !!}%</h3><small class="text-muted">{{ t('Purata Skor Kuiz', 'Average Quiz Score') }}</small></div></div>
        <div class="col-md-3 col-6"><div class="card-modern stat-card"><h3 class="text-warning">{!! $stats['exercises_done'] !!}</h3><small class="text-muted">{{ t('Latihan Diselesaikan', 'Exercises Completed') }}</small></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-modern p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-users me-2"></i>{{ t('Pemantauan Pelajar', 'Student Monitoring') }}</h5>
                @if (empty($student_monitor_groups))
                <div class="isep-empty py-3">
                    <div class="isep-empty-icon"><i class="fas fa-user-slash"></i></div>
                    <div class="isep-empty-title">{{ t('Tiada pelajar', 'No students') }}</div>
                </div>
                @else
                <div class="accordion" id="monitorDeptAccordion">
                    @foreach ($student_monitor_groups as $dept => $semGroups)
@php
                        $deptId = 'mdept_' . preg_replace('/[^A-Za-z0-9]/', '', $dept);
                        $deptCount = 0;
                        foreach ($semGroups as $sesiGroups) { foreach ($sesiGroups as $rows) { $deptCount += count($rows); } };
@endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{!! $deptId !!}">
                                <span class="badge me-2" style="background:#0B2545;">{{ $dept }}</span>
                                {!! $deptCount !!} {{ t('pelajar', 'students') }}
                            </button>
                        </h2>
                        <div id="{!! $deptId !!}" class="accordion-collapse collapse" data-bs-parent="#monitorDeptAccordion">
                            <div class="accordion-body">
                                <div class="accordion" id="{!! $deptId !!}_semAcc">
                                    @foreach ($semGroups as $sem => $sesiGroups)
@php
                                        $semId = $deptId . '_sem' . $sem;
                                        $semCount = 0;
                                        foreach ($sesiGroups as $rows) { $semCount += count($rows); }
                                        $semLabel = $sem > 0 ? t('Semester ', 'Semester ') . $sem : t('Tidak Diketahui', 'Unknown');
@endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{!! $semId !!}">
                                                {{ $semLabel }} <span class="badge bg-light text-dark ms-2">{!! $semCount !!}</span>
                                            </button>
                                        </h2>
                                        <div id="{!! $semId !!}" class="accordion-collapse collapse" data-bs-parent="#{!! $deptId !!}_semAcc">
                                            <div class="accordion-body">
                                                @foreach ($sesiGroups as $sesi => $rows)
                                                <h6 class="small fw-bold text-muted mt-2 mb-2"><i class="fas fa-calendar me-1"></i>{{ $sesi }} <span class="badge bg-light text-dark">{!! count($rows) !!}</span></h6>
                                                <div class="table-responsive mb-3">
                                                    <table class="table table-hover table-sm mb-0">
                                                        <thead><tr><th>{{ t('Nama', 'Name') }}</th><th>{{ t('ID Pelajar', 'Student ID') }}</th><th>{{ t('Bab Selesai', 'Chapters Completed') }}</th><th>{{ t('Purata Kuiz', 'Average Quiz') }}</th><th>XP</th><th>Streak</th><th>{{ t('Status', 'Status') }}</th></tr></thead>
                                                        <tbody>
                                                        @foreach ($rows as $s)
@php
                                                            $quiz_avg = round($s['quiz_avg'] ?? 0);
                                                            $at_risk = $s['chapters_done'] == 0 || $quiz_avg < 60;
@endphp
                                                        <tr>
                                                            <td class="fw-semibold">{{ $s['name'] }}</td>
                                                            <td>{{ $s['student_id'] ?? '-' }}</td>
                                                            <td>{!! $s['chapters_done'] !!}</td>
                                                            <td>{!! $quiz_avg !!}%</td>
                                                            <td>⚡ {!! $s['total_xp'] !!}</td>
                                                            <td>🔥 {!! $s['learning_streak'] !!}</td>
                                                            <td>
                                                                @if ($at_risk)
                                                                    <span class="badge badge-risk">{{ t('Perlu Perhatian', 'Needs Attention') }}</span>
                                                                @else
                                                                    <span class="badge bg-success">{{ t('Baik', 'Good') }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>{{ t('Bab Paling Sukar', 'Most Difficult Chapters') }}</h5>
                @if (count($difficult) === 0)
                    <div class="isep-empty py-3">
                        <div class="isep-empty-icon"><i class="fas fa-clipboard-question"></i></div>
                        <div class="isep-empty-title">{{ t('Belum ada data kuiz', 'No quiz data yet') }}</div>
                        <div class="isep-empty-sub">{{ t('Statistik bab paling sukar akan dipaparkan di sini apabila pelajar mula menjawab kuiz.', 'Statistics for the most difficult chapters will be shown here once students start answering quizzes.') }}</div>
                    </div>
                @endif
                @foreach ($difficult as $i => $d)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <div class="fw-semibold small">{{ $d['title'] }}</div>
                        <small class="text-muted">{{ $d['lang_name'] }}</small>
                    </div>
                    <span class="badge bg-danger">{!! round($d['avg_pct']) !!}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
