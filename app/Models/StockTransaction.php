<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model {
    protected $fillable = [
        'product_id', 
        'type', 
        'qty_change', 
        'current_qty', 
        'note'
    ];

    // Relasi biar tahu ini history punya barang apa
    public function product() {
        return $this->belongsTo(Product::class);
    }
}