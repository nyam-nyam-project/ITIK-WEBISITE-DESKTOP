<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->wajib_ganti_password) {
            // Rute yang diizinkan saat wajib ganti password
            if (! $request->routeIs('password.wajib', 'password.wajib.update', 'logout')) {
                return redirect()->route('password.wajib');
            }
        }

        return $next($request);
    }
}

