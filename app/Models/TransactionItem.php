<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'qty',
        'unit_price',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'qty'        => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost'  => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    /* ─────────── Relationships ─────────── */

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /* ─────────── Helpers ─────────── */

    public function profit(): float
    {
        return ((float) $this->unit_price - (float) $this->unit_cost) * $this->qty;
    }
}