<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

// ---------------------------------------------------------
// 1. TEST KIRIM WA PAKAI Guzzle LANGSUNG
// ---------------------------------------------------------
$router->get('/test-wa', function () {

    // Ambil credential dari .env
    $domain = env('WABLAS_DOMAIN');   // contoh: https://bdg.wablas.com
    $token  = env('WABLAS_TOKEN');    // Token Wablas
    $secret = env('WABLAS_SECRET');   // Secret Key Wablas
    $phone  = env('ADMIN_PHONE');     // Nomor WA tujuan

    // Cek apakah semua credential terbaca
    if (!$domain || !$token || !$secret || !$phone) {
        return [
            'status' => false,
            'message' => 'Credential Wablas belum lengkap. Cek .env',
            'domain' => $domain ?: null,
            'token' => $token ? 'TERBACA' : null,
            'secret' => $secret ? 'TERBACA' : null,
            'phone' => $phone ?: null,
        ];
    }

    // Payload WA
    $payload = [
        'phone'   => $phone,
        'message' => 'Tes koneksi Lumen + Secret Key berhasil!'
    ];

    try {
        $client = new \GuzzleHttp\Client();

        $response = $client->post("$domain/api/send-message", [
            'headers' => [
                'Authorization' => $token,
                'secret-key'    => $secret,
            ],
            'form_params' => $payload
        ]);

        return [
            'status' => true,
            'message' => 'WA request berhasil dikirim ke Wablas',
            'response' => json_decode($response->getBody(), true)
        ];

    } catch (\GuzzleHttp\Exception\ClientException $e) {
        return [
            'status' => false,
            'message' => 'Client Error: ' . $e->getResponse()->getBody()->getContents()
        ];
    } catch (\Exception $e) {
        return [
            'status' => false,
            'message' => 'Exception: ' . $e->getMessage()
        ];
    }
});


// ---------------------------------------------------------
// 2. ROUTE API PRODUCT
// ---------------------------------------------------------
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/products', 'ProductController@index');
    $router->put('/products/{id}', 'ProductController@updateStock');
    $router->get('/users', 'ActivityController@getUsers'); // Ambil list nama karyawan
    $router->post('/activities', 'ActivityController@store'); // Input kegiatan
});


$router->get('/env-check', function () {
    return [
        'domain' => env('WABLAS_DOMAIN'),
        'token'  => env('WABLAS_TOKEN') ? 'TERBACA' : 'KOSONG',
        'secret' => env('WABLAS_SECRET') ? 'TERBACA' : 'KOSONG',
        'phone'  => env('ADMIN_PHONE'),
    ];
});



// ---------------------------------------------------------
// 3. ROOT ROUTE
// ---------------------------------------------------------
$router->get('/', function () use ($router) {
    return $router->app->version();
});

// ---------------------------------------------------------
// 4. TEST KIRIM WA VIA SERVICE WABLAS
// ---------------------------------------------------------
$router->get('/test-wa-manual', function () {

    // contoh model dummy
    $dummyProduct = new \App\Models\Product();
    $dummyProduct->name     = "Barang Tes XAMPP";
    $dummyProduct->quantity = 0;

    // panggil service
    $result = \App\Services\WablasService::sendAlert($dummyProduct);

    return "Cek WA Anda. Hasil Server: {$result}";
});

$router->get('/test-wa-curl', function () {

    $token = env('WABLAS_TOKEN'); // ambil dari .env
    $phone = env('ADMIN_PHONE');  // ambil dari .env

    // cek token & phone
    if (!$token || !$phone) {
        return "ERROR: Token atau phone tidak terbaca dari ENV";
    }

    $payload = [
        'phone' => $phone,
        'message' => 'Halo test dari API via CURL',
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://bdg.wablas.com/api/send-message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $token,
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return "CURL ERROR: " . $err;
    }

    return $response;
});


$router->get('/test-wa', function () {

    $domain = env('WABLAS_DOMAIN');   // https://bdg.wablas.com
    $token  = env('WABLAS_TOKEN');    // Token Wablas
    $secret = env('WABLAS_SECRET');   // Secret Key
    $phone  = env('ADMIN_PHONE');     // Nomor WA

    if (!$domain || !$token || !$secret || !$phone) {
        return "ERROR: Credential Wablas belum lengkap. Cek .env";
    }

    try {
        $client = new \GuzzleHttp\Client();

        $response = $client->post("$domain/api/send-message", [
            'headers' => [
                'Authorization' => $token,
                'secret-key'    => $secret,  // ✅ wajib
            ],
            'form_params' => [
                'phone'   => $phone,
                'message' => 'Tes koneksi Lumen + Secret Key berhasil!'
            ]
        ]);

        return "SUKSES: " . $response->getBody();

    } catch (\GuzzleHttp\Exception\ClientException $e) {
        return "GAGAL: " . $e->getResponse()->getBody()->getContents();
    } catch (\Exception $e) {
        return "GAGAL: " . $e->getMessage();
    }
});

