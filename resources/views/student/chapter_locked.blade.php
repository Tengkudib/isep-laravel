@extends('layouts.app')

@section('title'){{ t('Bab Berkunci', 'Chapter Locked') }} - iSEP
@endsection
@section('body_class', 'lock-dark')
@section('no_fontawesome', '1')

@push('styles')
<style>
            body.lock-dark { background: linear-gradient(135deg, #0B2545, #13315C) !important; color:white !important; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',sans-serif; }
            .lock-box { text-align:center; max-width:420px; }
            .lock-icon { font-size:4rem; margin-bottom:16px; }
        </style>
@endpush

@section('body')
        <div class="lock-box">
            <div class="lock-icon">🔒</div>
            <h3 class="fw-bold">{{ t('Bab Berkunci', 'Chapter Locked') }}</h3>
            <p class="text-white-50">{{ t('Anda mesti selesaikan bab sebelumnya sebelum boleh mengakses:', 'You must complete the previous chapter before you can access:') }}</p>
            <p class="fw-semibold">{{ t('Bab', 'Chapter') }} {!! $chapter['chapter_number'] !!} — {{ $chapter['title'] }}</p>
            <a href="{{ route('student.language', $chapter['language_slug']) }}" class="btn btn-light fw-semibold mt-3">{{ t('Kembali ke Laluan Pembelajaran', 'Back to Learning Path') }}</a>
        </div>
    
@endsection
