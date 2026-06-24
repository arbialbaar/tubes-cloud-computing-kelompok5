<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    private function getUserData(Request $request)
    {
        return $request->attributes->get('user_data', []);
    }

    private function isAdmin(Request $request)
    {
        $userData = $this->getUserData($request);
        return ($userData['role'] ?? null) === 'Admin';
    }

    private function canEdit(Request $request, Asset $asset)
    {
        $userData = $this->getUserData($request);
        $userEmail = $userData['email'] ?? null;
        return $this->isAdmin($request) || ($asset->user_email === $userEmail);
    }

    public function preview($id)
    {
        $asset = Asset::find($id);
        if (!$asset) return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        if (!Storage::disk('public')->exists($asset->file_path)) return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
        return response()->file(Storage::disk('public')->path($asset->file_path));
    }

    public function download($token)
    {
        $asset = Asset::where('share_token', $token)->first();
        if (!$asset) return response()->json(['success' => false, 'message' => 'Token tidak valid.'], 404);
        if (!Storage::disk('public')->exists($asset->file_path)) return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
        return response()->download(Storage::disk('public')->path($asset->file_path), $asset->file_name);
    }

    public function index(Request $request)
    {
        $userData = $this->getUserData($request);
        $userEmail = $userData['email'] ?? null;
        $isAdmin = $this->isAdmin($request);
        $query = Asset::orderBy('created_at', 'desc');
        if (!$isAdmin) {
            $query->where('user_email', $userEmail);
        }
        return response()->json(['success' => true, 'data' => $query->get()], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|max:10240',
            'category' => 'required|string',
            'tags'     => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $userData  = $this->getUserData($request);
            $file      = $request->file('file');
            $filename  = $request->input('name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $finalName = $filename . '-' . time() . '.' . $extension;
            $path      = $file->storeAs('assets', $finalName, 'public');
            $asset = Asset::create([
                'file_name'  => $filename,
                'category'   => $request->category,
                'tags'       => $request->input('tags', ''),
                'file_path'  => $path,
                'file_type'  => $file->getClientMimeType(),
                'file_size'  => $file->getSize(),
                'version'    => 1,
                'user_email' => $userData['email'] ?? 'unknown',
                'user_role'  => $userData['role'] ?? 'user',
            ]);
            return response()->json(['message' => 'Asset berhasil diunggah!', 'data' => $asset], 201);
        }
        return response()->json(['message' => 'File tidak ditemukan.'], 400);
    }

    public function generateShareLink($id, Request $request)
    {
        $asset = Asset::find($id);
        if (!$asset) return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        if (empty($asset->share_token)) {
            $asset->update(['share_token' => Str::random(32)]);
        }
        return response()->json([
            'success'    => true,
            'message'    => 'Link sharing berhasil dibuat.',
            'share_link' => url('/api/assets/download/' . $asset->share_token),
            'data'       => $asset
        ], 200);
    }

    public function show($id, Request $request)
    {
        $asset = Asset::find($id);
        if (!$asset) return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        if (!$this->isAdmin($request) && !$this->canEdit($request, $asset)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        return response()->json(['success' => true, 'data' => $asset], 200);
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);
        if (!$asset) return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        if (!$this->canEdit($request, $asset)) {
            return response()->json(['success' => false, 'message' => 'Anda hanya bisa edit aset milik sendiri.'], 403);
        }
        $asset->update([
            'file_name' => $request->input('name', $asset->file_name),
            'category'  => $request->input('category', $asset->category),
            'tags'      => $request->input('tags', $asset->tags),
        ]);
        if ($request->hasFile('file')) {
            if (Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }
            $file = $request->file('file');
            $customName = Str::slug($request->input('name', $asset->file_name));
            $path = $file->storeAs('assets', $customName . '-' . time() . '.' . $file->getClientOriginalExtension(), 'public');
            $asset->update([
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'version'   => $asset->version + 1,
            ]);
        }
        return response()->json(['success' => true, 'message' => 'Aset berhasil diperbarui!', 'data' => $asset], 200);
    }

    public function destroy($id, Request $request)
    {
        $asset = Asset::find($id);
        if (!$asset) return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        if (!$this->canEdit($request, $asset)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        if (Storage::disk('public')->exists($asset->file_path)) {
            Storage::disk('public')->delete($asset->file_path);
        }
        $asset->delete();
        return response()->json(['success' => true, 'message' => 'Aset berhasil dihapus.'], 200);
    }
}
