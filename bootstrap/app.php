<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CartCount;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\DeveloperMiddleware;
use App\Http\Middleware\TrackVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            CartCount::class,
            TrackVisit::class,
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'developer' => DeveloperMiddleware::class,
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
