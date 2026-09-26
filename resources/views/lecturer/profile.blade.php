@extends('layouts.staff')

@section('title'){{ t('Profil Saya - iSEP', 'My Profile - iSEP') }}
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding: var(--isep-sp-6); border-radius: 0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border: none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-sm); }
        .avatar-lg {
            width: 84px; height: 84px; border-radius: 50%;
            background: linear-gradient(135deg, var(--isep-primary), var(--isep-secondary));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 2rem; margin: 0 auto var(--isep-sp-3);
        }
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if ($error && !$error_field)<div class="alert alert-danger border-0 shadow-sm">{{ $error }}</div>@endif
            @if ($success)<div class="alert alert-success border-0 shadow-sm">{{ $success }}</div>@endif

            <div class="card-modern p-4 text-center mb-4">
                <div class="avatar-lg">{!! strtoupper(substr($user['name'], 0, 1)) !!}</div>
                <h5 class="fw-bold mb-0">{{ $user['name'] }}</h5>
                <small class="text-muted">{{ $user['email'] }}</small>
            </div>

            <div class="card-modern p-4 mb-4">
                <h6 class="fw-bold mb-3">{{ t('Maklumat Peribadi', 'Personal Information') }}</h6>
                <form method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Nama Penuh', 'Full Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ $user['name'] }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ t('Emel', 'Email') }}</label>
                        <input type="email" class="form-control" value="{{ $user['email'] }}" disabled>
                        <small class="text-muted">{{ t('Emel tidak boleh ditukar, hubungi admin.', 'Email cannot be changed, contact admin.') }}</small>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">{{ t('Simpan Perubahan', 'Save Changes') }}</button>
                </form>
            </div>

            <div class="card-modern p-4">
                <h6 class="fw-bold mb-3">{{ t('Tukar Kata Laluan', 'Change Password') }}</h6>
                <form method="POST">
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
