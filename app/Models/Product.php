<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'supplier_id',
        'cost',
        'price',
        'stock_qty',
        'reorder_level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'cost'          => 'decimal:2',
        'price'         => 'decimal:2',
        'stock_qty'     => 'integer',
        'reorder_level' => 'integer',
        'is_active'     => 'boolean',
    ];

    /* ─────────── Relationships ─────────── */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function adjustmentRequests()
    {
        return $this->hasMany(AdjustmentRequest::class);
    }

    /* ─────────── Helpers ─────────── */

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->reorder_level;
    }

    public function profit(): float
    {
        return (float) $this->price - (float) $this->cost;
    }

    /* ─────────── Scopes ─────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'reorder_level');
    }
}