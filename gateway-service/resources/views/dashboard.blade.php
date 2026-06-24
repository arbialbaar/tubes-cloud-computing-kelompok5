<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — CloudApp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }
        .navbar {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; padding: 16px 32px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar h1 { font-size: 20px; font-weight: 700; }
        .nav-links { display: flex; gap: 12px; align-items: center; }
        .nav-link { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        .nav-link:hover { background: rgba(255,255,255,0.15); }
        .logout-btn { background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 8px 18px; border-radius: 8px; cursor: pointer; font-size: 14px; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 24px; }
        .welcome-card { background: white; border-radius: 16px; padding: 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .welcome-card h2 { color: #333; margin-bottom: 8px; font-size: 24px; }
        .welcome-card p { color: #666; font-size: 15px; margin-top: 6px; }
        .badges { margin-top: 14px; display: flex; gap: 10px; }
        .badge { display: inline-block; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .badge-email { background: #eef2ff; color: #667eea; }
        .badge-admin { background: #fef3c7; color: #d97706; }
        .badge-kontributor { background: #d1fae5; color: #065f46; }
        .badge-client { background: #e0f2fe; color: #0369a1; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .menu-card {
            background: white; border-radius: 12px; padding: 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center;
            text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s;
            display: block;
        }
        .menu-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .menu-card .icon { font-size: 36px; margin-bottom: 12px; }
        .menu-card h3 { color: #667eea; font-size: 16px; margin-bottom: 6px; }
        .menu-card p { color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>☁️ CloudApp DAM</h1>
        <div class="nav-links">
            <a href="{{ route('dashboard') }}" class="nav-link">🏠 Dashboard</a>
            <a href="{{ route('assets.index') }}" class="nav-link">📁 Aset</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Selamat datang, {{ $user['name'] ?? 'User' }}! 👋</h2>
            <p>Sistem Digital Asset Management (DAM) — Kelompok 5 Cloud Computing</p>
            <div class="badges">
                <span class="badge badge-email">{{ $user['email'] ?? '' }}</span>
                @php $role = $user['role'] ?? 'user'; @endphp
                <span class="badge badge-{{ strtolower($role) }}">{{ $role }}</span>
            </div>
        </div>

        <div class="grid">
            <a href="{{ route('assets.index') }}" class="menu-card">
                <div class="icon">📁</div>
                <h3>Kelola Aset Digital</h3>
                <p>Upload, edit, preview, dan bagikan aset digital</p>
            </a>
            <div class="menu-card" style="cursor:default">
                <div class="icon">🔐</div>
                <h3>Auth Service</h3>
                <p>JWT Authentication & RBAC aktif</p>
            </div>
        </div>
    </div>
</body>
</html>
