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
Route::any('/auth/{any}', function (Request $request, $any) {
    // Mengambil URL internal Auth Service dari file .env
    $authUrl = env('AUTH_SERVICE_URL', 'http://127.0.0.1:8001');

    // Meneruskan request beserta method, headers, dan body data
    $response = Http::withHeaders($request->headers->all())
        ->send($request->method(), $authUrl . '/api/' . $any, [
            'body'  => $request->getContent(),
            'query' => $request->query()
        ]);

    return response()->json($response->json(), $response->status());
})->where('any', '.*');


// ==========================================
// 2. ROUTING UNTUK PROJECT SERVICE (Port 8002)
// ==========================================
// Delegasikan ke GatewayController agar multipart/file upload & edit nama ditangani dengan benar
Route::any('/assets/{any?}', [GatewayController::class, 'proxyToProject'])
    ->where('any', '.*');