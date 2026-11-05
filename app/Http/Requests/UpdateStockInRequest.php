<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Validation for editing an existing stock-in record.
 * Same rules as recording a new one (see StockInRequest), except:
 *  - there is no one-time submission token, and
 *  - the product must not be deleted.
 * The "amount paid <= total" and "payment method needed when paid" checks,
 * the 2-decimal money format and the stocker-only authorization are inherited.
 */
class UpdateStockInRequest extends StockInRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['submission_token']);

        $rules['product_id'] = ['required', Rule::exists('products', 'id')->whereNull('deleted_at')];

        return $rules;
    }
}