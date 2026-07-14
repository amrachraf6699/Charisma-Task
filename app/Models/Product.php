<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'stock_quantity',
        'low_stock_threshold',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'status' => ProductStatus::class,
        ];
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where(
            'stock_quantity',
            '<=',
            'low_stock_threshold'
        );
    }
}
