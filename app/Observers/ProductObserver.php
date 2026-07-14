<?php

namespace App\Observers;

use App\Events\ProductStockBecameLow;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->flushProductCache();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->flushProductCache();

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
        $this->flushProductCache();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->flushProductCache();
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->flushProductCache();
    }

    private function flushProductCache(): void
    {
        Cache::store('redis')->tags(['products'])->flush();
    }
}
