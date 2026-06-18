<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

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
            'body' => $request->getContent(),
            'query' => $request->query()
        ]);

    return response()->json($response->json(), $response->status());
})->where('any', '.*');


// ==========================================
// 2. ROUTING UNTUK PROJECT SERVICE (Port 8002)
// ==========================================
Route::any('/assets/{any?}', function (Request $request, $any = null) {
    $url = env('PROJECT_SERVICE_URL', 'http://127.0.0.1:8002') . '/api/assets' . ($any ? '/' . $any : '');

    $response = Http::withHeaders($request->headers->all())
                    ->send($request->method(), $url, [
                        'body' => $request->getContent()
                    ]);

    return response()->json($response->json(), $response->status());
})->where('any', '.*');