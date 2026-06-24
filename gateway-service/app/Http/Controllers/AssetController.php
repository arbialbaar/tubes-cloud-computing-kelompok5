<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AssetController extends Controller
{
    private $projectServiceUrl;

    public function __construct()
    {
        $this->projectServiceUrl = env('PROJECT_SERVICE_URL', 'http://dam-project-service:8000');
    }

    private function getToken()
    {
        return Session::get('token');
    }

    public function index()
    {
        try {
            $response = Http::withToken($this->getToken())
                ->withHeaders(['Accept' => 'application/json'])
                ->get($this->projectServiceUrl . '/api/assets');

            $assets = [];
            if ($response->successful()) {
                $assets = $response->json('data', []);
            }
        } catch (\Exception $e) {
            $assets = [];
        }

        return view('assets.index', compact('assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|max:10240',
            'category' => 'required|string',
            'tags'     => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            $customName = $request->input('name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

            $response = Http::withToken($this->getToken())
                ->withHeaders(['Accept' => 'application/json'])
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($this->projectServiceUrl . '/api/assets', [
                    'name'     => $customName,
                    'category' => $request->category,
                    'tags'     => $request->input('tags', ''),
                ]);

            if ($response->successful()) {
                return redirect()->route('assets.index')->with('success', 'Aset berhasil diunggah!');
            }

            return redirect()->route('assets.index')
                ->with('error', 'Gagal upload: ' . $response->body());

        } catch (\Exception $e) {
            return redirect()->route('assets.index')
                ->with('error', 'Project service tidak dapat dihubungi: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = Http::withToken($this->getToken())
                ->withHeaders(['Accept' => 'application/json'])
                ->post($this->projectServiceUrl . '/api/assets/' . $id, [
                    'name'     => $request->name,
                    'category' => $request->category,
                    'tags'     => $request->input('tags', ''),
                ]);

            if ($response->successful()) {
                return redirect()->route('assets.index')->with('success', 'Aset berhasil diperbarui!');
            }

            return redirect()->route('assets.index')->with('error', 'Gagal memperbarui aset.');

        } catch (\Exception $e) {
            return redirect()->route('assets.index')
                ->with('error', 'Project service tidak dapat dihubungi.');
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::withToken($this->getToken())
                ->withHeaders(['Accept' => 'application/json'])
                ->delete($this->projectServiceUrl . '/api/assets/' . $id);

            if ($response->successful()) {
                return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus!');
            }

            return redirect()->route('assets.index')->with('error', 'Gagal menghapus aset.');

        } catch (\Exception $e) {
            return redirect()->route('assets.index')
                ->with('error', 'Project service tidak dapat dihubungi.');
        }
    }

    public function generateShareLink($id)
    {
        try {
            $response = Http::withToken($this->getToken())
                ->withHeaders(['Accept' => 'application/json'])
                ->post($this->projectServiceUrl . '/api/assets/' . $id . '/share');

            if ($response->successful()) {
                // Ganti URL internal dengan URL yang bisa diakses browser (pakai IP VM)
                $shareToken = $response->json('data.share_token');
                $shareLink = 'http://192.168.56.102:8000/assets/download/' . $shareToken;
                return redirect()->route('assets.index')->with('share_link', $shareLink);
            }

            return redirect()->route('assets.index')->with('error', 'Gagal membuat link sharing.');

        } catch (\Exception $e) {
            return redirect()->route('assets.index')
                ->with('error', 'Project service tidak dapat dihubungi.');
        }
    }

    public function download($token)
    {
        // Proxy download dari project-service langsung
        try {
            $response = Http::withHeaders(['Accept' => '*/*'])
                ->get($this->projectServiceUrl . '/api/assets/download/' . $token);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');
                $contentDisposition = $response->header('Content-Disposition');

                return response($response->body(), 200)
                    ->header('Content-Type', $contentType)
                    ->header('Content-Disposition', $contentDisposition ?? 'attachment');
            }

            return redirect()->route('assets.index')->with('error', 'File tidak ditemukan.');

        } catch (\Exception $e) {
            return redirect()->route('assets.index')
                ->with('error', 'Gagal mengunduh file.');
        }
    }
}
