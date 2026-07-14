<?php

namespace App\Http\Requests\API\Products;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'low_stock_threshold' => ['sometimes', 'required', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
        ];
    }
}
