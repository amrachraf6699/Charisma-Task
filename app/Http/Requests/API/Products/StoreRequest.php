<?php

namespace App\Http\Requests\API\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ProductStatus;

class StoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required','string','max:255','unique:products,sku'],
            'name' => ['required','string','min:3','max:255'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0'],
            'stock_quantity' => ['required','integer','min:0'],
            'low_stock_threshold' => ['required','integer','min:0'],
            'status' => ['required',Rule::enum(ProductStatus::class)],
        ];
    }
}
