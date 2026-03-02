<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Di sini kita menentukan koneksi mana yang digunakan secara default.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Pengaturan koneksi database. 
    | Nilai default di bawah disesuaikan langsung dengan kredensial Aiven Anda
    | agar aplikasi tetap bisa terhubung meskipun .env di server tidak terbaca.
    |
    */

    'connections' => [

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'mysql-24bf7e8c-icikiwir32.i.aivencloud.com'),
            'port'      => env('DB_PORT', '11378'),
            'database'  => env('DB_DATABASE', 'defaultdb'),
            'username'  => env('DB_USERNAME', 'avnadmin'),
            'password'  => env('DB_PASSWORD', 'AVNS_y3_wK-UknhvtUnQ1tyc'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
            'options'   => [
                // WAJIB: Aiven MySQL memerlukan SSL
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */
    'migrations' => 'migrations',

];