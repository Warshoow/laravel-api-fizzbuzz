<?php

use App\Http\Middleware\EnsureApiKey;
use App\Http\Middleware\ErrorManager;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Adding middleware on every route, optional
        // $middleware->append(EnsureApiKey::class);
        // $middleware->append(ErrorManager::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
