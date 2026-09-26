@extends('layouts.staff')

@section('title'){{ t('Laporan & Maklum Balas', 'Reports & Feedback') }} - iSEP
@endsection

@push('styles')
<style>
        body { background: #FAF7F0; font-family:'Segoe UI',sans-serif; }
        .card-modern { border:none; border-radius:var(--isep-r-xl); box-shadow:var(--isep-shadow-sm); }
    </style>
@endpush

@section('content')
@include('partials.report_page')
@endsection
