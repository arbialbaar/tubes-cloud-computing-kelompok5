<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function preview($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        }
        if (!Storage::disk('public')->exists($asset->file_path)) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan di server.'], 404);
        }
        $path = Storage::disk('public')->path($asset->file_path);
        return response()->file($path);
    }

    public function download($token)
    {
        $asset = Asset::where('share_token', $token)->first();
        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid.'], 404);
        }
        if (!Storage::disk('public')->exists($asset->file_path)) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
        }
        $path = Storage::disk('public')->path($asset->file_path);
        return response()->download($path, $asset->file_name);
    }

    public function index()
    {
        $assets = Asset::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Daftar aset berhasil dimuat.',
            'data'    => $assets
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|max:10240',
            'category' => 'required|string',
            'tags'     => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $filename  = $request->input('name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $finalName = $filename . '-' . time() . '.' . $extension;
            $path      = $file->storeAs('assets', $finalName, 'public');
            $fileType  = $file->getClientMimeType();
            $fileSize  = $file->getSize();

            $asset = Asset::create([
                'file_name' => $filename,
                'category'  => $request->category,
                'tags'      => $request->input('tags', ''),
                'file_path' => $path,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'version'   => 1,
            ]);

            return response()->json([
                'message' => 'Asset berhasil diunggah!',
                'data'    => $asset
            ], 201);
        }

        return response()->json(['message' => 'File tidak ditemukan.'], 400);
    }

    public function generateShareLink($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        }

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

    public function show($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'data' => $asset], 200);
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        }

        $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string',
            'tags'     => 'nullable|string',
        ]);

        $asset->update([
            'file_name' => $request->input('name', $asset->file_name),
            'category'  => $request->input('category', $asset->category),
            'tags'      => $request->input('tags', $asset->tags),
        ]);

        if ($request->hasFile('file')) {
            if (Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }
            $file      = $request->file('file');
            $customName = Str::slug($request->input('name', $asset->file_name));
            $extension = $file->getClientOriginalExtension();
            $fileName  = $customName . '-' . time() . '.' . $extension;
            $path      = $file->storeAs('assets', $fileName, 'public');
            $asset->update([
                'file_path' => $path,
                'version'   => $asset->version + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Aset berhasil diperbarui!',
            'data'    => $asset
        ], 200);
    }

    public function destroy($id)
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        }
        if (Storage::disk('public')->exists($asset->file_path)) {
            Storage::disk('public')->delete($asset->file_path);
        }
        $asset->delete();
        return response()->json(['success' => true, 'message' => 'Aset berhasil dihapus.'], 200);
    }
}
