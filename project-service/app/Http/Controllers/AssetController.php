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
        // 1. Cari data aset berdasarkan ID
        $asset = Asset::find($id);

        // 2. Jika aset tidak ditemukan, berikan respon 404
        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Aset tidak ditemukan.'
            ], 404);
        }

        // 3. Pastikan file ada di storage
        // Asumsi: path disimpan di kolom 'file_path'
        if (!Storage::exists($asset->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan di server.'
            ], 404);
    }

    // 4. Dapatkan path lengkap file
    $path = storage_path('app/' . $asset->file_path);

    // 5. Kembalikan file sebagai response agar bisa ditampilkan (preview)
    return response()->file($path);
    }
    // 1. Menampilkan semua aset beserta kategori/tag-nya
    public function index()
    {
        $assets = Asset::all();
        return response()->json([
            'success' => true,
            'message' => 'Daftar semua aset digital berhasil dimuat.',
            'data'    => $assets
        ], 200);
    }

    // 2. Menghandle upload file aset digital ke storage local
    public function store(Request $request)
    {
        // 1. Validasi request
        $request->validate([
            'file' => 'required|file|max:10240', // Maksimal 10MB
            'category' => 'required|string',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Mengambil nama asli atau nama dari input Postman
            $filename = $request->input('name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $finalName = $filename . '-' . time() . '.' . $extension;

            // Menyimpan file fisik ke storage/app/public/assets
            $path = $file->storeAs('assets', $finalName, 'public');

            // Mengambil tipe file dan ukuran file (Bytes)
            $fileType = $file->getClientMimeType();
            $fileSize = $file->getSize(); // <-- AMBIL UKURAN FILE DISINI

            // 2. Simpan informasi ke database project_db
            $asset = Asset::create([
                'file_name' => $filename,
                'category' => $request->category,
                'file_path' => $path,
                'file_type' => $fileType,
                'file_size' => $fileSize, // <-- MASUKKAN KE KUE RI DATABASE
            ]);

            return response()->json([
                'message' => 'Asset berhasil diunggah!',
                'data' => $asset
            ], 201);
        }

        return response()->json(['message' => 'File tidak ditemukan.'], 400);
    }

    // 3. Membuat link unduhan khusus (share token) yang bisa dibagikan ke klien
    public function generateShareLink($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Aset tidak ditemukan.'
            ], 404);
        }

        // Jika belum ada token, buat token acak unik sepanjang 32 karakter
        if (empty($asset->share_token)) {
            $asset->update([
                'share_token' => Str::random(32)
            ]);
        }

        // Susun full URL untuk download (misalnya endpoint download ditaruh di gateway atau service ini langsung)
        $downloadUrl = url('/api/assets/download/' . $asset->share_token);

        return response()->json([
            'success' => true,
            'message' => 'Link unduhan khusus berhasil dibuat.',
            'share_link' => $downloadUrl,
            'data' => $asset
        ], 200);
    }

    public function show($id)
    {
        // Mencari aset berdasarkan ID
        $asset = Asset::find($id);

        // Jika aset tidak ditemukan, kembalikan respon 404
        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Aset tidak ditemukan.'
            ], 404);
        }

        // Jika ditemukan, kembalikan data aset
        return response()->json([
            'success' => true,
            'data' => $asset
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // Cari aset berdasarkan ID
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Aset tidak ditemukan.'
            ], 404);
        }

        // Validasi input edit (opsional, sesuaikan kebutuhan)
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string',
        ]);

        // Update data teks di database
        $asset->update([
            'file_name' => $request->input('name', $asset->name),
            'category' => $request->input('category', $asset->category),
        ]);

        // Opsi Tambahan: Jika saat edit user juga ingin mengganti FILE fisiknya
        if ($request->hasFile('file')) {
            // Hapus file lama terlebih dahulu dari storage agar tidak penuh
            if (Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }

            // Simpan file baru dengan nama yang sudah diperbarui
            $file = $request->file('file');
            $customName = Str::slug($request->input('name', $asset->name));
            $extension = $file->getClientOriginalExtension();
            $fileName = $customName . '-' . time() . '.' . $extension;
            
            $path = $file->storeAs('assets', $fileName, 'public');
            
            // Update path baru di DB
            $asset->update(['file_path' => $path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Aset berhasil diperbarui!',
            'data' => $asset
        ], 200);
    }

    public function destroy($id)
    {
        $asset = Asset::find($id);

        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Aset tidak ditemukan.'], 404);
        }

        // Hapus file dari storage
        if (Storage::exists($asset->file_path)) {
            Storage::delete($asset->file_path);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aset berhasil dihapus.'
        ], 200);
    }
}