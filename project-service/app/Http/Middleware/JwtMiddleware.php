<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
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

        // 2. Ekstrak string token JWT-nya saja
        $token = str_replace('Bearer ', '', $authorizationHeader);

        try {
            // Kita pecah struktur JWT (Header.Payload.Signature)
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                return response()->json(['success' => false, 'message' => 'Format token tidak valid!'], 401);
            }

            // Decode payload untuk mengambil data user & role
            $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);

            // Cek apakah token sudah kedaluwarsa (exp)
            if (isset($payload['exp']) && time() >= $payload['exp']) {
                return response()->json(['success' => false, 'message' => 'Token telah kedaluwarsa, silakan login ulang!'], 401);
            }

            // 3. Simpan data user ke dalam request agar bisa diakses di Controller jika dibutuhkan
            $request->attributes->add(['user_data' => $payload]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau tidak dikenali!'
            ], 401);
        }

        return $next($request);
    }
}