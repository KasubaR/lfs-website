<?php

namespace App\Providers;

use App\Services\LencoService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LencoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Support/lfs_helpers.php');

        Password::defaults(fn () => Password::min(8));

        RateLimiter::for('admin', function (Request $request) {
            $sessionId = $request->session()->getId();

            return Limit::perMinute(60)->by('lfs_admin_post:'.sha1($sessionId !== '' ? $sessionId : (string) $request->ip()));
        });
    }
}
