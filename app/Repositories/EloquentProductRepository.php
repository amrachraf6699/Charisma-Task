<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(): LengthAwarePaginator
    {
        return Product::paginate();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function adjustStock(Product $product, int $quantity): Product
    {
        $product->update([
            'stock_quantity' => $product->stock_quantity + $quantity,
        ]);

        return $product->refresh();
    }

    public function paginateLowStock(): LengthAwarePaginator
    {
        return Product::lowStock()->paginate();
    }
}
