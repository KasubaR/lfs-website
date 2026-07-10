<?php

use App\Http\Middleware\AdminRateLimit;
use App\Http\Middleware\EnsureActiveMembership;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureMember;
use App\Http\Middleware\EnsurePasswordChanged;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'admin'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdminAuthenticated::class,
            'admin.ratelimit' => AdminRateLimit::class,
            'member' => EnsureMember::class,
            'force.password.change' => EnsurePasswordChanged::class,
            'membership.active' => EnsureActiveMembership::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/account');

        $middleware->encryptCookies(except: [
            'lfs_consent',
        ]);

        $middleware->validateCsrfTokens(except: [
            'shop/checkout/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
