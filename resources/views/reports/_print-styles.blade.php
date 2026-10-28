<style>
    * { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        padding: 24px;
        font-size: 12px;
        color: #111;
        max-width: 1200px;
        margin: 0 auto;
    }
    .header { margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 12px; }
    h1 { font-size: 20px; margin: 0 0 4px; }
    h2 { font-size: 14px; margin: 0; }
    .sub { color: #555; margin: 2px 0; font-size: 11px; }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 6px 8px;
        text-align: left;
        vertical-align: top;
    }
    th {
        background: #f0f0f0;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.5px;
    }
    td.num, th.num { text-align: right; }
    tfoot .totals td {
        background: #f9f9f9;
        font-weight: bold;
    }
    .empty {
        text-align: center;
        color: #888;
        padding: 20px;
    }
    .footer {
        margin-top: 30px;
        text-align: center;
        color: #888;
        font-size: 10px;
    }

    /* Print-only behavior */
    @media print {
        body { padding: 0; max-width: none; }
        .no-print { display: none !important; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
    }

    /* Screen-only print buttons */
    .no-print {
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #ccc;
    }
    .no-print button {
        padding: 8px 16px;
        font-size: 13px;
        cursor: pointer;
        margin-right: 8px;
        border: 1px solid #333;
        background: #fff;
        border-radius: 4px;
    }
    .no-print button:hover { background: #f3f4f6; }
</style>