<?php

namespace App\Observers;

use App\Events\ProductStockBecameLow;
use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if (
            !$product->wasChanged([
                'stock_quantity',
                'low_stock_threshold',
            ])
        ) {
            return;
        }

        $isLowStock = $product->stock_quantity
            <= $product->low_stock_threshold;

        if ($isLowStock) {
            ProductStockBecameLow::dispatch($product);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
