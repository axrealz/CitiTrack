<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Receipt') — CitiTrack</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 80mm;
            color: #000;
        }
        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }
        .line   { border-top: 1px dashed #000; margin: 6px 0; }
        table   { width: 100%; border-collapse: collapse; }
        td, th  { padding: 2px 0; font-size: 11px; }
        th      { text-align: left; border-bottom: 1px solid #000; }
        .totals td { padding-top: 3px; }
        .totals .label { text-align: right; padding-right: 8px; }
        .totals .value { text-align: right; }

        @media print {
            body { width: auto; padding: 0; }
            .no-print { display: none !important; }
        }

        .no-print button {
            margin-top: 20px;
            padding: 8px 16px;
            font-family: sans-serif;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>

@yield('content')

<div class="no-print center" style="margin-top:20px;">
    <button onclick="window.print()">🖨 Print</button>
    <a href="{{ route('cashier.pos') }}"
       style="display:inline-block;margin-left:8px;padding:8px 16px;
              font-family:sans-serif;font-size:14px;text-decoration:none;
              border:1px solid #333;border-radius:4px;color:#000;background:#fff;">
        ← Back to POS
    </a>
</div>

</body>
</html>