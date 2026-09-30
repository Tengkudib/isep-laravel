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
            <div class="brand-logo"><img src="{{ asset('assets/img/iSEP-256.png') }}" alt="iSEP"></div>
            <h4 class="fw-bold mb-0">{{ t('Tetapkan Semula Kata Laluan', 'Reset Password') }}</h4>
            <small class="text-muted">{{ t('Lupa kata laluan? Kami akan bantu anda.', 'Forgot your password? We can help.') }}</small>
        </div>

        <div class="alert alert-info border-0 small mb-0">
            <i class="fas fa-user-shield me-2"></i>{{ t('Untuk keselamatan akaun, kata laluan hanya boleh ditetapkan semula oleh pentadbir sistem. Sila hubungi admin atau pensyarah anda dengan username anda, dan mereka akan berikan kata laluan sementara.', 'For account security, passwords can only be reset by the system administrator. Please contact the admin or your lecturer with your username, and they will give you a temporary password.') }}
        </div>
        <div class="text-center mt-4"><a href="{{ route('login') }}" class="back-to-login"><i class="fas fa-arrow-left me-1"></i> {{ t('Kembali ke Log Masuk', 'Back to Login') }}</a></div>
    </div>
@endsection
