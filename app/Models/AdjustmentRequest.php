<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'requested_by',
        'reviewed_by',
        'old_qty',
        'new_qty',
        'difference',
        'reason',
        'rejection_reason',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'old_qty'     => 'integer',
        'new_qty'     => 'integer',
        'difference'  => 'integer',
        'reviewed_at' => 'datetime',
    ];

    /* ─────────── Relationships ─────────── */

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    /* ─────────── Helpers ─────────── */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /* ─────────── Scopes ─────────── */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}