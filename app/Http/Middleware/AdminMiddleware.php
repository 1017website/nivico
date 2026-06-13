<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Akses khusus admin.');
        }

        if (! $user->is_active) {
            auth()->logout();
            abort(403, 'Akun Anda dinonaktifkan.');
        }

        // pastikan relasi role + permissions termuat untuk pengecekan menu
        if ($user->role_id && ! $user->relationLoaded('role')) {
            $user->load('role.permissions');
        }

        return $next($request);
    }
}
