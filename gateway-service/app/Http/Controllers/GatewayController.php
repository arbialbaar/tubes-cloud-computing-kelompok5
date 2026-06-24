<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayController extends Controller
{
    /**
     * Meneruskan request ke Auth Service (Port 8001)
     */
    public function proxyToAuth(Request $request)
    {
        $uri = $request->getRequestUri();
        // Ubah /api/auth menjadi /api untuk disesuaikan dengan routing internal auth-service
        if (str_starts_with($uri, '/api/auth')) {
            $uri = '/api' . substr($uri, 9);
        }
        $url = env('AUTH_SERVICE_URL', 'http://dam-auth-service:8000') . $uri;
        return $this->forwardRequest($request, $url);
    }

    /**
     * Meneruskan request ke Project Service (Port 8002)
     */
    public function proxyToProject(Request $request)
    {
        $url = env('PROJECT_SERVICE_URL', 'http://dam-project-service:8000') . $request->getRequestUri();
        return $this->forwardRequest($request, $url);
    }

    /**
     * Logika UTAMA penerus request (Otomatis handle File & JSON/Form-Data)
     */
    private function forwardRequest(Request $request, $url)
    {
        $method = strtolower($request->method());

        // 1. Bersihkan & normalisasi header
        //    headers->all() mengembalikan array-of-arrays, Guzzle butuh scalar string
        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            // Lewati header yang bisa bikin bentrok dengan Guzzle/downstream
            if (in_array(strtolower($key), ['host', 'content-length', 'content-type', 'transfer-encoding'])) {
                continue;
            }
            $headers[$key] = implode(', ', $values);
        }

        // Auto-inject token JWT dari Session jika request berasal dari front-end browser
        if (!isset($headers['Authorization']) && \Illuminate\Support\Facades\Session::has('token')) {
            $headers['Authorization'] = 'Bearer ' . \Illuminate\Support\Facades\Session::get('token');
        }

        // 2. Cek apakah ada file yang dikirim
        if (count($request->allFiles()) > 0) {

            // Mulai dengan PendingRequest dasar
            $client = Http::withHeaders($headers)->timeout(30);

            // Tempelkan setiap file — WAJIB di-reassign karena attach() mengembalikan instance baru
            foreach ($request->allFiles() as $key => $file) {
                $client = $client->attach(
                    $key,
                    file_get_contents($file->getPathname()),
                    $file->getClientOriginalName()
                );
            }

            // Ambil semua field teks (name, category, _method, dll.) kecuali file
            $textData = $request->except(array_keys($request->allFiles()));

            // Paksa POST — edit multipart menggunakan _method=PUT di form-data
            $response = $client->post($url, $textData);

        } elseif (in_array($method, ['get', 'delete', 'head'])) {
            // GET/DELETE tidak punya body — kirim query string saja jika ada
            $client = Http::withHeaders($headers)->timeout(30);
            $response = $client->$method($url);

        } else {
            // POST/PUT/PATCH tanpa file — kirim sebagai form params
            $client = Http::withHeaders($headers)->timeout(30);
            $response = $client->asForm()->$method($url, $request->all());
        }

        // 3. Kembalikan respon utuh dari microservice ke client/Postman
        return response($response->body(), $response->status())
                ->withHeaders($response->headers());
    }
}