<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Nama token API yang dibuat otomatis untuk JavaScript dashboard.
     */
    public const TOKEN_NAME = 'web-dashboard';

    public function showLogin(Request $request): View|RedirectResponse
    {
        // Dipakai jika sesi web masih ada tapi token API bermasalah: paksa login ulang.
        if ($request->boolean('expired') && Auth::check()) {
            $this->endSession($request);
        }

        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Token untuk JavaScript dashboard (memanggil /api dengan Bearer token).
        $user->tokens()->where('name', self::TOKEN_NAME)->delete();
        $request->session()->put(
            'api_token',
            $user->createToken(self::TOKEN_NAME)->plainTextToken
        );

        return redirect($this->homeFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->endSession($request);

        return redirect('/login');
    }

    /**
     * Hapus token dashboard, lalu akhiri sesi web.
     */
    private function endSession(Request $request): void
    {
        $request->user()?->tokens()->where('name', self::TOKEN_NAME)->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function homeFor(User $user): string
    {
        return match ($user->role) {
            'admin' => '/admin',
            'kasir' => '/kasir',
            'manager' => '/manager',
            default => '/',
        };
    }
}
