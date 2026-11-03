<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · URL Shortener</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="site-shell">
        <header class="topbar">
            <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('login') }}" aria-label="Sembark URL Shortener home">
                <span class="brand-mark">↗URL Short</span>
            </a>
            @auth
                <div class="topbar-right">
                    <span class="signed-in">{{ auth()->user()->name }} <span class="role-chip">{{ auth()->user()->role }}</span></span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="button button-quiet" type="submit">Log out <span aria-hidden="true">→</span></button>
                    </form>
                </div>
            @endauth
        </header>

        @if (session('status'))
            <div class="notice notice-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice notice-error" role="alert">
                <strong>Check the details below.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
        <footer class="site-footer" style="text-align: center !importent"><span>&copy; 2026 URL SHORTENER </span></footer>
    </div>
    @stack('scripts')
</body>
</html>
