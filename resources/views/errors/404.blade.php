@extends('layouts.app')

@section('title'){{ t('Halaman Tidak Dijumpai', 'Page Not Found') }} - iSEP
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%);
            padding: var(--isep-sp-5); position: relative; overflow: hidden;
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.45; z-index: 0; }
        .orb-1 { width: 320px; height: 320px; background: #D4AF37; top: -100px; left: -100px; animation: isepOrbDrift 12s ease-in-out infinite alternate; }
        .orb-2 { width: 260px; height: 260px; background: #13315C; bottom: -80px; right: -60px; animation: isepOrbDrift 14s ease-in-out infinite alternate -5s; }
        @keyframes isepOrbDrift { from { transform: translate(0,0) scale(1); } to { transform: translate(24px,-24px) scale(1.1); } }
        @media (prefers-reduced-motion: reduce) { .orb-1, .orb-2 { animation: none; } }

        .error-card {
            position: relative; z-index: 1; text-align: center; max-width: 480px; width: 100%;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: var(--isep-r-xl);
            box-shadow: var(--isep-shadow-xl);
            padding: var(--isep-sp-7) var(--isep-sp-6);
            animation: isepFadeInUp 0.5s var(--isep-ease) both;
        }
        .error-code {
            font-size: 5.5rem; font-weight: 800; line-height: 1;
            background: linear-gradient(135deg, #0B2545, #D4AF37);
            -webkit-background-clip: text; background-clip: text; color: transparent;
            margin-bottom: var(--isep-sp-2);
        }
        .error-icon {
            width: 72px; height: 72px; margin: 0 auto var(--isep-sp-4);
            border-radius: 50%; background: linear-gradient(135deg, #13315C, #0B2545);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: white; box-shadow: 0 10px 24px rgba(11,37,69,0.35);
        }
        .error-title { font-size: var(--isep-fs-xl); font-weight: 700; color: #0B2545; margin-bottom: var(--isep-sp-2); }
        .error-sub { color: #4a5568; margin-bottom: var(--isep-sp-6); font-size: var(--isep-fs-base); }
        .btn-home {
            background: linear-gradient(135deg, #D4AF37, #B8941F); color: #0B2545; border: none;
            padding: 13px 32px; border-radius: var(--isep-r-lg); font-weight: 700; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px; transition: all var(--isep-duration) var(--isep-ease);
        }
        .btn-home:hover { color: #0B2545; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(212,175,55,0.4); }
    </style>
@endpush

@section('body')
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="error-card">
        <div class="error-code">404</div>
        <div class="error-icon"><i class="fas fa-map-signs"></i></div>
        <div class="error-title">{{ t('Alamak, halaman ini tak jumpa!', 'Oops, this page could not be found!') }}</div>
        <p class="error-sub">{{ t('Halaman yang anda cari mungkin telah dipindah, dipadam, atau URL yang ditaip tidak tepat.', 'The page you are looking for may have been moved, deleted, or the URL you typed is incorrect.') }}</p>
        <a href="{{ route('home') }}" class="btn-home"><i class="fas fa-house"></i> {{ t('Kembali ke Laman Utama', 'Back to Home') }}</a>
    </div>
@endsection
