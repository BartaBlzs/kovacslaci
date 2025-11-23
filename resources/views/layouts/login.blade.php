<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; }
        h1 { text-align: center; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input { width: 100%; padding: 5px; margin-top: 5px; }
        button { width: 100%; padding: 10px; margin-top: 15px; cursor: pointer; }
        .error { color: red; font-size: 14px; margin: 10px 0; }
        a { color: #0066cc; }
    </style>
</head>
<body>
    <h1>Bejelentkezés</h1>
    
    @if($errors->any())
    <div class="error">
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required value="{{ old('email') }}">
        
        <label for="password">Jelszó:</label>
        <input type="password" name="password" id="password" required>
        
        <label>
            <input type="checkbox" name="remember"> Emlékezz rám
        </label>
        
        <button type="submit">Bejelentkezés</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        <a href="{{ route('register') }}">Nincs még fiókod? Regisztrálj!</a>
    </p>
</body>
</html>