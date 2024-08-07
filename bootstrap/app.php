<?php

use App\Http\Middleware\CORS;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
                  ->withRouting(
                      using: function () {
                          Route::prefix('api')
                               ->group(base_path('routes/api.php'));
                          Route::prefix('game')
                               ->group(base_path('routes/game.php'));
                          Route::middleware('web')
                               ->group(base_path('routes/web.php'));
                      },
                      commands: __DIR__ . '/../routes/console.php',
                  )
                  ->withMiddleware(function (Middleware $middleware) {
                      $middleware->append(CORS::class);
                  })
                  ->withExceptions(function (Exceptions $exceptions) {
                      //
                  })->create();
