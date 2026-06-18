<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;

// --- ROUTE PUBLIK (Bisa diakses tanpa token / untuk fitur Share Link) ---
Route::get('/assets/preview/{id}', [AssetController::class, 'preview']);
Route::get('/assets/download/{token}', [AssetController::class, 'download']); // jika ada fungsi download lewat share_token

// --- ROUTE TERPROTEKSI JWT (Wajib membawa Bearer Token dari Auth Service) ---
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/assets', [AssetController::class, 'store']);       // Upload aset
    Route::get('/assets', [AssetController::class, 'index']);        // Lihat semua aset
    Route::get('/assets/{id}', [AssetController::class, 'show']);    // Lihat detail aset tunggal
    Route::post('/assets/{id}', [AssetController::class, 'update']); // Edit aset (Menggunakan spoofing POST)
    Route::delete('/assets/{id}', [AssetController::class, 'destroy']); // Hapus aset
    Route::post('/assets/{id}/share', [AssetController::class, 'generateShareLink']); // Membuat link share baru
});