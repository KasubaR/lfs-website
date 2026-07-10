<?php

return [
    'email' => env('ADMIN_EMAIL', 'support@lfszambia.run'),
    'login_slug' => env('ADMIN_LOGIN_SLUG', 'door'),
    'password_hash' => env('ADMIN_PASSWORD_HASH', '$2y$12$invalid.placeholder.hash.that.never.matches.anything..'),
    'session_timeout' => (int) env('ADMIN_SESSION_TIMEOUT', 1800),
    'session_auth_key' => 'admin_authenticated',
    'session_active_key' => 'admin_last_active',
];
