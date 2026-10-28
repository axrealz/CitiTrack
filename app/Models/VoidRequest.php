<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoidRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'requested_by',
        'reviewed_by',
        'reason',
        'rejection_reason',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /* ─────────── Relationships ─────────── */

    public function transaction()
    {
        return $this->belongsTo(Transaction::class)->withTrashed();
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