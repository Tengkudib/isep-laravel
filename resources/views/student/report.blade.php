@extends('layouts.student')

@section('title'){{ t('Laporan & Maklum Balas', 'Reports & Feedback') }} - iSEP
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .topbar { background: linear-gradient(135deg, #0B2545 0%, #13315C 70%, #D4AF37 100%); padding:24px 32px; border-radius:0 0 var(--isep-r-xl) var(--isep-r-xl); }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); }
    </style>
@endpush

@section('content')
@include('partials.report_page')
@endsection
