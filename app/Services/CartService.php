<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    protected string $key = 'pos_cart';

    /**
     * Raw cart: [product_id => qty]
     */
    public function all(): array
    {
        return session()->get($this->key, []);
    }

    /**
     * Add qty to a product.
     */
    public function add(int $productId, int $qty = 1): void
    {
        $cart = $this->all();
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        session()->put($this->key, $cart);
    }

    /**
     * Set exact qty (remove if <= 0).
     */
    public function update(int $productId, int $qty): void
    {
        $cart = $this->all();

        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }

        session()->put($this->key, $cart);
    }

    /**
     * Remove a product.
     */
    public function remove(int $productId): void
    {
        $cart = $this->all();
        unset($cart[$productId]);
        session()->put($this->key, $cart);
    }

    /**
     * Clear the cart.
     */
    public function clear(): void
    {
        session()->forget($this->key);
    }

    /**
     * Get the qty of a product in the cart.
     */
    public function qtyOf(int $productId): int
    {
        return $this->all()[$productId] ?? 0;
    }

    /**
     * Detailed cart rows with product model + subtotal.
     */
    public function detailed(): array
    {
        $cart = $this->all();

        if (empty($cart)) {
            return [];
        }

        $products = Product::whereIn('id', array_keys($cart))->get();

        return $products->map(function ($product) use ($cart) {
            $qty = $cart[$product->id];

            return [
                'product'  => $product,
                'qty'      => $qty,
                'subtotal' => (float) $product->price * $qty,
            ];
        })->toArray();
    }

    /**
     * Subtotal (before discount).
     */
    public function subtotal(): float
    {
        return collect($this->detailed())->sum('subtotal');
    }

    /**
     * Total (after discount — currently no cart-level discount).
     */
    public function total(): float
    {
        return $this->subtotal();
    }

    /**
     * Total item count (for cart badge).
     */
    public function count(): int
    {
        return array_sum($this->all());
    }
}