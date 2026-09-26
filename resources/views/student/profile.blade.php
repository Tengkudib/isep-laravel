@extends('layouts.student')

@section('title'){{ t('Profil Saya', 'My Profile') }} - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { background: #f4f6fb; }
        .topbar {
            background: linear-gradient(135deg, #0a1128 0%, #1e3a8a 45%, #6d28d9 80%, #06b6d4 100%);
            color: white; padding: var(--isep-sp-6); border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl);
        }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-sm); }
        .avatar-lg {
            width: 84px; height: 84px; border-radius: 50%;
            background: linear-gradient(135deg, var(--isep-primary), var(--isep-secondary));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 2rem; margin: 0 auto var(--isep-sp-3);
        }
        .stat-mini { text-align: center; }
        .stat-mini h4 { font-weight: 800; margin-bottom: 0; }
        .form-label { font-size: var(--isep-fs-sm); font-weight: 600; color: var(--isep-text-muted); }
        .isep-field-hint { font-size: var(--isep-fs-xs); color: var(--isep-text-faint); margin-top: 4px; display: block; }
    </style>
@endpush

@section('content')
<div class="topbar">
    <h4 class="fw-bold mb-0"><i class="fas fa-user me-2"></i>{{ t('Profil Saya', 'My Profile') }}</h4>
    <small>{{ t('Urus maklumat peribadi dan kata laluan anda', 'Manage your personal information and password') }}</small>
</div>

<div class="container-fluid p-4">
    @if ($error && !$error_field)<div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>@endif
    @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

    <div class="row g-4">
        <!-- Ringkasan Profil -->
        <div class="col-lg-4">
            <div class="card-modern p-4 text-center mb-4">
                <div class="avatar-lg" style="@php
 echo $border_style ? 'background:' . htmlspecialchars($border_style) . '; padding:4px;' : ''; 
@endphp">
                    @if ($border_style)
                    <div style="width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,var(--isep-primary),var(--isep-secondary));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:2rem;">{!! strtoupper(substr($user['name'], 0, 1)) !!}</div>
                    @else
                        {!! strtoupper(substr($user['name'], 0, 1)) !!}
                    @endif
                </div>
                <a href="{{ route('student.shop') }}" class="small d-block mb-2"><i class="fas fa-store me-1"></i>{{ t('Tukar Border di Kedai XP', 'Change Border in XP Shop') }}</a>
                <h5 class="fw-bold mb-0 {!! $cosmetics['name_effect_class'] ?? '' !!}">{{ $user['name'] }}</h5>
                @if ($cosmetics['title_text'])
                <span class="badge mb-1" style="background:#D4AF37; color:#0B2545; font-weight:700;">{{ $cosmetics['title_text'] }}</span>
                @endif
                <small class="text-muted"><i class="fas fa-user-tag me-1"></i>{{ $user['username'] }}</small>
                @if ($user['student_id'])<p class="small text-muted mb-0 mt-1">ID: {{ $user['student_id'] }}</p>@endif
                @if (empty($user['email']))
                <p class="small text-warning mb-0 mt-1"><i class="fas fa-triangle-exclamation me-1"></i>{{ t('Emel belum ditetapkan', 'Email not set yet') }}</p>
                @endif

                <div class="row mt-4">
                    <div class="col-4 stat-mini"><h4 class="text-primary">{!! $stats['chapters'] !!}</h4><small class="text-muted">{{ t('Bab Selesai', 'Chapters Completed') }}</small></div>
                    <div class="col-4 stat-mini"><h4 class="text-success">{!! $stats['certificates'] !!}</h4><small class="text-muted">{{ t('Sijil', 'Certificates') }}</small></div>
                    <div class="col-4 stat-mini"><h4 class="text-warning">{!! $stats['badges'] !!}</h4><small class="text-muted">{{ t('Lencana', 'Badges') }}</small></div>
                </div>
                <hr>
                <div class="d-flex justify-content-around">
                    <div><i class="fas fa-bolt text-warning"></i> {{ $total_xp_earned }} XP</div>
                    <div><i class="fas fa-fire text-danger"></i> {!! (int)$user['learning_streak'] !!} {{ t('Hari', 'Days') }}</div>
                    <div><i class="fas fa-icicles text-info"></i> {!! (int)$user['streak_freezes'] !!} {{ t('Freeze', 'Freeze') }}</div>
                </div>
            </div>
        </div>

        <!-- Kemaskini Profil & Kata Laluan -->
        <div class="col-lg-8">
            <div class="card-modern p-4 mb-4">
                <h6 class="fw-bold mb-3">{{ t('Maklumat Peribadi', 'Personal Information') }}</h6>
                <form method="POST" action="{{ route('student.profile') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Nama Penuh', 'Full Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ $user['name'] }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Username (log masuk)', 'Username (login)') }}</label>
                        <input type="text" class="form-control" value="{{ $user['username'] }}" disabled>
                        <small class="text-muted">{{ t('Username tidak boleh ditukar. Hubungi admin jika perlu.', 'Username cannot be changed. Contact admin if needed.') }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Emel', 'Email') }}</label>
                        <input type="email" name="email" class="form-control {!! $error_field === 'email' ? 'is-invalid' : '' !!}" value="{{ $user['email'] ?? '' }}" placeholder="{{ t('Masukkan emel anda', 'Enter your email') }}">
                        @if ($error_field === 'email')<span class="isep-field-error-msg"><i class="fas fa-circle-exclamation"></i> {{ $error }}</span>
                        @else<span class="isep-field-hint">{{ t('Pilihan. Digunakan untuk tetapan semula kata laluan.', 'Optional. Used for password reset.') }}</span>@endif
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">{{ t('Program', 'Programme') }}</label>
                            <input type="text" name="programme" class="form-control" value="{{ $user['programme'] ?? '' }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-semibold">{{ t('Semester', 'Semester') }}</label>
                            <input type="number" name="semester" class="form-control" min="1" max="8" value="{{ $user['semester'] ?? '' }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-semibold">{{ t('Sesi', 'Session') }}</label>
                            <input type="text" name="session_year" class="form-control" placeholder="2024/2025" value="{{ $user['session_year'] ?? '' }}">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">{{ t('Simpan Perubahan', 'Save Changes') }}</button>
                </form>
            </div>

            <div class="card-modern p-4">
                <h6 class="fw-bold mb-3">{{ t('Tukar Kata Laluan', 'Change Password') }}</h6>
                <form method="POST" action="{{ route('student.profile') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Kata Laluan Semasa', 'Current Password') }}</label>
                        <input type="password" name="current_password" class="form-control {!! $error_field === 'current_password' ? 'is-invalid' : '' !!}" required>
                        @if ($error_field === 'current_password')<span class="isep-field-error-msg"><i class="fas fa-circle-exclamation"></i> {{ $error }}</span>@endif
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">{{ t('Kata Laluan Baharu', 'New Password') }}</label>
                            <input type="password" name="new_password" class="form-control {!! $error_field === 'new_password' ? 'is-invalid' : '' !!}" minlength="6" required>
                            @if ($error_field === 'new_password')<span class="isep-field-error-msg"><i class="fas fa-circle-exclamation"></i> {{ $error }}</span>
                            @else<span class="isep-field-hint">{{ t('Sekurang-kurangnya 6 aksara', 'At least 6 characters') }}</span>@endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">{{ t('Sahkan Kata Laluan Baharu', 'Confirm New Password') }}</label>
                            <input type="password" name="confirm_password" class="form-control {!! $error_field === 'confirm_password' ? 'is-invalid' : '' !!}" minlength="6" required>
                            @if ($error_field === 'confirm_password')<span class="isep-field-error-msg"><i class="fas fa-circle-exclamation"></i> {{ $error }}</span>@endif
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-outline-primary">{{ t('Tukar Kata Laluan', 'Change Password') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
