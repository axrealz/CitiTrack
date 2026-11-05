<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Item-Level Sales — {{ $from }} to {{ $to }}</title>
    @include('reports._print-styles')
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <button onclick="window.close()">Close</button>
</div>

<div class="header">
    <h1>CitiTrack — Item-Level Sales Report</h1>
    <p class="sub">Citi Power Electronic Supply</p>
    <p class="sub">Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p>
    <p class="sub">Generated: {{ now()->format('M d, Y g:i A') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th class="num">Qty Sold</th>
            <th class="num">Revenue</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="num">{{ number_format($item->total_qty) }}</td>
                <td class="num">{{ number_format($item->total_sales, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="empty">No records found for this period.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="totals">
            <td>Total</td>
            <td class="num">{{ number_format($items->sum('total_qty')) }}</td>
            <td class="num">₱{{ number_format($items->sum('total_sales'), 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <p>— End of Report —</p>
</div>

</body>
</html>