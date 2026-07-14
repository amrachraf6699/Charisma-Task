<?php

namespace App\Listeners;

use App\Events\ProductStockBecameLow;
use Illuminate\Support\Facades\Log;

class SendLowStockAlert
{
    public function handle(ProductStockBecameLow $event): void
    {
        $product = $event->product;

        Log::warning('Low stock Product.', [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'stock_quantity' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold,
        ]);
    }
}