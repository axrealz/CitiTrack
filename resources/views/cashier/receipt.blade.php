@extends('layouts.print')

@section('title', 'Receipt ' . $transaction->reference_no)

@section('content')

<div class="center bold" style="font-size: 14px;">CITI POWER ELECTRONIC SUPPLY</div>
<div class="center">Davao City</div>
<div class="center">Sales &amp; Inventory System</div>
<div class="line"></div>

<div>Ref: <span class="bold">{{ $transaction->reference_no }}</span></div>
<div>Date: {{ $transaction->created_at->format('M d, Y g:i A') }}</div>
<div>Cashier: {{ $transaction->cashier->name ?? '—' }}</div>
<div class="line"></div>

<table>
    <thead>
        <tr>
            <th>Item</th>
            <th class="right">Qty</th>
            <th class="right">Price</th>
            <th class="right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transaction->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="right">{{ $item->qty }}</td>
                <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>

<table class="totals">
    <tr>
        <td class="label">Subtotal</td>
        <td class="value">₱{{ number_format($transaction->subtotal, 2) }}</td>
    </tr>
    <tr class="bold">
        <td class="label">TOTAL</td>
        <td class="value">₱{{ number_format($transaction->total, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Payment</td>
        <td class="value">{{ strtoupper($transaction->payment_method) }}</td>
    </tr>
    <tr>
        <td class="label">Amount Paid</td>
        <td class="value">₱{{ number_format($transaction->amount_paid, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Change</td>
        <td class="value">₱{{ number_format($transaction->change_due, 2) }}</td>
    </tr>
</table>

<div class="line"></div>

<div class="center">Thank you for your purchase!</div>
<div class="center" style="font-size: 10px; margin-top: 4px;">
    Returns accepted within 24 hours with this receipt.
</div>

@if($transaction->status === 'voided')
    <div class="line"></div>
    <div class="center bold" style="color: red; font-size: 14px;">** VOIDED **</div>
@elseif($transaction->status === 'returned')
    <div class="line"></div>
    <div class="center bold" style="color: red; font-size: 14px;">** RETURNED **</div>
@endif

@endsection