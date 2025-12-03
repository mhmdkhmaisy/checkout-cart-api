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
        'api/*',
        'admin/cache/chunked-upload*',
        'admin/cache/patches/download-combined',
        'admin/cache/patches/check-updates',
        'vote/callback',
    ];
}