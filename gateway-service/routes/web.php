<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.token')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Asset routes
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::post('/assets/{id}/update', [AssetController::class, 'update'])->name('assets.update');
    Route::post('/assets/{id}/delete', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::post('/assets/{id}/share', [AssetController::class, 'generateShareLink'])->name('assets.share');
});

// Public download route (tanpa login)
Route::get('/assets/download/{token}', [AssetController::class, 'download'])->name('assets.download');
