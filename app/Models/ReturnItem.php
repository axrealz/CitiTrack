<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'processed_by',
        'qty',
        'refund_amount',
        'type',
        'reason',
    ];

    protected $casts = [
        'qty'           => 'integer',
        'refund_amount' => 'decimal:2',
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

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by')->withTrashed();
    }

    /* ─────────── Helpers ─────────── */

    public function isExchange(): bool
    {
        return $this->type === 'exchange';
    }
}