@extends('layouts.staff')

@section('title'){{ t('Urus Pengguna - iSEP Admin', 'Manage Users - iSEP Admin') }}
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); }
        .role-badge { padding:4px 12px; border-radius:999px; font-weight:600; font-size:0.78rem; }
        .role-admin { background:#fff3cd; color:#664d03; }
        .role-lecturer { background:#d1e7dd; color:#0a5132; }
        .role-student { background:#cfe2ff; color:#0a2c6b; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <h4 class="fw-bold mb-0"><i class="fas fa-users me-2"></i>{{ t('Urus Pengguna', 'Manage Users') }}</h4>
    <small>{!! t('Daftar akaun student &amp; lecturer baharu', 'Register new student &amp; lecturer accounts') !!}</small>
</div>

<div class="container-fluid p-4">
    @if ($error)<div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>@endif
    @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            @foreach (['admin' => 'Admin', 'lecturer' => 'Lecturer', 'student' => 'Student'] as $role_key => $role_label)
            <div class="card-modern p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-secondary mb-3">{!! $role_label !!} <span class="badge bg-light text-dark">{!! count($users_by_role[$role_key]) !!}</span></h6>

                @if ($role_key === 'student')
                    @if (empty($student_groups))
                    <div class="isep-empty py-3">
                        <div class="isep-empty-icon"><i class="fas fa-user-slash"></i></div>
                        <div class="isep-empty-title">{{ t('Tiada pelajar', 'No students') }}</div>
                        <div class="isep-empty-sub">{{ t('Belum ada akaun pelajar didaftarkan lagi.', 'No student accounts registered yet.') }}</div>
                    </div>
                    @else
                    <div class="accordion" id="studentDeptAccordion">
                        @foreach ($student_groups as $dept => $semGroups)
@php
                            $deptId = 'dept_' . preg_replace('/[^A-Za-z0-9]/', '', $dept);
                            $deptCount = 0;
                            foreach ($semGroups as $sesiGroups) { foreach ($sesiGroups as $students) { $deptCount += count($students); } };
@endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{!! $deptId !!}">
                                    <span class="badge me-2" style="background:#0B2545;">{{ $dept }}</span>
                                    {!! $deptCount !!} {{ t('pelajar', 'students') }}
                                </button>
                            </h2>
                            <div id="{!! $deptId !!}" class="accordion-collapse collapse" data-bs-parent="#studentDeptAccordion">
                                <div class="accordion-body">
                                    <div class="accordion" id="{!! $deptId !!}_semAcc">
                                        @foreach ($semGroups as $sem => $sesiGroups)
@php
                                            $semId = $deptId . '_sem' . $sem;
                                            $semCount = 0;
                                            foreach ($sesiGroups as $students) { $semCount += count($students); }
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
                                                    @foreach ($sesiGroups as $sesi => $students)
                                                    <h6 class="small fw-bold text-muted mt-2 mb-2"><i class="fas fa-calendar me-1"></i>{{ $sesi }} <span class="badge bg-light text-dark">{!! count($students) !!}</span></h6>
                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-hover table-sm mb-0">
                                                            <thead><tr><th>{{ t('Nama', 'Name') }}</th><th>{{ t('Username', 'Username') }}</th><th>{{ t('Emel', 'Email') }}</th><th>{{ t('ID Pelajar', 'Student ID') }}</th><th>{{ t('Status', 'Status') }}</th><th class="text-center">{{ t('Tindakan', 'Actions') }}</th></tr></thead>
                                                            <tbody>
                                                            @foreach ($students as $u)
@include('admin.partials.user_row', ['u' => $u, 'role_key' => 'student'])
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
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>{{ t('Nama', 'Name') }}</th><th>{{ t('Username', 'Username') }}</th><th>{{ t('Emel', 'Email') }}</th><th>{{ t('Status', 'Status') }}</th><th class="text-center">{{ t('Tindakan', 'Actions') }}</th></tr></thead>
                        <tbody>
                        @if (count($users_by_role[$role_key]) === 0)
                        <tr><td colspan="5">
                            <div class="isep-empty py-3">
                                <div class="isep-empty-icon"><i class="fas fa-user-slash"></i></div>
                                <div class="isep-empty-title">{{ t('Tiada', 'No') }} {!! strtolower($role_label) !!}</div>
                                <div class="isep-empty-sub">{{ t('Belum ada akaun ', 'No ') }}{!! strtolower($role_label) !!}{{ t(' didaftarkan lagi.', ' accounts registered yet.') }}</div>
                            </div>
                        </td></tr>
                        @endif
                        @foreach ($users_by_role[$role_key] as $u)
@include('admin.partials.user_row', ['u' => $u, 'role_key' => $role_key])
@endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="col-lg-4">
            <div class="card-modern p-4" style="position:sticky; top:16px;">
                <h6 class="fw-bold mb-3">{{ t('Daftar Pengguna Baharu', 'Register New User') }}</h6>
                <form method="POST" id="addUserForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Peranan', 'Role') }}</label>
                        <select name="role" id="roleSelect" class="form-select" onchange="toggleStudentFields(this.value)">
                            <option value="student">Student</option>
                            <option value="lecturer">Lecturer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Nama Penuh', 'Full Name') }}</label>
                        <input type="text" name="name" id="nameInput" class="form-control" required onkeyup="syncUsername()">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Username (log masuk)', 'Username (login)') }}</label>
                        <input type="text" name="username" id="usernameInput" class="form-control text-uppercase" required>
                        <small class="text-muted">{{ t('Auto diisi huruf besar dari nama; boleh diedit.', 'Auto-filled in caps from the name; editable.') }}</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Emel (pilihan)', 'Email (optional)') }}</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">{{ t('Kata Laluan', 'Password') }}</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div id="studentFields">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">{{ t('ID Pelajar', 'Student ID') }}</label>
                            <input type="text" name="student_id" class="form-control" placeholder="{{ t('Contoh: 34DIT24FXXXX', 'Example: 34DIT24FXXXX') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">{{ t('Program', 'Programme') }}</label>
                            <input type="text" name="programme" class="form-control" placeholder="{{ t('Diploma Sains Komputer', 'Diploma in Computer Science') }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">{{ t('Semester', 'Semester') }}</label>
                            <input type="number" name="semester" class="form-control" min="1" max="8">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">{{ t('Sesi', 'Session') }}</label>
                            <input type="text" name="session_year" class="form-control" placeholder="{{ t('Contoh: 2024/2025', 'Example: 2024/2025') }}">
                        </div>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary w-100 mt-2">
                        <i class="fas fa-user-plus me-2"></i>{{ t('Daftar Pengguna', 'Register User') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
function toggleStudentFields(role) {
    document.getElementById('studentFields').style.display = (role === 'student') ? 'block' : 'none';
}
var usernameTouched = false;
document.getElementById('usernameInput').addEventListener('input', function() { usernameTouched = true; });
function syncUsername() {
    if (usernameTouched) return;
    document.getElementById('usernameInput').value = document.getElementById('nameInput').value.toUpperCase();
}
</script>
@endsection
