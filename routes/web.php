<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return "Sistem Monitoring Karyawan Aktif (Lumen v" . $router->app->version() . ")";
});

// ROUTE API KARYAWAN
$router->group(['prefix' => 'api'], function () use ($router) {
    
    // 1. Ambil List Karyawan (Untuk Dropdown)
    $router->get('/users', 'ActivityController@getUsers'); 
    
    // 2. Karyawan Input Kegiatan
    $router->post('/activities', 'ActivityController@store'); 

});

$router->get('/', function () use ($router) {
    return "API Monitoring Ready";
});

$router->group(['prefix' => 'api'], function () use ($router) {
    
    // PUBLIC (Bisa diakses sebelum login)
    $router->get('/users', 'ActivityController@getUsers'); // Untuk dropdown di halaman login
    $router->post('/login', 'AuthController@login');       // Proses Login

    // PROTECTED (Kegiatan)
    $router->post('/activities', 'ActivityController@store'); 
     $router->get('/admin/stats', 'AdminController@getDashboardStats');
});