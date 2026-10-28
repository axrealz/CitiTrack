<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* ─────────── Relationships ─────────── */

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    /* ─────────── Scopes ─────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}