<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayController extends Controller
{
    /**
     * Meneruskan request ke Auth Service (Port 8001)
     */
    public function proxyToAuth(Request $request, $any = null)
    {
        $baseUrl = env('AUTH_SERVICE_URL', 'http://127.0.0.1:8001');
        // Mendapatkan path asli yang diminta, misal: api/auth/login
        $path = $request->path(); 
        $fullUrl = "{$baseUrl}/{$path}";

        return $this->forwardRequest($request, $fullUrl);
    }


    /**
     * Meneruskan request ke Project Service (Port 8002)
     */
    public function proxyToProject(Request $request, $any = null)
    {
   // 1. Tentukan URL internal menuju Project Service
        $url = env('PROJECT_SERVICE_URL', 'http://127.0.0.1:8002') . '/api/assets';

        // 2. Ambil token JWT bawaan dari header permintaan Client (Postman)
        $token = $request->bearerToken();

        // 3. Inisialisasi HTTP Client dengan Header Authorization
        $client = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ]);

        // 4. TRICK UTAMA: Periksa apakah ada file yang diunggah
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Pasang file fisik ke dalam HTTP Client sebelum di-POST
            $client->attach(
                'file', // Nama key harus sama dengan yang dicari di Project Service
                file_get_contents($file->getRealPath()), 
                $file->getClientOriginalName()
            );

            // Ambil semua data teks lainnya (seperti key: name, category, dll)
            $inputs = $request->except('file');
            
            // Kirim sebagai multipart data
            $response = $client->post($url, $inputs);
        } else {
            // Jika request POST biasa tanpa file (fallback)
            $response = $client->post($url, $request->all());
        }

        // 5. Kembalikan respon dari Project Service secara utuh ke Client
        return response()->json($response->json(), $response->status());
    }
    }

    /**
     * Logika utama untuk meneruskan method, headers, multipart/form-data, dan body
     */
    private function forwardRequest(Request $request, $url)
    {
        $method = $request->method();
        $headers = $request->headers->all();

        // Menyaring header agar tidak bentrok dengan server gateway sendiri
        $cleanHeaders = [];
        foreach ($headers as $key => $value) {
            if (!in_array(strtolower($key), ['host', 'content-length'])) {
                $cleanHeaders[$key] = $value[0];
            }
        }

        // Jika request membawa file fisik (multipart/form-data seperti fitur upload aset)
        if ($request->isJson() === false && count($request->allFiles()) > 0) {
            $pendingRequest = Http::withHeaders($cleanHeaders);
            
            // Masukkan semua file fisik ke dalam multipart request
            foreach ($request->allFiles() as $key => $file) {
                $pendingRequest->attach(
                    $key, 
                    file_get_contents($file->getPathname()), 
                    $file->getClientOriginalName()
                );
            }
            
            // Masukkan data teks lainnya ke dalam multipart
            $response = $pendingRequest->send($method, $url, [
                'form_params' => $request->except(array_keys($request->allFiles()))
            ]);
        } else {
            // Jika request berupa JSON biasa (seperti login, register, update status)
            $response = Http::withHeaders($cleanHeaders)->send($method, $url, [
                'json' => $request->all()
            ]);
        }

        // Mengembalikan respon asli dari microservice ke client beserta status kodenya
        return response()->json($response->json(), $response->status());
    }
}