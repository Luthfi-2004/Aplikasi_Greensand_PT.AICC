<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    | Supported drivers: "session"
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    | Supported: "eloquent", "database"
    |
    | NOTE:
    | - Model User kamu harus:
    |   protected $connection = 'mysql_aicc';
    |   protected $table = 'tb_user';
    |   public function getAuthPassword() { return $this->pswd; }
    | - Username field di LoginController: return 'usr';
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],

        // Jika suatu saat butuh provider berbasis query builder:
        // 'users' => [
        //     'driver' => 'database',
        //     'table'  => 'tb_user',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,   // minutes
            'throttle' => 60,   // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */
    'password_timeout' => 10800, // 3 hours
];
