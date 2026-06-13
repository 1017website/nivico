<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gating per-permission. Dipakai: ->middleware('permission:products.manage')
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki akses ke menu ini.');
        }
        return $next($request);
    }
}
