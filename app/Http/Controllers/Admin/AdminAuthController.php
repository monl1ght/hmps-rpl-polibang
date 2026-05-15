<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->is_admin && Auth::user()?->is_active) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('admin/pages/auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
            ],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, underscore, atau strip.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = strtolower(trim($validated['username']));

        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [$username])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password admin tidak sesuai.',
            ]);
        }

        if (! $user->is_admin) {
            throw ValidationException::withMessages([
                'username' => 'Akun ini tidak memiliki akses sebagai admin.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => 'Akun admin ini sedang dinonaktifkan.',
            ]);
        }

        Auth::login($user, (bool) ($validated['remember'] ?? false));

        $request->session()->regenerate();

        return redirect()
            ->intended(route('admin.dashboard'))
            ->with('success', 'Berhasil login sebagai admin.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('success', 'Anda berhasil logout dari halaman admin.');
    }
}
