<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report — {{ $from }} to {{ $to }}</title>
    @include('reports._print-styles')
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <button onclick="window.close()">Close</button>
</div>

<div class="header">
    <h1>CitiTrack — Sales Report</h1>
    <p class="sub">Citi Power Electronic Supply</p>
    <p class="sub">Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p>
    <p class="sub">Generated: {{ now()->format('M d, Y g:i A') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Reference</th>
            <th>Date</th>
            <th>Cashier</th>
            <th class="num">Subtotal</th>
            <th class="num">Discount</th>
            <th class="num">Total</th>
            <th>Payment</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $tx)
            <tr>
                <td>{{ $tx->reference_no }}</td>
                <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $tx->cashier->name ?? '—' }}</td>
                <td class="num">{{ number_format($tx->subtotal, 2) }}</td>
                <td class="num">{{ number_format($tx->discount, 2) }}</td>
                <td class="num">{{ number_format($tx->total, 2) }}</td>
                <td>{{ strtoupper($tx->payment_method) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">No records found for this period.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="totals">
            <td colspan="5">Total Revenue</td>
            <td class="num">₱{{ number_format($sales->sum('total'), 2) }}</td>
            <td></td>
        </tr>
        <tr class="totals">
            <td colspan="5">Transactions</td>
            <td class="num">{{ $sales->count() }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <p>— End of Report —</p>
</div>

</body>
</html>