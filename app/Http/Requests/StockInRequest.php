<?php

namespace App\Http\Requests;

use App\Models\StockLog;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStocker() ?? false;
    }

    /** An empty "amount paid" means nothing was paid yet. */
    protected function prepareForValidation(): void
    {
        $paid = trim((string) $this->input('amount_paid'));

        $this->merge([
            'amount_paid' => $paid === '' ? '0' : $paid,
            'unit_cost'   => trim((string) $this->input('unit_cost')),
        ]);
    }

    public function rules(): array
    {
        // Money: digits with at most 2 decimals (no "1e3", no negatives, no 3 decimals)
        $money = 'regex:/^\d+(\.\d{1,2})?$/';

        return [
            'product_id'       => ['required', 'exists:products,id'],
            'supplier_id'      => ['nullable', 'exists:suppliers,id'],
            'qty'              => ['required', 'integer', 'min:1', 'max:100000'],
            'unit_cost'        => ['required', 'numeric', 'gt:0', 'max:99999.99', $money],
            'purchase_date'    => ['required', 'date', 'before_or_equal:today'],
            'amount_paid'      => ['required', 'numeric', 'min:0', 'max:9999999999.99', $money],
            'payment_method'   => ['nullable', Rule::in(array_keys(StockLog::PAYMENT_METHODS))],
            'reference_no'     => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'submission_token' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_cost.gt'                  => 'The purchase cost per unit must be more than ₱0.',
            'unit_cost.regex'               => 'Enter the cost as a number with at most 2 decimal places.',
            'amount_paid.regex'             => 'Enter the amount as a number with at most 2 decimal places.',
            'purchase_date.before_or_equal' => 'The purchase date cannot be in the future.',
            'submission_token.required'     => 'This form has expired. Please try again.',
            'submission_token.uuid'         => 'This form has expired. Please try again.',
        ];
    }

    public function attributes(): array
    {
        return [
            'unit_cost'      => 'purchase cost per unit',
            'amount_paid'    => 'amount paid',
            'payment_method' => 'payment method',
            'purchase_date'  => 'purchase date',
            'reference_no'   => 'reference number',
        ];
    }

    /** Checks that need more than one field. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['qty', 'unit_cost', 'amount_paid'])) {
                return;
            }

            $calc = StockLog::calculatePurchase(
                (int) $this->input('qty'),
                $this->input('unit_cost'),
                $this->input('amount_paid')
            );

            if ($calc['paid_cents'] > $calc['total_cents']) {
                $validator->errors()->add(
                    'amount_paid',
                    'The amount paid cannot be more than the total purchase cost (₱' . number_format($calc['total_cents'] / 100, 2) . ').'
                );
            }

            if ($calc['paid_cents'] > 0 && blank($this->input('payment_method'))) {
                $validator->errors()->add('payment_method', 'Choose how the supplier was paid.');
            }
        });
    }
}