<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — CloudApp</title>
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
        input, select {
            width: 100%; padding: 12px 16px;
            border: 2px solid #e1e5e9; border-radius: 8px;
            font-size: 15px; transition: border-color 0.2s; outline: none;
            background: white;
        }
        input:focus, select:focus { border-color: #667eea; }
        .alert-error {
            background: #ffeaea; border: 1px solid #f5c6cb;
            color: #c0392b; padding: 12px 14px;
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
        .role-badge { font-size: 11px; color: #888; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <h1>☁️ CloudApp</h1>
            <p>Buat akun baru</p>
        </div>

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="John Doe" required autofocus>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="contoh@email.com" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- Pilih Role --</option>
                    <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Kontributor" {{ old('role') == 'Kontributor' ? 'selected' : '' }}>Kontributor</option>
                    <option value="Client" {{ old('role') == 'Client' ? 'selected' : '' }}>Client</option>
                </select>
                <div class="role-badge">Admin: akses penuh | Kontributor: upload & edit | Client: lihat & download</div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation"
                       placeholder="Ulangi password" required>
            </div>
            <button type="submit" class="btn">Daftar</button>
        </form>

        <div class="link-text">
            Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </div>
    </div>
</body>
</html>
