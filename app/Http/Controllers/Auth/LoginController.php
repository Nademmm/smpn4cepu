<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir portal masuk umum untuk seluruh pengguna sekolah.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('siswa')) {
                return redirect()->intended(route('quizzes'));
            }
            if ($user->hasRole(['super_admin', 'guru', 'staf_perpus', 'panitia_pilketos'])) {
                return redirect()->intended(route('filament.admin.pages.dashboard'));
            }
            return redirect()->intended(route('home'));
        }

        return view('pages.auth.login');
    }

    /**
     * Proses autentikasi masuk dari portal terpadu.
     */
    public function login(Request $request): RedirectResponse
    {
        if (!$request->filled('login_id') && $request->filled('login')) {
            $request->merge(['login_id' => $request->input('login')]);
        }

        $validated = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ], [
            'login_id.required' => 'Email atau NISN / Nama Akun wajib diisi.',
            'password.required' => 'Kata sandi akun wajib diisi.',
        ]);

        $loginInput = trim($validated['login_id']);
        $password = $validated['password'];
        $remember = $request->boolean('remember');

        $throttleKey = Str::lower($loginInput) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput($request->only('login_id', 'login', 'remember'))
                ->withErrors([
                    'login_id' => 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam ' . $seconds . ' detik.',
                    'login' => 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam ' . $seconds . ' detik.',
                ]);
        }

        // Cari user berdasarkan identifier unik terlebih dahulu (email, NISN, NIP), lalu fallback ke nama akun
        $user = User::where('email', $loginInput)
            ->orWhere('nisn', $loginInput)
            ->orWhere('nip', $loginInput)
            ->first() ?? User::where('name', $loginInput)->first();

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            if ($user->hasRole('siswa')) {
                return redirect()->intended(route('quizzes'))
                    ->with('status', 'Selamat datang, ' . $user->name . '! Anda berhasil masuk ke portal belajar.');
            }

            if ($user->hasRole(['super_admin', 'guru', 'staf_perpus', 'panitia_pilketos'])) {
                return redirect()->intended(route('filament.admin.pages.dashboard'))
                    ->with('status', 'Selamat datang kembali, ' . $user->name . '.');
            }

            return redirect()->intended(route('home'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withInput($request->only('login_id', 'login', 'remember'))
            ->withErrors([
                'login_id' => 'Akun atau kata sandi yang Anda masukkan tidak sesuai. Silakan periksa kembali atau hubungi pengelola sekolah.',
            ]);
    }

    /**
     * Keluar dari sesi akun aktif.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Anda telah berhasil keluar dari portal akun.');
    }
}
