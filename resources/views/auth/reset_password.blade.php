@extends('layouts.app')

@section('title'){{ t('Tetapkan Semula Kata Laluan', 'Reset Password') }} - iSEP
@endsection
@section('body_class', 'auth-hero')

@push('styles')
<style>
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%);
            font-family: 'Segoe UI', sans-serif; position: relative; overflow: hidden; padding: 24px;
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.5; z-index: 0; }
        .orb-1 { width: 300px; height: 300px; background: #D4AF37; top: -80px; left: -80px; animation: isepOrbDrift 11s ease-in-out infinite alternate; }
        .orb-2 { width: 260px; height: 260px; background: #13315C; bottom: -60px; right: -60px; animation: isepOrbDrift 13s ease-in-out infinite alternate -4s; }
        .orb-3 { width: 200px; height: 200px; background: #6B7A8F; top: 40%; right: 8%; animation: isepOrbDrift 9s ease-in-out infinite alternate -7s; }
        @keyframes isepOrbDrift { from { transform: translate(0,0) scale(1); } to { transform: translate(24px,-24px) scale(1.1); } }
        @media (prefers-reduced-motion: reduce) { .orb-1, .orb-2, .orb-3 { animation: none; } }
        .reset-card {
            position: relative; z-index: 1;
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(24px) saturate(180%); -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 24px;
            box-shadow: 0 8px 40px rgba(31, 38, 135, 0.25), inset 0 1px 0 rgba(255,255,255,0.6);
            padding: 48px 40px; animation: isepFadeInUp 0.5s cubic-bezier(.4,0,.2,1) both;
            width: 100%; max-width: 440px;
        }
        .brand-logo {
            width: 64px; height: 64px; border-radius: 16px; overflow: hidden;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;
            box-shadow: 0 6px 18px rgba(11,37,69,0.25);
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: cover; }
        .form-control {
            border-radius: 10px; border: 1px solid rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.7); padding: 11px 15px;
        }
        .form-control:focus { background: rgba(255,255,255,0.9); border-color: #D4AF37; box-shadow: 0 0 0 3px rgba(212,175,55,0.2); }
        .btn-reset {
            background: linear-gradient(135deg, #D4AF37, #B8941F); border: none; border-radius: 10px;
            padding: 13px; font-weight: 700; color: #0B2545; transition: all 0.2s ease; width: 100%;
        }
        .btn-reset:hover { opacity: 0.95; color: #0B2545; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(212,175,55,0.4); }
        .back-to-login { color: #0B2545; font-weight: 600; text-decoration: none; font-size: 0.88rem; }
        .back-to-login:hover { color: #13315C; }
    </style>
@endpush

@section('body')
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="reset-card">
        <div class="text-center mb-4">
            <div class="brand-logo"><img src="{{ asset('assets/img/iSEP.png') }}" alt="iSEP"></div>
            <h4 class="fw-bold mb-0">{{ t('Tetapkan Semula Kata Laluan', 'Reset Password') }}</h4>
            <small class="text-muted">{{ t('Sahkan identiti anda untuk tetapkan kata laluan baharu.', 'Verify your identity to set a new password.') }}</small>
        </div>

        @if (session('error'))
        <div class="alert alert-danger border-0 small"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
        @endif
        @if (session('success'))
        <div class="alert alert-success border-0 small"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
        <div class="text-center mt-3"><a href="{{ route('login') }}" class="back-to-login"><i class="fas fa-arrow-left me-1"></i> {{ t('Kembali ke Log Masuk', 'Back to Login') }}</a></div>
        @else

        <form method="POST" action="{{ route('password.reset') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small">{{ t('Username', 'Username') }}</label>
                <input type="text" name="username" class="form-control text-uppercase" required value="{{ old('username') }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">{{ t('Emel Berdaftar', 'Registered Email') }}</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">{{ t('Kata Laluan Baharu', 'New Password') }}</label>
                <input type="password" name="new_password" class="form-control" required>
                <small class="text-muted">{{ t('Sekurang-kurangnya 6 aksara.', 'At least 6 characters.') }}</small>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold small">{{ t('Sahkan Kata Laluan Baharu', 'Confirm New Password') }}</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-reset"><i class="fas fa-key me-2"></i>{{ t('Tetapkan Semula', 'Reset Password') }}</button>
        </form>
        <div class="text-center mt-3"><a href="{{ route('login') }}" class="back-to-login"><i class="fas fa-arrow-left me-1"></i> {{ t('Kembali ke Log Masuk', 'Back to Login') }}</a></div>
        @endif
    </div>
@endsection
