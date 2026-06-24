<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Aset — CloudApp</title>
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
        .container { max-width: 1100px; margin: 32px auto; padding: 0 24px; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #ffeaea; color: #c0392b; border: 1px solid #f5c6cb; }
        .alert-share { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; word-break: break-all; }
        .card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .card h2 { font-size: 18px; color: #333; margin-bottom: 20px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px; }
        .form-group input, .form-group select { width: 100%; padding: 10px 14px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px; outline: none; background: white; }
        .form-group input:focus, .form-group select:focus { border-color: #667eea; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-danger { background: #fee2e2; color: #dc2626; }
        .btn-info { background: #eff6ff; color: #1d4ed8; }
        .btn-warning { background: #fef3c7; color: #d97706; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 12px 16px; text-align: left; font-size: 13px; color: #555; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #444; vertical-align: middle; }
        tr:hover td { background: #f8fafc; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-gambar { background: #dbeafe; color: #1d4ed8; }
        .badge-dokumen { background: #fef3c7; color: #d97706; }
        .badge-video { background: #fce7f3; color: #9d174d; }
        .badge-audio { background: #d1fae5; color: #065f46; }
        .badge-lainnya { background: #f3f4f6; color: #6b7280; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .preview-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; }
        .preview-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border-radius: 6px; font-size: 24px; }
        .version-badge { background: #667eea; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
        .empty-state { text-align: center; padding: 40px; color: #888; }
        .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; }
        .modal h3 { margin-bottom: 20px; color: #333; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .file-size { color: #888; font-size: 12px; }
        .tag-pill { display: inline-block; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin: 2px; }
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
    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif
    @if(session('share_link'))
        <div class="alert alert-share">
            🔗 <strong>Link Sharing (bisa dibagikan tanpa login):</strong><br><br>
            <a href="{{ session('share_link') }}" target="_blank">{{ session('share_link') }}</a>
            <br><br>
            <small>Klik link di atas untuk download langsung</small>
        </div>
    @endif

    {{-- Upload Form --}}
    <div class="card">
        <h2>📤 Upload Aset Digital</h2>
        <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Aset (opsional)</label>
                    <input type="text" name="name" placeholder="Nama file (tanpa ekstensi)">
                </div>
                <div class="form-group">
                    <label>Kategori <span style="color:red">*</span></label>
                    <select name="category" required>
                        <option value="" disabled selected>-- Pilih Kategori --</option>
                        <option value="Gambar">🖼️ Gambar</option>
                        <option value="Dokumen">📄 Dokumen</option>
                        <option value="Video">🎬 Video</option>
                        <option value="Audio">🎵 Audio</option>
                        <option value="Lainnya">📦 Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>File <span style="color:red">*</span></label>
                    <input type="file" name="file" required>
                </div>
		<div class="form-group">
    		    <label>Ganti File (opsional)</label>
    		    <input type="file" name="file">
		</div>
                <div class="form-group">
                    <label>Tags (pisahkan dengan koma)</label>
                    <input type="text" name="tags" placeholder="desain, logo, 2024">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">📤 Upload Aset</button>
        </form>
    </div>

    {{-- Asset List --}}
    <div class="card">
        <h2>📁 Daftar Aset Digital
            <span style="background:#f3f4f6;color:#666;padding:3px 10px;border-radius:12px;font-size:13px;margin-left:8px;">
                {{ count($assets) }} aset
            </span>
        </h2>

        @if(count($assets) > 0)
        <table>
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Tags</th>
                    <th>Ukuran</th>
                    <th>Versi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                <tr>
                    <td>
                        @if(str_contains($asset['file_type'] ?? '', 'image'))
                            <img src="http://192.168.56.102:8002/api/assets/preview/{{ $asset['id'] }}"
                                 class="preview-thumb" alt="preview"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="preview-icon" style="display:none">🖼️</div>
                        @elseif(str_contains($asset['file_type'] ?? '', 'pdf'))
                            <div class="preview-icon">📄</div>
                        @elseif(str_contains($asset['file_type'] ?? '', 'video'))
                            <div class="preview-icon">🎬</div>
                        @elseif(str_contains($asset['file_type'] ?? '', 'audio'))
                            <div class="preview-icon">🎵</div>
                        @else
                            <div class="preview-icon">📦</div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $asset['file_name'] }}</strong><br>
                        <span class="file-size">{{ $asset['file_type'] ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ strtolower($asset['category']) }}">
                            {{ $asset['category'] }}
                        </span>
                    </td>
                    <td>
                        @if(!empty($asset['tags']))
                            @foreach(explode(',', $asset['tags']) as $tag)
                                <span class="tag-pill">{{ trim($tag) }}</span>
                            @endforeach
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>
                    <td class="file-size">
                        {{ $asset['file_size'] ? number_format($asset['file_size'] / 1024, 1) . ' KB' : '-' }}
                    </td>
                    <td><span class="version-badge">v{{ $asset['version'] ?? 1 }}</span></td>
                    <td>
                        <div class="actions">
                            <button class="btn btn-warning btn-sm"
                                onclick="openEdit({{ $asset['id'] }}, '{{ addslashes($asset['file_name']) }}', '{{ $asset['category'] }}', '{{ addslashes($asset['tags'] ?? '') }}')">
                                ✏️ Edit
                            </button>
                            <form method="POST" action="{{ route('assets.share', $asset['id']) }}" style="margin:0">
                                @csrf
                                <button type="submit" class="btn btn-info btn-sm">🔗 Share</button>
                            </form>
                            <form method="POST" action="{{ route('assets.destroy', $asset['id']) }}" style="margin:0"
                                onsubmit="return confirm('Hapus aset ini?')">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <div class="icon">📭</div>
            <p>Belum ada aset. Upload file pertama kamu!</p>
        </div>
        @endif
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>✏️ Edit Aset</h3>
        <form method="POST" id="editForm"<form method="POST" id="editForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Nama Aset</label>
                <input type="text" name="name" id="editName" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category" id="editCategory" required>
                    <option value="Gambar">🖼️ Gambar</option>
                    <option value="Dokumen">📄 Dokumen</option>
                    <option value="Video">🎬 Video</option>
                    <option value="Audio">🎵 Audio</option>
                    <option value="Lainnya">📦 Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tags (pisahkan dengan koma)</label>
                <input type="text" name="tags" id="editTags" placeholder="desain, logo, 2024">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-danger" onclick="closeEdit()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, category, tags) {
    document.getElementById('editName').value = name;
    document.getElementById('editCategory').value = category;
    document.getElementById('editTags').value = tags;
    document.getElementById('editForm').action = '/assets/' + id + '/update';
    document.getElementById('editModal').classList.add('active');
}
function closeEdit() {
    document.getElementById('editModal').classList.remove('active');
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
</script>
</body>
</html>
