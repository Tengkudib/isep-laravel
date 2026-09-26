@extends('layouts.app')

@section('body_class', 'has-sidebar')

@section('body')
@include('partials.student_sidebar')
@yield('content')
@unless (View::hasSection('no_bootstrap_js'))
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endunless
@endsection
