<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman admin.');
        }

        if (! $user->is_admin || ! $user->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with('error', 'Akun Anda tidak memiliki akses ke halaman admin.');
        }

        return $next($request);
    }
}
