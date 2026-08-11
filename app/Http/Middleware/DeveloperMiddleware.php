<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeveloperMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isDeveloper()) {
            abort(403, 'Akses khusus developer.');
        }

        return $next($request);
    }
}
