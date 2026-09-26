@extends('layouts.student')

@section('title'){{ t('Sijil Saya', 'My Certificates') }} - iSEP
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); color:white; padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius: var(--isep-r-xl); box-shadow: var(--isep-shadow-sm); }
        .cert-card { padding:20px; text-align:center; transition: transform var(--isep-duration) var(--isep-ease); }
        .cert-card:hover { transform: translateY(-4px); }
    </style>
@endpush

@section('content')
<div class="topbar">
    <h4 class="fw-bold mb-0"><i class="fas fa-certificate me-2"></i>{{ t('Sijil Saya', 'My Certificates') }}</h4>
    <small>{{ t('Sijil dikeluarkan automatik bila anda selesaikan sepenuhnya satu kursus', 'Certificates are issued automatically when you fully complete a course') }}</small>
</div>

<div class="container-fluid p-4">
    @if (count($certs) === 0)
    <div class="card-modern">
        <div class="isep-empty">
            <div class="isep-empty-icon"><i class="fas fa-award"></i></div>
            <div class="isep-empty-title">{{ t('Belum Ada Sijil', 'No Certificates Yet') }}</div>
            <div class="isep-empty-sub">{!! t('Selesaikan semua bab dalam satu kursus (nota, video, latihan &amp; kuiz) untuk mendapatkan sijil pertama anda!', 'Complete all chapters in a course (notes, videos, exercises &amp; quizzes) to earn your first certificate!') !!}</div>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach ($certs as $c)
        <div class="col-md-4">
            <div class="card-modern cert-card">
                <div style="width:56px;height:56px;border-radius:50%;background:{{ $c['color_theme'] }}22;color:{{ $c['color_theme'] }};display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 12px;">
                    <i class="{{ $c['icon'] }}"></i>
                </div>
                <h6 class="fw-bold">{{ $c['lang_name'] }}</h6>
                <small class="text-muted d-block mb-3">{{ t('Dikeluarkan', 'Issued') }}: {!! date('d M Y', strtotime($c['issued_at'])) !!}</small>
                <a href="{{ route('student.certificate', $c['certificate_code']) }}" class="btn btn-sm btn-primary w-100">{{ t('Lihat Sijil', 'View Certificate') }}</a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
