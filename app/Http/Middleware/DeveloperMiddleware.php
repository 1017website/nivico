<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class DeveloperMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $developerMigrationPending = Schema::hasTable('users')
            && ! Schema::hasColumn('users', 'is_developer');
        $canBootstrapDeveloper = $developerMigrationPending && $user?->isSuperAdmin();

        if (! $user?->isDeveloper() && ! $canBootstrapDeveloper) {
            abort(403, 'Akses khusus developer.');
        }

        return $next($request);
    }
}
