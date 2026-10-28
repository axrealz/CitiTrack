<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    use HasFactory;

    /** Ways a supplier can be paid (recording only, no payment gateway). */
    public const PAYMENT_METHODS = [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'e_wallet'      => 'E-Wallet',
        'other'         => 'Other',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid'  => 'Unpaid',
        'partial' => 'Partially Paid',
        'paid'    => 'Fully Paid',
    ];

    protected $fillable = [
        'product_id',
        'supplier_id',
        'user_id',
        'type',
        'qty',
        'stock_before',
        'stock_after',
        'unit_cost',
        'reason',
        'purchase_date',
        'total_cost',
        'amount_paid',
        'payment_method',
        'payment_status',
        'reference_no',
        'submission_token',
    ];

    protected $casts = [
        'qty'           => 'integer',
        'stock_before'  => 'integer',
        'stock_after'   => 'integer',
        'unit_cost'     => 'decimal:2',
        'purchase_date' => 'date',
        'total_cost'    => 'decimal:2',
        'amount_paid'   => 'decimal:2',
    ];

    /* ─────────── Relationships ─────────── */

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /* ─────────── Helpers ─────────── */

    public function isIncrease(): bool
    {
        return $this->qty > 0;
    }

    public function isDecrease(): bool
    {
        return $this->qty < 0;
    }

    /**
     * Work out total / balance / status for a purchase.
     * Money is converted to whole centavos first, so there are no
     * floating-point rounding errors (e.g. 3 x 0.10 is exactly 0.30).
     */
    public static function calculatePurchase(int $qty, string|int|float $unitCost, string|int|float $amountPaid): array
    {
        $unit  = self::toCents($unitCost);
        $paid  = self::toCents($amountPaid);
        $total = $qty * $unit;

        $status = match (true) {
            $paid <= 0      => 'unpaid',
            $paid >= $total => 'paid',
            default         => 'partial',
        };

        return [
            'total_cents'   => $total,
            'paid_cents'    => $paid,
            'balance_cents' => $total - $paid,
            'total'         => self::fromCents($total),
            'paid'          => self::fromCents($paid),
            'balance'       => self::fromCents($total - $paid),
            'status'        => $status,
        ];
    }

    private static function toCents(string|int|float $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private static function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    /** Remaining balance for a recorded purchase (null for old rows with no cost data). */
    public function getBalanceAttribute(): ?string
    {
        if ($this->total_cost === null) {
            return null;
        }

        return self::fromCents(self::toCents($this->total_cost) - self::toCents($this->amount_paid ?? 0));
    }

    public function getPaymentStatusLabelAttribute(): ?string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? null;
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? null;
    }

    /* ─────────── Scopes ─────────── */

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}