<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    /* ─────────── Relationships ─────────── */

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class, 'user_id');
    }

    public function returnsProcessed()
    {
        return $this->hasMany(ReturnItem::class, 'processed_by');
    }

    public function voidRequestsMade()
    {
        return $this->hasMany(VoidRequest::class, 'requested_by');
    }

    public function voidRequestsReviewed()
    {
        return $this->hasMany(VoidRequest::class, 'reviewed_by');
    }

    public function adjustmentRequestsMade()
    {
        return $this->hasMany(AdjustmentRequest::class, 'requested_by');
    }

    public function adjustmentRequestsReviewed()
    {
        return $this->hasMany(AdjustmentRequest::class, 'reviewed_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /* ─────────── Helpers ─────────── */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isStocker(): bool
    {
        return $this->role === 'stocker';
    }

    /* ─────────── Scopes ─────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}