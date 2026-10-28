<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_no',
        'cashier_id',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'amount_paid',
        'change_due',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_due'  => 'decimal:2',
    ];

    /* ─────────── Relationships ─────────── */

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function voidRequest()
    {
        return $this->hasOne(VoidRequest::class);
    }

    /* ─────────── Helpers ─────────── */

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /* ─────────── Scopes ─────────── */

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}