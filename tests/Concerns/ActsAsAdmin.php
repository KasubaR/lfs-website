<?php

namespace Tests\Concerns;

trait ActsAsAdmin
{
    protected function actingAsAdmin(): static
    {
        return $this->withSession([
            config('admin.session_auth_key') => true,
            config('admin.session_active_key') => time(),
        ]);
    }

    protected function adminLoginPath(): string
    {
        return '/admin/'.config('admin.login_slug', 'door');
    }
}
