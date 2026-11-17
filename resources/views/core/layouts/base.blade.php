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
<body class="dw-body">
    <div class="dw-shell">
        @hasSection('sidebar')
            <aside class="dw-sidebar">@yield('sidebar')</aside>
        @endif
        <main class="dw-main">
            @yield('content')
        </main>
    </div>

    <span id="dw-live" class="dw-sr" role="status" aria-live="polite"></span>

    <script src="{{ asset('assets/core/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/core/js/ddwl.js') }}"></script>
    @stack('scripts')
</body>
</html>