<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\GatewayController;

/*
|--------------------------------------------------------------------------
| API Routes - Gateway Service (Reverse Proxy)
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. ROUTING UNTUK AUTH SERVICE (Port 8001)
// ==========================================
Route::any('/auth/{any?}', [GatewayController::class, 'proxyToAuth'])
    ->where('any', '.*');


// ==========================================
// 2. ROUTING UNTUK PROJECT SERVICE (Port 8002)
// ==========================================
// Delegasikan ke GatewayController agar multipart/file upload & edit nama ditangani dengan benar
Route::any('/assets/{any?}', [GatewayController::class, 'proxyToProject'])
    ->where('any', '.*');