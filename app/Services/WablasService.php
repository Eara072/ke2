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
            $response = Http::withoutVerifying()
                ->asForm()
                ->withHeaders([
                    'Authorization' => $token
                ])->post("$domain/api/send-message", [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            $body = $response->body();
            Log::info("Wablas Status: " . $response->status());
            Log::info("Wablas Response: " . $body);

            return $body;

        } catch (\Exception $e) {
            Log::error('Wablas Gagal: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    // ================================================================
    // [BARU] Function Generic untuk kirim pesan bebas
    // ================================================================
    public static function sendMessage($targetPhone, $messageText) {
        $domain = env('WABLAS_DOMAIN');
        $token  = env('WABLAS_TOKEN');

        try {
            $response = Http::withoutVerifying()
                ->asForm()
                ->withHeaders(['Authorization' => $token])
                ->post("$domain/api/send-message", [
                    'phone'   => $targetPhone,
                    'message' => $messageText,
                ]);

            Log::info("Wablas Reminder Sent to $targetPhone");
            return $response->body();

        } catch (\Exception $e) {
            Log::error('Wablas Reminder Error: ' . $e->getMessage());
            return false;
        }
    }
}
