<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // kalau route webhook kamu di web.php:
        'midtrans/notification',

        // kalau route webhook kamu di api.php:
        // 'api/midtrans/notification',

        // atau bisa juga pakai wildcard:
        // 'midtrans/*',
        // 'api/midtrans/*',
    ];
}
