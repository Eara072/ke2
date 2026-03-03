<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction; // Import model baru untuk history stok
use App\Services\WablasService;
use Illuminate\Http\Request;

class ProductController extends Controller {
    
    // Menampilkan semua produk
    public function index() {
        return response()->json(Product::all());
    }

    // Update stok produk
    public function updateStock(Request $request, $id) {
        $product = Product::find($id);
              
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $oldQty = $product->quantity;
        $newQty = $request->input('quantity');
        
        // 1. Cek Selisih
        $diff = $newQty - $oldQty;
        if ($diff == 0) {
            return response()->json(['message' => 'Stok tidak berubah'], 200);
        }

        $type = $diff > 0 ? 'in' : 'out';

        // 2. Simpan transaksi history
        StockTransaction::create([
            'product_id'  => $product->id,
            'type'        => $type,
            'qty_change'  => abs($diff), // selalu positif
            'current_qty' => $newQty,
            'note'        => $note
        ]);

        // 3. Update stok barang utama
        $product->quantity = $newQty;
        $product->save();

        // 4. Logic Wablas: Kirim notifikasi jika stok menipis
        $alertStatus = "";
        if ($product->quantity <= $product->min_stock) {
            \App\Services\WablasService::sendAlert($product);
        }

        return response()->json(['message' => 'Stok & History tersimpan', 'data' => $product]);
    }

    // Mendapatkan riwayat stok produk
    public function getHistory($id) { // <--- Ubah jadi $id sesuai route
    $history = StockTransaction::where('product_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json($history);
}
}