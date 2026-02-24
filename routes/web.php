<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return "Sistem Monitoring Karyawan Aktif (Lumen v" . $router->app->version() . ")";
});

// ROUTE API KARYAWAN
$router->group(['prefix' => 'api'], function () use ($router) {
    
    // PUBLIC (Bisa diakses sebelum login)
    $router->get('/users', 'ActivityController@getUsers'); // Untuk list pengguna
    $router->post('/login', 'AuthController@login');       // Proses Login
    
    // MANAJEMEN PENGGUNA (Oleh Super Admin)
    $router->post('/users', 'UserController@store');       // Tambah User Baru
    $router->put('/users/{id}', 'UserController@update');  // Edit User (Update Nama/PIN/Role)

    // KEGIATAN & DASHBOARD
    $router->post('/activities', 'ActivityController@store');         // Karyawan Input Kegiatan
    $router->get('/admin/stats', 'AdminController@getDashboardStats'); // Data Dashboard Admin
});