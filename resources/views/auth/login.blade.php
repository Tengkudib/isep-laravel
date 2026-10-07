@extends('layouts.app')

@section('title'){{ t('Log Masuk', 'Log In') }} - iSEP
@endsection
@section('body_class', 'auth-hero')

@push('styles')
<style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%);
            font-family: 'Segoe UI', sans-serif;
            position: relative;
            overflow: hidden;
        }
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.5;
            z-index: 0;
        }
        .orb-1 { width: 300px; height: 300px; background: #D4AF37; top: -80px; left: -80px; animation: isepOrbDrift 11s ease-in-out infinite alternate; }
        .orb-2 { width: 260px; height: 260px; background: #13315C; bottom: -60px; right: -60px; animation: isepOrbDrift 13s ease-in-out infinite alternate -4s; }
        .orb-3 { width: 200px; height: 200px; background: #6B7A8F; top: 40%; right: 8%; animation: isepOrbDrift 9s ease-in-out infinite alternate -7s; }
        @keyframes isepOrbDrift { from { transform: translate(0,0) scale(1); } to { transform: translate(24px,-24px) scale(1.1); } }
        @media (prefers-reduced-motion: reduce) { .orb-1, .orb-2, .orb-3 { animation: none; } }
        .login-card { position: relative; z-index: 1; }
        .login-card {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(31, 38, 135, 0.25), inset 0 1px 0 rgba(255,255,255,0.6);
            padding: 48px 40px;
            animation: isepFadeInUp 0.5s cubic-bezier(.4,0,.2,1) both;
            width: 100%;
            max-width: 420px;
        }
        .brand-logo {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 6px 18px rgba(32,178,170,0.25);
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: cover; }
        .form-control {
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.7);
            padding: 12px 16px;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.9);
            border-color: #D4AF37;
            box-shadow: 0 0 0 3px rgba(212,175,55,0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #D4AF37, #B8941F);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-weight: 700;
            color: #0B2545;
            transition: all 0.2s ease;
        }
        .btn-login:hover { opacity: 0.95; color: #0B2545; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(212,175,55,0.4); }
        .lang-toggle-login {
            position: absolute; top: 20px; right: 20px; z-index: 2;
            background: rgba(255,255,255,0.35); color: #0B2545; border: 1px solid rgba(255,255,255,0.5);
            border-radius: var(--isep-r-md); padding: 6px 12px; font-size: 0.78rem; font-weight: 700; cursor: pointer;
        }
        .lang-toggle-login:hover { background: rgba(255,255,255,0.55); }
    </style>
@endpush

@section('body')
    <button type="button" class="lang-toggle-login" onclick="toggleIsepLang()">{!! current_lang() === 'en' ? 'BM' : 'EN' !!}</button>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-logo"><img src="{{ asset('assets/img/iSEP-256.png') }}" alt="iSEP"></div>
            <h4 class="fw-bold mb-0">iSEP</h4>
            <small class="text-muted">Improve Self Education Platform</small>
        </div>

        @if (session('error'))
        <div class="alert alert-danger border-0 small"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ t('Username', 'Username') }}</label>
                <input type="text" name="username" class="form-control text-uppercase" placeholder="{{ t('NAMA PENUH', 'FULL NAME') }}" autocapitalize="characters" required>
            </div>
            <div class="mb-2">
                <label class="form-label fw-semibold">{{ t('Kata Laluan', 'Password') }}</label>
                <input type="password" name="password" class="form-control" placeholder="{{ t('Kata laluan', 'Password') }}" required>
            </div>
            <div class="text-end mb-4">
                <a href="{{ route('password.reset') }}" style="color:#0B2545; font-size:0.85rem; font-weight:600; text-decoration:none;">{{ t('Lupa Kata Laluan?', 'Forgot Password?') }}</a>
            </div>
            <button type="submit" class="btn btn-login w-100">
                <i class="fas fa-sign-in-alt me-2"></i>{{ t('Log Masuk', 'Log In') }}
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            Demo: ADMIN ISEP / admin123<br>
            Demo: ALI AHMAD / admin123
        </p>
    </div>
    <script src="{{ asset('assets/theme-toggle.js') }}?v=3"></script>
@endsection
