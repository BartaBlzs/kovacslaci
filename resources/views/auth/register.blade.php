<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
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
    <h1>Regisztráció</h1>
    
    @if($errors->any())
    <div class="error">
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('register.post') }}" method="POST">
        @csrf
        
        <label for="name">Név:</label>
        <input type="text" name="name" id="name" required value="{{ old('name') }}">
        
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required value="{{ old('email') }}">
        
        <label for="password">Jelszó:</label>
        <input type="password" name="password" id="password" required>
        
        <label for="password_confirmation">Jelszó megerősítése:</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required>
        
        <button type="submit">Regisztráció</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        <a href="{{ route('login') }}">Már van fiókod? Jelentkezz be!</a>
    </p>
</body>
</html>