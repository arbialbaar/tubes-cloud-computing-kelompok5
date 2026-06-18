<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     * Validasi token ke Auth Service agar blacklist (pasca-logout) dipatuhi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ambil token dari Header Authorization
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan. Akses ditolak!'
            ], 401);
        }

        // 2. Verifikasi token ke Auth Service (bukan decode manual)
        //    Auth Service menggunakan tymon/jwt-auth yang mengelola blacklist secara otomatis.
        $authServiceUrl = env('AUTH_SERVICE_URL', 'http://127.0.0.1:8001');

        try {
            $response = Http::withHeaders([
                'Authorization' => $authorizationHeader,
                'Accept'        => 'application/json',
            ])->timeout(10)->get($authServiceUrl . '/api/validate-token');

            // 3. Jika auth service menolak (401 = token expired atau sudah di-blacklist)
            if ($response->status() === 401) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau sudah logout. Silakan login ulang!'
                ], 401);
            }

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memverifikasi token. Coba lagi nanti.'
                ], 503);
            }

            // 4. Token valid — simpan data user ke request agar bisa diakses controller
            $userData = $response->json('user');
            $request->attributes->add(['user_data' => $userData]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auth service tidak dapat dihubungi: ' . $e->getMessage()
            ], 503);
        }

        return $next($request);
    }
}