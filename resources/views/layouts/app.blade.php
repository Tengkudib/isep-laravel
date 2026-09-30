<!DOCTYPE html>
<html lang="{{ current_lang() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{!! trim($__env->yieldContent('title', 'iSEP')) !!}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/iSEP-256.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @unless (View::hasSection('no_fontawesome'))
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @endunless
    @hasSection('theme_first')
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}?v=3">
    @endif
    @stack('styles')
    @unless (View::hasSection('theme_first'))
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}?v=3">
    @endunless
    @stack('styles_after')
</head>
<body class="@yield('body_class')">
@yield('body')
@auth
{{-- Log keluar melalui POST (dilindungi CSRF); mana-mana pautan dengan data-logout menghantar borang ini --}}
<form id="isepLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
<script>
document.addEventListener('click', function (e) {
    const link = e.target.closest('[data-logout]');
    if (!link) return;
    e.preventDefault();
    document.getElementById('isepLogoutForm').submit();
});
</script>
@endauth
@stack('scripts')
</body>
</html>
