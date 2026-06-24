<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CloudApp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo h1 { color: #667eea; font-size: 28px; font-weight: 700; }
        .logo p { color: #888; font-size: 14px; margin-top: 4px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px; }
        input {
            width: 100%; padding: 12px 16px;
            border: 2px solid #e1e5e9; border-radius: 8px;
            font-size: 15px; transition: border-color 0.2s; outline: none;
        }
        input:focus { border-color: #667eea; }
        .alert-error {
            background: #ffeaea; border: 1px solid #f5c6cb;
            color: #c0392b; padding: 12px 14px;
            border-radius: 8px; font-size: 13px; margin-bottom: 18px;
        }
        .alert-success {
            background: #eafaf1; border: 1px solid #a9dfbf;
            color: #1e8449; padding: 12px 14px;
            border-radius: 8px; font-size: 13px; margin-bottom: 18px;
        }
        .btn {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; border: none; border-radius: 8px;
            font-size: 16px; font-weight: 600; cursor: pointer;
            transition: opacity 0.2s; margin-top: 6px;
        }
        .btn:hover { opacity: 0.9; }
        .link-text { text-align: center; margin-top: 22px; font-size: 14px; color: #666; }
        .link-text a { color: #667eea; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <h1>☁️ CloudApp</h1>
            <p>Kelompok 5 — Cloud Computing</p>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="contoh@email.com" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn">Masuk</button>
        </form>

        <div class="link-text">
            Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
        </div>
    </div>
</body>
</html>
