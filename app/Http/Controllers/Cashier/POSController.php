<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class POSController extends Controller
{
    public function __construct(protected CartService $cart) {}

    /**
     * Show the POS screen.
     */
    public function index(Request $request): View
    {
        $search = $request->get('q');

        $products = Product::active()
            ->where('stock_qty', '>', 0)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(24)
            ->get();

        return view('cashier.pos', [
            'products' => $products,
            'cart'     => $this->cart->detailed(),
            'subtotal' => $this->cart->subtotal(),
            'total'    => $this->cart->total(),
            'search'   => $search,
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request, Product $product): RedirectResponse
    {
        if ($product->stock_qty <= 0) {
            return back()->with('error', 'Product is out of stock.');
        }

        $qty = (int) $request->input('qty', 1);

        $alreadyInCart = $this->cart->qtyOf($product->id);

        if ($alreadyInCart + $qty > $product->stock_qty) {
            return back()->with('error', "Only {$product->stock_qty} in stock.");
        }

        $this->cart->add($product->id, $qty);

        return back()->with('success', "{$product->name} added to cart.");
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $qty = (int) $request->input('qty', 1);

        if ($qty > $product->stock_qty) {
            return back()->with('error', "Only {$product->stock_qty} in stock.");
        }

        $this->cart->update($product->id, $qty);

        return back();
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product->id);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return back()->with('success', 'Cart cleared.');
    }

    /**
     * Process the checkout.
     */
    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        $cart = $this->cart->detailed();

        if (empty($cart)) {
            return back()->with('error', 'Cart is empty.');
        }

        $subtotal = $this->cart->subtotal();
        $discount = 0;
        $total    = $subtotal;

        // Validate payment amount
        $amountPaid = (float) $request->input('amount_paid');

        if ($amountPaid < $total) {
            return back()->with('error', 'Amount paid is less than total.');
        }

        $transaction = DB::transaction(function () use ($request, $cart, $subtotal, $discount, $total, $amountPaid) {

            // Generate reference number
            $referenceNo = 'CT-' . now()->format('Ymd') . '-' . str_pad(
                Transaction::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            $transaction = Transaction::create([
                'reference_no'   => $referenceNo,
                'cashier_id'     => auth()->id(),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'payment_method' => $request->input('payment_method'),
                'amount_paid'    => $amountPaid,
                'change_due'     => $amountPaid - $total,
                'status'         => 'completed',
                'notes'          => $request->input('notes'),
            ]);

            // Save line items + deduct stock
            foreach ($cart as $row) {
                $product = $row['product'];

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'qty'            => $row['qty'],
                    'unit_price'     => $product->price,
                    'unit_cost'      => $product->cost,
                    'subtotal'       => $row['subtotal'],
                ]);

                $before = $product->stock_qty;
                $product->decrement('stock_qty', $row['qty']);
                $after = $product->fresh()->stock_qty;

                StockLog::create([
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(),
                    'type'         => 'sale_deduction',
                    'qty'          => -$row['qty'],
                    'stock_before' => $before,
                    'stock_after'  => $after,
                    'reason'       => 'Sale ' . $referenceNo,
                ]);
            }

            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'sale.completed',
                'model_type' => Transaction::class,
                'model_id'   => $transaction->id,
                'new_values' => ['reference_no' => $referenceNo, 'total' => $total],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $transaction;
        });

        $this->cart->clear();

        return redirect()->route('cashier.receipt', $transaction)
            ->with('success', 'Sale completed.');
    }
}