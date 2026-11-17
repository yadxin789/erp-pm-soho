<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('assets/core/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/core/font-awesome/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/core/css/ddwl.css') }}">
    @stack('styles')
</head>
@hasSection('plain')
<body class="dw-login-body">
    @yield('content')
@else
<body>
    <div class="dw-app">
        @include('core::partials.sidebar')
        <div class="dw-body">
            @include('core::partials.topstrip')
            <main class="dw-content">
                @yield('content')
            </main>
        </div>
    </div>
@endif

    <span id="dw-live" class="dw-sr" role="status" aria-live="polite"></span>

    <script src="{{ asset('assets/core/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/core/js/ddwl.js') }}"></script>
    @stack('scripts')
</body>
</html>
