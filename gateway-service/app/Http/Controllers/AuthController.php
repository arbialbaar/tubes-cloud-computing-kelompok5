<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    private $authServiceUrl;

    public function __construct()
    {
        $this->authServiceUrl = env('AUTH_SERVICE_URL', 'http://dam-auth-service:8000');
    }

    public function showLogin()
    {
        if (Session::has('token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = Http::post($this->authServiceUrl . '/api/login', [
                'email'    => $request->email,
                'password' => $request->password,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['access_token'])) {
                // Auth-service pakai JWT: field-nya access_token, bukan token
                Session::put('token', $data['access_token']);
                Session::put('user', $data['user']);
                return redirect()->route('dashboard');
            }

            $errorMsg = $data['error'] ?? 'Email atau password salah.';
            return back()->withErrors(['email' => $errorMsg])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Auth service tidak dapat dihubungi: ' . $e->getMessage()])->withInput();
        }
    }

    public function showRegister()
    {
        if (Session::has('token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:Admin,Kontributor,Client',
        ]);

        try {
            $response = Http::post($this->authServiceUrl . '/api/register', [
                'name'                  => $request->name,
                'email'                 => $request->email,
                'password'              => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'role'                  => $request->role,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                return redirect()->route('login')
                    ->with('success', 'Registrasi berhasil! Silakan login.');
            }

            // Tampilkan error validasi dari auth-service
            $errors = [];
            foreach ($data as $field => $messages) {
                $errors[$field] = is_array($messages) ? implode(', ', $messages) : $messages;
            }
            return back()->withErrors($errors)->withInput();

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Auth service tidak dapat dihubungi: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }

    public function dashboard()
    {
        $user = Session::get('user');
        return view('dashboard', compact('user'));
    }
}
