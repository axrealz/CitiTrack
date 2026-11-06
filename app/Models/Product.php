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

        /**
     * Stock status for the product lists, based on the product's own
     * reorder_level (the same threshold isLowStock() / scopeLowStock() use):
     *   out = quantity is 0 or less
     *   low = quantity is above 0 but at or below the reorder level
     *   in  = quantity is above the reorder level
     * Calculated from the current quantity, so it changes by itself whenever
     * stock goes up or down (stock-in, sales, returns, adjustments...).
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock_qty <= 0) {
            return 'out';
        }

        return $this->stock_qty <= $this->reorder_level ? 'low' : 'in';
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

        /**
     * Filter by stock status: 'in', 'low' or 'out'. Anything else = no filter.
     */
    public function scopeWithStockStatus($query, ?string $status)
    {
        return match ($status) {
            'out'   => $query->where('stock_qty', '<=', 0),
            'low'   => $query->where('stock_qty', '>', 0)->whereColumn('stock_qty', '<=', 'reorder_level'),
            'in'    => $query->where('stock_qty', '>', 0)->whereColumn('stock_qty', '>', 'reorder_level'),
            default => $query,
        };
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'reorder_level');
    }
}