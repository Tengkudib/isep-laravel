@extends('layouts.app')

@section('title')iSEP - {{ t('Improve Self Education Platform', 'Improve Self Education Platform') }}
@endsection
@section('theme_first', '1')

@push('styles')
<style>
        body { font-family: var(--isep-font); }

        /* ---------- Navbar awam: rata, tenang, tiada glass/blur ---------- */
        .public-nav {
            padding: 22px 40px; display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 100;
            background: var(--isep-bg);
            border-bottom: 1px solid transparent;
            transition: border-color var(--isep-duration) var(--isep-ease), background var(--isep-duration) var(--isep-ease);
        }
        .public-nav.is-scrolled { border-bottom-color: var(--isep-border); }
        .public-nav .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .public-nav .brand img { height: 30px; }
        .public-nav .brand span { font-weight: 800; font-size: 1.2rem; color: var(--isep-text) !important; letter-spacing: -0.01em; }
        .public-nav .nav-right { display: flex; align-items: center; gap: 24px; }
        .public-nav .nav-links { display: flex; gap: 36px; align-items: center; }
        .public-nav .nav-links a:not(.btn-login-nav) { color: var(--isep-text-muted); text-decoration: none; font-weight: 600; font-size: 0.92rem; }
        .public-nav .nav-links a:not(.btn-login-nav):hover { color: var(--isep-text); }
        .public-nav .btn-login-nav {
            background: var(--isep-primary); color: white !important; padding: 9px 20px;
            border-radius: var(--isep-r-lg); font-weight: 600; font-size: 0.92rem; text-decoration: none;
            transition: background var(--isep-duration) var(--isep-ease);
        }
        .public-nav .btn-login-nav:hover { background: var(--isep-primary-dark); color: white !important; }
        .nav-utils { display: flex; align-items: center; gap: 8px; }
        .icon-toggle-btn {
            background: transparent; color: var(--isep-text-muted); border: 1px solid var(--isep-border);
            border-radius: var(--isep-r-md); height: 34px; padding: 0 10px; font-size: 0.76rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: border-color var(--isep-duration) var(--isep-ease), color var(--isep-duration) var(--isep-ease);
        }
        .icon-toggle-btn:hover { border-color: var(--isep-text-muted); color: var(--isep-text); }

        /* ---------- Hero: guna latar asas laman, bukan panel berasingan ---------- */
        .hero-section { padding: 110px 40px 90px; }
        .hero-headline {
            font-weight: 800; line-height: 1.03; letter-spacing: -0.03em;
            font-size: clamp(2.6rem, 5vw + 1.5rem, 5.5rem);
            color: var(--isep-text); margin-bottom: var(--isep-sp-5);
        }
        .hero-headline .accent-word { position: relative; white-space: nowrap; }
        .hero-headline .accent-word::after {
            content: ''; position: absolute; left: 0; bottom: 0.06em; height: 0.09em; width: 100%;
            background: var(--isep-secondary); border-radius: 2px;
            transform: scaleX(0); transform-origin: left; animation: heroUnderline 0.6s var(--isep-ease) 0.4s forwards;
        }
        @keyframes heroUnderline { to { transform: scaleX(1); } }
        @media (prefers-reduced-motion: reduce) {
            .hero-headline .accent-word::after { transform: scaleX(1); animation: none; }
        }
        .hero-lead { font-size: var(--isep-fs-lg); color: var(--isep-text-muted); max-width: 46ch; margin-bottom: var(--isep-sp-6); line-height: 1.6; }
        .hero-ctas { display: flex; gap: var(--isep-sp-4); flex-wrap: wrap; align-items: center; margin-bottom: var(--isep-sp-7); }
        .btn-flat-primary {
            background: var(--isep-primary); color: white; border: none; padding: 14px 28px;
            border-radius: var(--isep-r-lg); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center;
            transition: background var(--isep-duration) var(--isep-ease);
        }
        .btn-flat-primary:hover { background: var(--isep-primary-dark); color: white; }
        .btn-flat-ghost {
            background: transparent; color: var(--isep-text); border: 1.5px solid var(--isep-border); padding: 13px 26px;
            border-radius: var(--isep-r-lg); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center;
            transition: border-color var(--isep-duration) var(--isep-ease), color var(--isep-duration) var(--isep-ease);
        }
        .btn-flat-ghost:hover { border-color: var(--isep-text-muted); color: var(--isep-text); }
        .hero-stats-row { color: var(--isep-text-muted); font-size: 0.92rem; }
        .hero-stats-row strong { color: var(--isep-text); font-weight: 800; }
        .hero-stats-row .sep { margin: 0 var(--isep-sp-3); color: var(--isep-border); }

        .hero-preview-panel {
            border: 1px solid var(--isep-border); border-radius: var(--isep-r-xl); background: var(--isep-card-bg);
            padding: var(--isep-sp-6) 0 var(--isep-sp-6) var(--isep-sp-5); overflow: hidden;
        }
        .hero-preview-label { font-size: 0.78rem; font-weight: 700; letter-spacing: 0.02em; color: var(--isep-text-muted); margin-bottom: var(--isep-sp-4); text-transform: uppercase; padding-right: var(--isep-sp-5); }
        .hero-marquee {
            overflow: hidden; -webkit-mask-image: linear-gradient(90deg, #000 82%, transparent 100%);
            mask-image: linear-gradient(90deg, #000 82%, transparent 100%);
        }
        .hero-marquee-track {
            display: flex; gap: var(--isep-sp-4); width: max-content;
            animation: heroMarquee 22s linear infinite;
        }
        .hero-preview-panel:hover .hero-marquee-track { animation-play-state: paused; }
        @keyframes heroMarquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @media (prefers-reduced-motion: reduce) { .hero-marquee-track { animation: none; } }
        .hero-chip {
            flex: 0 0 auto; width: 168px; border: 1px solid var(--isep-border); border-radius: var(--isep-r-lg);
            padding: var(--isep-sp-4); background: var(--isep-bg);
        }
        .hero-chip-icon {
            width: 34px; height: 34px; border-radius: var(--isep-r-md); display: flex; align-items: center; justify-content: center;
            color: white; font-size: 0.95rem; margin-bottom: var(--isep-sp-3);
        }
        .hero-chip-name { font-weight: 700; color: var(--isep-text); margin-bottom: 2px; }
        .hero-chip-meta { font-size: 0.76rem; color: var(--isep-text-muted); }

        /* ---------- Seksyen editorial umum ---------- */
        .section-editorial { padding: var(--isep-sp-8) 40px; }
        .section-tinted { background: var(--isep-bg-teal); }
        @media (max-width: 768px) { .section-editorial { padding: var(--isep-sp-7) 24px; } .hero-section { padding: 70px 24px 60px; } }
        .section-heading h2 { font-weight: 800; color: var(--isep-text); letter-spacing: -0.015em; margin-bottom: var(--isep-sp-2); }
        .section-heading p { color: var(--isep-text-muted); margin-bottom: 0; }

        /* ---------- Ciri-ciri: senarai bergaris, bukan kad ikon ---------- */
        .feature-row { padding: var(--isep-sp-5) 0; border-top: 1px solid var(--isep-border); }
        .feature-row:first-child { border-top: none; padding-top: 0; }
        .feature-row-head { display: flex; align-items: center; gap: 14px; margin-bottom: var(--isep-sp-2); }
        .feature-row-head i { font-size: 1.15rem; color: var(--isep-primary); width: 24px; text-align: center; }
        [data-theme="dark"] .feature-row-head i { color: var(--isep-text); }
        .feature-row-head h5 { font-weight: 700; color: var(--isep-text); margin-bottom: 0; }
        .feature-row p { color: var(--isep-text-muted); margin-bottom: 0; padding-left: 38px; }

        /* ---------- Kursus: senarai baris, bukan kad ---------- */
        .course-list { border-top: 1px solid var(--isep-border); }
        .course-row {
            display: flex; justify-content: space-between; align-items: center; gap: var(--isep-sp-5);
            padding: var(--isep-sp-5) var(--isep-sp-3); border-bottom: 1px solid var(--isep-border);
            border-radius: var(--isep-r-md);
            transition: background var(--isep-duration) var(--isep-ease);
        }
        .course-row:hover { background: var(--isep-card-bg); }
        .course-row-main h3 { font-weight: 700; font-size: 1.2rem; color: var(--isep-text); margin-bottom: 4px; }
        .course-row-main p { color: var(--isep-text-muted); margin-bottom: 0; font-size: 0.92rem; max-width: 52ch; }
        .course-row-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
        .course-tag {
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;
            color: var(--isep-text-muted); border: 1px solid var(--isep-border); border-radius: var(--isep-r-sm);
            padding: 3px 9px; white-space: nowrap;
        }
        .course-chapters { font-size: 0.82rem; color: var(--isep-text-muted); white-space: nowrap; }
        @media (max-width: 576px) {
            .course-row { flex-direction: column; align-items: flex-start; }
            .course-row-meta { flex-direction: row; align-items: center; }
        }

        /* ---------- Tentang ---------- */
        .about-check { padding: var(--isep-sp-4) 0; border-top: 1px solid var(--isep-border); }
        .about-check:first-child { border-top: none; padding-top: 0; }
        .about-check h6 { font-weight: 700; color: var(--isep-text); margin-bottom: 4px; }
        .about-check h6 i { color: var(--isep-primary); margin-right: 8px; }
        [data-theme="dark"] .about-check h6 i { color: var(--isep-text); }
        .about-check p { color: var(--isep-text-muted); margin-bottom: 0; font-size: 0.9rem; }
        .about-quote {
            font-size: clamp(1.5rem, 1.6vw + 1.1rem, 2.15rem); font-weight: 700; color: var(--isep-text);
            line-height: 1.4; letter-spacing: -0.01em; margin: 0;
        }
        .about-quote span { color: var(--isep-text-muted); font-weight: 600; }

        /* ---------- CTA: jalur navy pekat, rata ---------- */
        .cta-band { background: var(--isep-primary-dark); padding: 80px 40px; text-align: center; }
        .cta-band h2 { color: white; font-weight: 800; margin-bottom: var(--isep-sp-3); letter-spacing: -0.015em; }
        .cta-band p { color: rgba(255,255,255,0.75); margin-bottom: var(--isep-sp-6); }
        .btn-flat-gold {
            background: var(--isep-secondary); color: var(--isep-primary-dark); border: none; padding: 14px 32px;
            border-radius: var(--isep-r-lg); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center;
            transition: background var(--isep-duration) var(--isep-ease);
        }
        .btn-flat-gold:hover { background: var(--isep-secondary-dark); color: var(--isep-primary-dark); }

        /* ---------- Footer ---------- */
        footer.site-footer { background: var(--isep-primary-dark); color: rgba(255,255,255,0.7); padding: 56px 40px 22px; }
        footer.site-footer h5 { color: white; font-weight: 700; font-size: 1rem; }
        footer.site-footer a { color: rgba(255,255,255,0.65) !important; text-decoration: none; }
        footer.site-footer a:hover { color: var(--isep-secondary) !important; }
        footer.site-footer .social-icon { width: 34px; height: 34px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; color: rgba(255,255,255,0.85) !important; transition: border-color var(--isep-duration) var(--isep-ease); }
        footer.site-footer .social-icon:hover { border-color: rgba(255,255,255,0.5); }
    </style>
@endpush

@section('body')
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('isep-theme') || 'light');</script>

<!-- Navbar -->
<nav class="public-nav" id="publicNav">
    <a href="{{ route('home') }}" class="brand">
        <img src="{{ asset('assets/img/iSEP.png') }}" alt="iSEP">
        <span>iSEP</span>
    </a>
    <div class="nav-right">
        <div class="nav-links d-none d-md-flex">
            <a href="{{ route('home') }}" class="nav-link-item">{{ t('Utama', 'Home') }}</a>
            <a href="#courses" class="nav-link-item">{{ t('Kursus', 'Courses') }}</a>
            <a href="#about" class="nav-link-item">{{ t('Tentang', 'About') }}</a>
            <a href="#contact" class="nav-link-item">{{ t('Hubungi', 'Contact') }}</a>
            <a href="{{ $dashboardUrl ?? route('login') }}" class="btn-login-nav">{!! $dashboardUrl ? t('Dashboard', 'Dashboard') : t('Log Masuk', 'Login') !!}</a>
        </div>
        <a href="{{ $dashboardUrl ?? route('login') }}" class="btn-login-nav d-md-none">{!! $dashboardUrl ? t('Dashboard', 'Dashboard') : t('Log Masuk', 'Login') !!}</a>
        <div class="nav-utils">
            <button type="button" class="icon-toggle-btn" onclick="toggleIsepLang()" title="{{ t('Tukar bahasa', 'Switch language') }}">{!! current_lang() === 'en' ? 'BM' : 'EN' !!}</button>
            <button type="button" class="icon-toggle-btn" onclick="toggleIsepTheme()" title="{{ t('Tukar mod gelap/terang', 'Toggle dark/light mode') }}" style="width:34px;">
                <i id="themeToggleIcon" class="fas fa-moon"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <h1 class="hero-headline">{{ t('Belajar Coding', 'Learn Coding') }} <span class="accent-word">{{ t('Dalam Talian', 'Online') }}</span></h1>
                <p class="hero-lead">{!! t('Kuasai Python, PHP, Java &amp; lebih lagi dengan kursus interaktif dan kuiz. Mulakan perjalanan pengaturcaraan anda hari ini.', 'Master Python, PHP, Java and more with interactive courses and quizzes. Start your programming journey today.') !!}</p>
                <div class="hero-ctas">
                    <a href="{{ $dashboardUrl ?? route('login') }}" class="btn btn-flat-primary"><i class="fas fa-arrow-right me-2"></i>{!! $dashboardUrl ? t('Ke Dashboard Saya', 'Go to My Dashboard') : t('Log Masuk untuk Mula', 'Login to Get Started') !!}</a>
                    <a href="#courses" class="btn btn-flat-ghost">{{ t('Lihat Kursus', 'Browse Courses') }}</a>
                </div>
                <div class="hero-stats-row">
                    <strong>{!! $stats['students'] !!}+</strong> {{ t('Pelajar', 'Students') }}<span class="sep">&middot;</span><strong>{!! $stats['languages'] !!}+</strong> {{ t('Kursus', 'Courses') }}<span class="sep">&middot;</span><strong>{!! $stats['quizzes'] !!}+</strong> {{ t('Kuiz', 'Quizzes') }}
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-preview-panel">
                    <div class="hero-preview-label">{{ t('Kursus Popular', 'Popular Courses') }}</div>
                    <div class="hero-marquee">
                        <div class="hero-marquee-track">
                            @for ($rep = 0; $rep < 2; $rep++)
                                @foreach ($allLanguages as $lang)
                                <div class="hero-chip">
                                    <div class="hero-chip-icon" style="background:{{ $lang['color_theme'] }};"><i class="{{ $lang['icon'] }}"></i></div>
                                    <div class="hero-chip-name">{{ $lang['name'] }}</div>
                                    <div class="hero-chip-meta">{{ $diffMap[$lang['difficulty']] ?? $lang['difficulty'] }} &middot; {!! $lang['chapter_count'] !!} {{ t('bab', 'chapters') }}</div>
                                </div>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section id="features" class="section-editorial">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="section-heading">
                    <h2>{{ t('Kenapa Pilih Kami?', 'Why Choose Us?') }}</h2>
                    <p>{{ t('Semua yang anda perlukan untuk belajar coding dengan berkesan.', 'Everything you need to learn coding effectively.') }}</p>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="feature-row">
                    <div class="feature-row-head"><i class="fas fa-laptop-code"></i><h5>{{ t('Kursus Interaktif', 'Interactive Courses') }}</h5></div>
                    <p>{{ t('Belajar melalui latihan praktikal dan projek sebenar.', 'Learn by doing with hands-on coding exercises and projects.') }}</p>
                </div>
                <div class="feature-row">
                    <div class="feature-row-head"><i class="fas fa-brain"></i><h5>{{ t('Kuiz Pintar', 'Smart Quizzes') }}</h5></div>
                    <p>{{ t('Uji kefahaman anda dengan kuiz interaktif dan maklum balas segera.', 'Test your knowledge with interactive quizzes and instant feedback.') }}</p>
                </div>
                <div class="feature-row">
                    <div class="feature-row-head"><i class="fas fa-certificate"></i><h5>{{ t('Perolehi Sijil', 'Earn Certificates') }}</h5></div>
                    <p>{{ t('Dapatkan sijil apabila anda selesaikan kursus sepenuhnya, bukan sekadar kuiz.', 'Get a certificate when you complete a course in full, not just quizzes.') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Courses Preview -->
<section id="courses" class="section-editorial section-tinted">
    <div class="container">
        <div class="section-heading text-center mb-5">
            <h2>{{ t('Kursus Popular', 'Popular Courses') }}</h2>
            <p>{{ t('Mula belajar bahasa pengaturcaraan popular ini.', 'Start learning these popular programming languages.') }}</p>
        </div>
        <div class="course-list">
            @foreach ($languages as $lang)
            <div class="course-row">
                <div class="course-row-main">
                    <h3>{{ $lang['name'] }}</h3>
                    <p>{{ $lang['description'] ?: t('Kursus pengaturcaraan lengkap untuk pelajar.', 'A complete programming course for students.') }}</p>
                </div>
                <div class="course-row-meta">
                    <span class="course-tag">{{ $diffMap[$lang['difficulty']] ?? $lang['difficulty'] }}</span>
                    <span class="course-chapters"><i class="fas fa-list me-1"></i>{!! $lang['chapter_count'] !!} {{ t('bab', 'chapters') }}</span>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ $dashboardUrl ?? route('login') }}" class="btn btn-flat-primary">{!! $dashboardUrl ? t('Ke Dashboard Saya', 'Go to My Dashboard') : t('Log Masuk untuk Akses Semua Kursus', 'Login to Access All Courses') !!}</a>
        </div>
    </div>
</section>

<!-- About -->
<section id="about" class="section-editorial">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="section-heading">
                    <h2>{{ t('Tentang Platform Kami', 'About Our Platform') }}</h2>
                </div>
                <p class="hero-lead" style="margin-top: var(--isep-sp-3);">{{ t('iSEP ialah platform pembelajaran kendiri dalam talian yang dibangunkan untuk membantu pelajar mempelajari pengaturcaraan melalui nota, latihan praktikal, dan kuiz.', 'iSEP is a self-paced online learning platform built to help students learn programming through notes, hands-on exercises, and quizzes.') }}</p>
                <div class="mt-4">
                    <div class="about-check">
                        <h6><i class="fas fa-check"></i>{{ t('Belajar Mengikut Kadar Sendiri', 'Learn at your own pace') }}</h6>
                        <p>{{ t('Belajar pengaturcaraan bila-bila masa mengikut kadar anda sendiri.', 'Learn programming anytime, at whatever pace suits you.') }}</p>
                    </div>
                    <div class="about-check">
                        <h6><i class="fas fa-check"></i>{{ t('Berlatih dan Uji Kemahiran Anda', 'Practice and Test Your Skills') }}</h6>
                        <p>{{ t('Perkukuh kefahaman melalui latihan praktikal dan kuiz.', 'Reinforce your understanding through hands-on exercises and quizzes.') }}</p>
                    </div>
                    <div class="about-check">
                        <h6><i class="fas fa-check"></i>{{ t('Pantau Perkembangan Anda', 'Track Your Progress') }}</h6>
                        <p>{{ t('Pantau perjalanan pembelajaran anda dan lihat perkembangan.', 'Track your learning journey and see how far you have come.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <p class="about-quote">&ldquo;{{ t('Lebih ', 'Over ') }}<span>{!! $stats['students'] !!}+ {{ t('pelajar', 'students') }}</span>{{ t(' sudah belajar bersama iSEP, meneroka kursus sebenar setiap hari.', ' are already learning with iSEP, working through real courses every day.') }}&rdquo;</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-band">
    <h2>{{ t('Bersedia untuk Mula Belajar?', 'Ready to Start Learning?') }}</h2>
    <p>{{ t('Sertai pelajar yang sedang belajar di platform kami.', 'Join students already learning on our platform.') }}</p>
    <a href="{{ $dashboardUrl ?? route('login') }}" class="btn btn-flat-gold">{!! $dashboardUrl ? t('Ke Dashboard Saya', 'Go to My Dashboard') : t('Log Masuk Sekarang', 'Login Now') !!}</a>
</section>

<!-- Footer -->
<footer class="site-footer" id="contact">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5><i class="fas fa-graduation-cap me-2"></i>iSEP</h5>
                <p class="small">{{ t('Improve Self Education Platform, platform dipercayai untuk belajar pengaturcaraan.', 'Improve Self Education Platform, a trusted platform for learning programming.') }}</p>
                <div class="d-flex gap-2">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-md-4">
                <h5>{{ t('Pautan Pantas', 'Quick Links') }}</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('home') }}">{{ t('Utama', 'Home') }}</a></li>
                    <li class="mb-2"><a href="#courses">{{ t('Kursus', 'Courses') }}</a></li>
                    <li class="mb-2"><a href="#about">{{ t('Tentang Kami', 'About Us') }}</a></li>
                    <li class="mb-2"><a href="#contact">{{ t('Hubungi', 'Contact') }}</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>{{ t('Hubungi Kami', 'Contact Us') }}</h5>
                <p class="small mb-2"><i class="fas fa-envelope me-2"></i>info@isep.edu.my</p>
                <p class="small mb-2"><i class="fas fa-phone me-2"></i>+60 172 825 884</p>
                <p class="small mb-2"><i class="fas fa-map-marker-alt me-2"></i>Malaysia</p>
            </div>
        </div>
        <hr style="border-color: rgba(255,255,255,0.15);">
        <p class="text-center small mb-0">&copy; {{ date('Y') }} iSEP. {{ t('Hak cipta terpelihara.', 'All rights reserved.') }}</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/theme-toggle.js') }}?v=3"></script>
<script>
    var publicNav = document.getElementById('publicNav');
    window.addEventListener('scroll', function () {
        publicNav.classList.toggle('is-scrolled', window.scrollY > 8);
    }, { passive: true });
</script>
@endsection
