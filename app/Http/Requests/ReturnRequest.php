<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCashier() ?? false;
    }

    public function rules(): array
    {
        $transaction = $this->route('transaction');

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'required',
                Rule::exists('transaction_items', 'id')
                    ->where('transaction_id', $transaction?->id),
            ],
            'items.*.qty' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($transaction) {
                    $index = explode('.', $attribute)[1];
                    $itemId = $this->input("items.{$index}.id");
                    
                    $item = $transaction->items()->find($itemId);
                    
                    if ($item && $value > $item->qty) {
                        $fail("The return quantity cannot exceed the original sold quantity of {$item->qty}.");
                    }
                },
            ],
            'type'   => ['required', Rule::in(['return', 'exchange'])],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Please select at least one item to return.',
            'items.*.id.exists' => 'One of the selected items is not part of this transaction.',
            'items.*.qty.min' => 'Quantity must be at least 1.',
        ];
    }
}