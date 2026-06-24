<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthToken
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('token')) {
            return redirect()->route('login')->withErrors(['auth' => 'Silakan login terlebih dahulu.']);
        }
        return $next($request);
    }
}
