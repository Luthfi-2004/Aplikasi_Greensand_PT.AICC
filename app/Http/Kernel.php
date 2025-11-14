<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\BypassAuthForTesting::class,
        ],
        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    // PAKAI INI untuk alias di Laravel baru
    protected $middlewareAliases = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'role'             => \App\Http\Middleware\RoleMiddleware::class,
        'check.permission' => \App\Http\Middleware\CheckPermission::class,
        'bypass.auth'      => \App\Http\Middleware\BypassAuthForTesting::class,
    ];

    // OPSIONAL: biar kompatibel dengan basis lama (boleh dihapus kalau gak perlu)
    protected $routeMiddleware = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'role'             => \App\Http\Middleware\RoleMiddleware::class,
        'check.permission' => \App\Http\Middleware\CheckPermission::class,
        'bypass.auth'      => \App\Http\Middleware\BypassAuthForTesting::class,
    ];
}
