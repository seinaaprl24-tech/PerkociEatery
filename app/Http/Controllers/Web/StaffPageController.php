<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Halaman dashboard staff (kasir, admin, manager).
 *
 * Route-nya dilindungi middleware auth + role (lihat routes/web.php).
 * Isi halaman masih memakai data contoh dari config/preview.php sampai
 * tiap halaman disambungkan ke API.
 */
class StaffPageController extends Controller
{
    public function dashboard(Request $request, string $role): View
    {
        return $this->render($request, 'staff.'.$role, $role);
    }

    public function page(Request $request, string $role, string $page): View
    {
        return $this->render($request, 'staff.'.$role.'.'.$page, $role);
    }

    private function render(Request $request, string $view, string $role): View
    {
        $data = config('preview.roles.'.$role);

        abort_if($data === null || ! view()->exists($view), 404);

        $this->ensureApiToken($request);

        return view($view, [
            'role' => $data,
            'preview' => config('preview'),
        ]);
    }

    /**
     * Pastikan sesi punya token API yang masih berlaku. Dibutuhkan, misalnya,
     * saat login lewat "ingat saya" atau setelah token dicabut (ganti password).
     */
    private function ensureApiToken(Request $request): void
    {
        $user = $request->user();

        if (! $user) {
            return;
        }

        $token = $request->session()->get('api_token');

        if (is_string($token) && $token !== '' && PersonalAccessToken::findToken($token)) {
            return;
        }

        $user->tokens()->where('name', AuthController::TOKEN_NAME)->delete();

        $request->session()->put(
            'api_token',
            $user->createToken(AuthController::TOKEN_NAME)->plainTextToken
        );
    }
}
