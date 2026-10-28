<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Summary</title>
    @include('reports._print-styles')
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <button onclick="window.close()">Close</button>
</div>

<div class="header">
    <h1>CitiTrack — Inventory Summary</h1>
    <p class="sub">Citi Power Electronic Supply</p>
    <p class="sub">Generated: {{ now()->format('M d, Y g:i A') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>SKU</th>
            <th>Product</th>
            <th>Category</th>
            <th>Supplier</th>
            <th class="num">Cost</th>
            <th class="num">Price</th>
            <th class="num">Stock</th>
            <th class="num">Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $p)
            <tr>
                <td>{{ $p->sku ?? '—' }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->category->name ?? '—' }}</td>
                <td>{{ $p->supplier->name ?? '—' }}</td>
                <td class="num">{{ number_format($p->cost, 2) }}</td>
                <td class="num">{{ number_format($p->price, 2) }}</td>
                <td class="num">{{ $p->stock_qty }}</td>
                <td class="num">{{ number_format($p->stock_qty * $p->cost, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals">
            <td colspan="7">Total Stock Value</td>
            <td class="num">₱{{ number_format($totalValue, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <p>— End of Report —</p>
</div>

</body>
</html>