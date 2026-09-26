@extends('layouts.app')

@section('title'){{ t('Daftar Kursus', 'Course Enrollment') }} - iSEP
@endsection
@section('body_class', 'auth-hero')

@push('styles')
<style>
        body {
            min-height: 100vh; display:flex; align-items:center; justify-content:center;
            background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%);
            font-family: 'Segoe UI', sans-serif; padding: 24px;
        }
        .pledge-card { background: white; border-radius: var(--isep-r-xl); padding: 40px; max-width: 560px; box-shadow: var(--isep-shadow-xl); animation: isepFadeInUp 0.5s cubic-bezier(.4,0,.2,1) both; }
        .pledge-box { background: #FAF7F0; border-radius: var(--isep-r-xl); padding: 22px; border-left: 5px solid #0d6efd; }
        .pledge-box li { margin-bottom: 10px; }
        .btn-enroll { background: linear-gradient(135deg, #D4AF37, #B8941F); border:none; color:#0B2545; border-radius: var(--isep-r-lg); padding: 14px; font-weight:600; transition: all var(--isep-duration) var(--isep-ease); }
        .btn-enroll:hover { transform: translateY(-2px); box-shadow: var(--isep-shadow-lg); }
    </style>
@endpush

@section('body')
<script>document.documentElement.setAttribute('data-theme', localStorage.getItem('isep-theme') || 'light');</script>
<button type="button" onclick="toggleIsepTheme()" title="{{ t('Tukar mod gelap/terang', 'Toggle dark/light mode') }}" style="position:fixed; top:20px; right:20px; z-index:10; background:rgba(255,255,255,0.2); color:white; border:1px solid rgba(255,255,255,0.3); border-radius:10px; width:40px; height:40px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
    <i id="themeToggleIcon" class="fas fa-moon"></i>
</button>
<div class="pledge-card">
    <div class="text-center mb-3">
        <div style="width:56px;height:56px;border-radius:14px;background:{{ $language['color_theme'] }}22;color:{{ $language['color_theme'] }};display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto;">
            <i class="{{ $language['icon'] }}"></i>
        </div>
        <h4 class="fw-bold mt-3 mb-0">{{ t('Daftar Kursus', 'Enroll in Course') }} {{ $language['name'] }}</h4>
        <small class="text-muted">{{ $language['difficulty'] }} · {!! $language['total_chapters'] !!} {{ t('bab', 'chapters') }}</small>
    </div>

    @if (session('error'))
    <div class="alert alert-danger small">{{ session('error') }}</div>
    @endif

    <div class="pledge-box mb-4">
        <p class="fw-bold mb-2">📜 {{ t('Ikrar Pelajar iSEP', 'iSEP Student Pledge') }}</p>
        <p class="small text-muted mb-2">{{ t('Sebelum memulakan kursus ini, saya berikrar bahawa saya akan:', 'Before starting this course, I pledge that I will:') }}</p>
        <ul class="small mb-0">
            <li>{{ t('Belajar mengikut susunan bab yang ditetapkan dengan tekun dan konsisten.', 'Study the chapters in the set order diligently and consistently.') }}</li>
            <li>{{ t('Menyiapkan latihan dan kuiz dengan usaha sendiri, tanpa meniru jawapan orang lain.', 'Complete exercises and quizzes through my own effort, without copying others\' answers.') }}</li>
            <li>{{ t('Meluangkan masa secara berkala untuk mengekalkan momentum pembelajaran.', 'Set aside time regularly to maintain learning momentum.') }}</li>
            <li>{{ t('Menghubungi pensyarah/lecturer sekiranya menghadapi kesukaran memahami sesuatu topik.', 'Contact the lecturer if I have difficulty understanding a topic.') }}</li>
            <li>{{ t('Menggunakan platform ini secara bertanggungjawab untuk tujuan pembelajaran sahaja.', 'Use this platform responsibly for learning purposes only.') }}</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('student.enroll', $language['slug']) }}">
        @csrf
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="agree" id="agreeCheck" required>
            <label class="form-check-label small" for="agreeCheck">
                {{ t('Saya,', 'I,') }} <strong>{{ $user->name }}</strong>, {{ t('telah membaca dan bersetuju dengan ikrar di atas.', 'have read and agree to the pledge above.') }}
            </label>
        </div>
        <button type="submit" class="btn btn-enroll w-100">
            <i class="fas fa-check-circle me-2"></i>{!! t('Saya Setuju & Mula Belajar', 'I Agree & Start Learning') !!}
        </button>
    </form>
</div>
<script src="{{ asset('assets/theme-toggle.js') }}?v=3"></script>
@endsection
