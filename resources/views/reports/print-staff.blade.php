<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Activity — {{ $from }} to {{ $to }}</title>
    @include('reports._print-styles')
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 Print / Save as PDF</button>
    <button onclick="window.close()">Close</button>
</div>

<div class="header">
    <h1>CitiTrack — Staff Activity Report</h1>
    <p class="sub">Citi Power Electronic Supply</p>
    <p class="sub">Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p>
    <p class="sub">Generated: {{ now()->format('M d, Y g:i A') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th class="num">Transactions</th>
            <th class="num">Total Sales</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($staff as $person)
            <tr>
                <td>{{ $person->name }}</td>
                <td>{{ $person->email }}</td>
                <td>{{ ucfirst($person->role) }}</td>
                <td class="num">{{ $person->transactions_count ?? 0 }}</td>
                <td class="num">{{ number_format($person->transactions_sum_total ?? 0, 2) }}</td>
                <td>{{ $person->is_active ? 'Active' : 'Inactive' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">No staff found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <p>— End of Report —</p>
</div>

</body>
</html>