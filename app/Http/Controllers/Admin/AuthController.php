<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;


class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if ($this->isAuthenticated()) {
            return redirect('/admin/dashboard');
        }

        return view('admin.auth.login', [
            'pageTitle' => 'Admin Login',
            'activePage' => '',
            'authPage' => true,
            'error' => session()->pull('admin_login_error'),
        ]);
    }

    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $password = $request->input('password', '');

        if (password_verify($password, config('admin.password_hash'))) {
            $request->session()->regenerate();
            $request->session()->put([
                config('admin.session_auth_key') => true,
                config('admin.session_active_key') => time(),
            ]);
            $request->session()->forget('admin_login_error');

            return redirect('/admin/dashboard');
        }

        return redirect('/admin/'.config('admin.login_slug'))
            ->with('admin_login_error', 'Invalid password. Please try again.');
    }

    public function logout(): RedirectResponse
    {
        session()->forget([
            config('admin.session_auth_key'),
            config('admin.session_active_key'),
            'admin_login_error',
        ]);
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    private function isAuthenticated(): bool
    {
        $authKey = config('admin.session_auth_key');
        if (! session($authKey)) {
            return false;
        }

        $lastActive = (int) session(config('admin.session_active_key'), 0);
        $timeout = (int) config('admin.session_timeout', 1800);

        if ((time() - $lastActive) > $timeout) {
            session()->forget([$authKey, config('admin.session_active_key')]);

            return false;
        }

        return true;
    }
}
