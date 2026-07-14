<?php

namespace Tests\Feature;

use App\Events\ProductStockBecameLow;
use App\Models\Product;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.redis' => [
                'driver' => 'array',
                'serialize' => false,
            ],
        ]);

        Cache::flush();
        Cache::store('redis')->flush();
    }

    public function test_correct_create_product(): void
    {
        $response = $this->postJson('/api/products', $this->validPayload());

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Products created successfully.')
            ->assertJsonPath('data.sku', 'product-1');

        $this->assertDatabaseHas('products', [
            'sku' => 'product-1',
            'name' => 'Product 1',
        ]);
    }

    public function test_incorrect_create_product(): void
    {
        $response = $this->postJson('/api/products', [
            'sku' => '',
            'name' => 'x',
            'price' => -10,
            'stock_quantity' => -1,
            'low_stock_threshold' => -1,
            'status' => 'invalid',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonValidationErrors([
                'sku',
                'name',
                'price',
                'stock_quantity',
                'low_stock_threshold',
                'status',
            ], 'data');
    }

    public function test_non_cached_index_call_queries_products(): void
    {
        $this->createProduct(['sku' => 'product-1']);

        DB::enableQueryLog();

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.sku', 'product-1');

        $this->assertGreaterThan(0, $this->productQueryCount());
    }

    public function test_cached_index_call_does_not_query_products_again(): void
    {
        $this->createProduct(['sku' => 'product-1']);

        $this->getJson('/api/products')->assertOk();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.sku', 'product-1');

        $this->assertSame(0, $this->productQueryCount());
    }

    public function test_event_observer_dispatches_when_stock_becomes_low(): void
    {
        Event::fake([ProductStockBecameLow::class]);

        $product = $this->createProduct([
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
        ]);

        $product->update([
            'stock_quantity' => 3,
        ]);

        Event::assertDispatched(ProductStockBecameLow::class);
    }

    public function test_correct_update_product(): void
    {
        $product = $this->createProduct();

        $response = $this->putJson("/api/products/{$product->id}", [
            'sku' => 'updated-product',
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 25.50,
            'stock_quantity' => 15,
            'low_stock_threshold' => 5,
            'status' => 'inactive',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', 'updated-product')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'updated-product',
            'name' => 'Updated Product',
        ]);
    }

    public function test_incorrect_update_product(): void
    {
        $product = $this->createProduct();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'x',
            'price' => -1,
            'status' => 'invalid',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors([
                'name',
                'price',
                'status',
            ], 'data');
    }

    public function test_soft_deletes_product(): void
    {
        $product = $this->createProduct();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_accepted_and_banned_requests_because_of_rate_limiting(): void
    {
        RateLimiter::for('api-write', function ($request) {
            return Limit::perMinute(1)->by($request->ip());
        });

        $this->postJson('/api/products', $this->validPayload([
            'sku' => 'product-1',
        ]))->assertOk();

        $this->postJson('/api/products', $this->validPayload([
            'sku' => 'product-2',
        ]))
            ->assertTooManyRequests()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Too many requests. Please try again later.');
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'sku' => 'product-1',
            'name' => 'Product 1',
            'description' => 'Product description',
            'price' => 11.20,
            'stock_quantity' => 22,
            'low_stock_threshold' => 12,
            'status' => 'active',
        ], $overrides);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::create($this->validPayload($overrides));
    }

    private function productQueryCount(): int
    {
        return collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_contains($query['query'], 'products'))
            ->count();
    }
}
