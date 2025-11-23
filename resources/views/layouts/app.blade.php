<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Magyar Városok')</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        nav a { margin-right: 15px; text-decoration: none; color: #0066cc; }
        nav a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .success { background-color: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; }
        form { margin: 20px 0; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 5px; margin-top: 5px; }
        button, .btn { padding: 8px 15px; margin: 5px 5px 5px 0; cursor: pointer; text-decoration: none; display: inline-block; border: 1px solid #000; background: #fff; }
        button:hover, .btn:hover { background: #f0f0f0; }
        .filter-section { border: 1px solid #ccc; padding: 15px; margin: 20px 0; }
        .letter-btn { display: inline-block; padding: 5px 10px; margin: 5px; border: 1px solid #000; text-decoration: none; color: #000; }
        .letter-btn:hover { background: #f0f0f0; }
        .letter-btn.active { background: #000; color: #fff; }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <nav>
        <strong>Magyar Városok</strong> | 
        <a href="{{ route('dashboard') }}">Főoldal</a>
        <a href="{{ route('counties.index') }}">Megyék</a>
        <a href="{{ route('cities.index') }}">Városok</a>
        <span style="float: right;">
            {{ Auth::user()->name }} | 
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit">Kijelentkezés</button>
            </form>
        </span>
    </nav>
    @endauth

    @if(session('success'))
    <div class="success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="error">{{ session('error') }}</div>
    @endif

    @yield('content')

    @stack('scripts')
</body>
</html>