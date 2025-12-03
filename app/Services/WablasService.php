<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WablasService {
    public static function sendAlert($product) {
        $domain = env('WABLAS_DOMAIN');
        $token = env('WABLAS_TOKEN');
        $phone = env('ADMIN_PHONE');

        // Format pesan
        $message = "⚠️ *PERINGATAN STOK MENIPIS*\n\n";
        $message .= "Barang: " . $product->name . "\n";
        $message .= "Sisa Stok: " . $product->quantity . "\n";
        $message .= "Segera lakukan restock!";

        try {
            // KIRIM REQUEST
            // 'withoutVerifying()' = Solusi agar XAMPP tidak error SSL
            // 'asForm()' = Agar format data dikenali Wablas
            $response = Http::withoutVerifying()
                ->asForm()
                ->withHeaders([
                    'Authorization' => $token
                ])->post("$domain/api/send-message", [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            // Cek respon server
            $body = $response->body();
            Log::info("Wablas Status: " . $response->status());
            Log::info("Wablas Response: " . $body);

            return $body;

        } catch (\Exception $e) {
            Log::error('Wablas Gagal: ' . $e->getMessage());
            return $e->getMessage();
        }
    }
}