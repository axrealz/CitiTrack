<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustmentRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStocker() ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'new_qty'    => ['required', 'integer', 'min:0'],
            'reason'     => ['required', 'string', 'max:500'],
        ];
    }
}