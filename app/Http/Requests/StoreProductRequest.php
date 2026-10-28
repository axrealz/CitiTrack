<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name'          => ['required', 'string', 'max:150'],
            'sku'           => ['nullable', 'string', 'max:50', 'unique:products,sku,' . $productId],
            'category_id'   => ['required', 'exists:categories,id'],
            'supplier_id'   => ['nullable', 'exists:suppliers,id'],
            'cost'          => ['required', 'numeric', 'min:0'],
            'price'         => ['required', 'numeric', 'min:0', 'gte:cost'],
            'stock_qty'     => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.gte' => 'Selling price must be greater than or equal to cost.',
        ];
    }
}