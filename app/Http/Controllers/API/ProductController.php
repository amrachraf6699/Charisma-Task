<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Products\{AdjustStockRequest, StoreRequest, UpdateRequest};
use App\Http\Resources\API\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Cache::store('redis')
            ->tags(['products'])
            ->remember(
                'products:index:page:' . $request->query('page', 1),
                now()->addSeconds((int) config('cache.product_listing_ttl')),
                fn () => Product::paginate()
            );

        return $this->success(
            200,
            'Products fetched successfully.',
            ProductResource::collection($products),
            [
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $product = Product::create($request->validated());

        return $this->success(
            200,
            'Products created successfully.',
            ProductResource::make($product),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $this->success(
            200,
            'Products fetched successfully.',
            ProductResource::make($product),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Product $product)
    {
        $product->update($request->validated());

        return $this->success(
            200,
            'Product updated successfully.',
            ProductResource::make($product->refresh())
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->success(
            200,
            'Product deleted successfully.'
        );
    }

    public function adjustStock(AdjustStockRequest $request, Product $product)
    {
        $newStock = $request->quantity + $product->stock_quantity;

        if ($newStock < 0) {
            return $this->error(422, "The new stock can't be in negative");
        }

        $product->update([
            'stock_quantity' => $newStock,
        ]);

        $product->refresh();

        return $this->success(
            200,
            'Product stock adjusted successfully.',
            ProductResource::make($product->refresh())
        );
    }

    public function lowStock()
    {
        $products = Product::lowStock()->paginate();

        return $this->success(
            200,
            'Low stock products fetched successfully.',
            ProductResource::collection($products),
            [
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]
        );
    }
}
