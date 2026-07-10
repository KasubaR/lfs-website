<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $loginSlug = config('admin.login_slug');

        if ($this->isExemptRoute($request, $loginSlug)) {
            return $next($request);
        }

        if (! $this->isAuthenticated()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['ok' => false, 'message' => 'Unauthenticated.'], 401);
            }

            return redirect('/admin/'.$loginSlug);
        }

        session([config('admin.session_active_key') => time()]);

        return $next($request);
    }

    private function isExemptRoute(Request $request, string $loginSlug): bool
    {
        if ($request->is('admin/logout')) {
            return true;
        }

        return $request->is('admin/'.$loginSlug) || $request->is('admin/'.$loginSlug.'/*');
    }

    private function isAuthenticated(): bool
    {
        $authKey = config('admin.session_auth_key');

        if (! session($authKey)) {
            return false;
        }

        $activeKey = config('admin.session_active_key');
        $lastActive = (int) session($activeKey, 0);
        $timeout = (int) config('admin.session_timeout', 1800);

        if ((time() - $lastActive) > $timeout) {
            session()->forget([$authKey, $activeKey]);

            return false;
        }

        return true;
    }
}
