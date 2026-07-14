<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return
        [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->when($request->routeIs('products.show'), $this->description),
            'price' => number_format($this->price, 2),
            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_low_stock' => $this->when($request->routeIs('products.show'), $this->stock_quantity <= $this->low_stock_threshold),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'created_at_humanly' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at,
            'updated_at_humanly' => $this->updated_at->diffForHumans(),
        ];
    }
}
