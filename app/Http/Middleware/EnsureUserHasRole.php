<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $role = strtolower(trim((string) $request->user()->role));
        $allowedRoles = array_map(
            fn (string $allowed) => strtolower(trim($allowed)),
            $roles,
        );

        if (! in_array($role, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
