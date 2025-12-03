<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\WablasService;
use Illuminate\Http\Request;

class ProductController extends Controller {
    
    public function index() {
        return response()->json(Product::all());
    }

    public function updateStock(Request $request, $id) {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Update quantity
        $product->quantity = $request->input('quantity');
        $product->save();

        // LOGIC WABLAS: Jika stok <= min_stock, kirim WA
        if ($product->quantity <= $product->min_stock) {
            WablasService::sendAlert($product);
            $status = "Stok diperbarui & Peringatan dikirim!";
        } else {
            $status = "Stok diperbarui.";
        }

        return response()->json([
            'message' => $status,
            'data' => $product
        ]);
    }
}