<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report — {{ $from }} to {{ $to }}</title>
    @include('reports._print-styles')
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <button onclick="window.close()">Close</button>
</div>

<div class="header">
    <h1>CitiTrack — Financial Report</h1>
    <p class="sub">Citi Power Electronic Supply</p>
    <p class="sub">Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p>
    <p class="sub">Generated: {{ now()->format('M d, Y g:i A') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Metric</th>
            <th class="num">Value</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>Total Revenue</td>       <td class="num">₱{{ number_format($revenue, 2) }}</td></tr>
        <tr><td>Gross Profit</td>        <td class="num">₱{{ number_format($profit, 2) }}</td></tr>
        <tr><td>Margin</td>              <td class="num">{{ $revenue > 0 ? number_format(($profit / $revenue) * 100, 2) : '0.00' }}%</td></tr>
        <tr><td>Transaction Count</td>   <td class="num">{{ $count }}</td></tr>
        <tr><td>Average Sale</td>        <td class="num">₱{{ number_format($count > 0 ? $revenue / $count : 0, 2) }}</td></tr>
    </tbody>
</table>

<h2 style="font-size: 14px; margin-top: 24px;">Payment Breakdown</h2>
<table>
    <thead>
        <tr>
            <th>Method</th>
            <th class="num">Amount</th>
            <th class="num">Share</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Cash</td>
            <td class="num">₱{{ number_format($cashTotal, 2) }}</td>
            <td class="num">{{ $revenue > 0 ? number_format(($cashTotal / $revenue) * 100, 2) : '0.00' }}%</td>
        </tr>
        <tr>
            <td>GCash</td>
            <td class="num">₱{{ number_format($gcashTotal, 2) }}</td>
            <td class="num">{{ $revenue > 0 ? number_format(($gcashTotal / $revenue) * 100, 2) : '0.00' }}%</td>
        </tr>
        <tr class="totals">
            <td>Total</td>
            <td class="num">₱{{ number_format($cashTotal + $gcashTotal, 2) }}</td>
            <td class="num">100.00%</td>
        </tr>
    </tbody>
</table>

<div class="footer">
    <p>— End of Report —</p>
</div>

</body>
</html>