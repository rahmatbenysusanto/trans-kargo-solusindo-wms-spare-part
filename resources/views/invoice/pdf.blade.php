<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice PDF - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .details-table th {
            text-align: left;
            background: #f3f4f6;
            padding: 8px;
            border: 1px solid #ddd;
        }

        .details-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .amount-section {
            text-align: right;
            font-size: 16px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            background: #e5e7eb;
            border-radius: 4px;
            font-size: 10px;
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="invoice-title">OFFICIAL INVOICE RECORD</div>
        <div>Trans Kargo Solusindo - WMS Spare Room</div>
    </div>

    <table class="details-table">
        <tr>
            <th width="30%">Invoice Number</th>
            <td><strong>{{ $invoice->invoice_number }}</strong></td>
        </tr>
        <tr>
            <th>Invoice Date</th>
            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d F Y') }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $invoice->description ?? '-' }}</td>
        </tr>
    </table>

    <h3>Linked Transactions</h3>
    <table class="details-table">
        <thead>
            <tr>
                <th width="10%">#</th>
                <th width="20%">Type</th>
                <th width="40%">Reference Number</th>
                <th width="30%">Client</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach ($invoice->inbounds as $in)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>INBOUND</td>
                    <td>{{ $in->number }}</td>
                    <td>{{ $in->client->name ?? '-' }}</td>
                </tr>
            @endforeach
            @foreach ($invoice->outbounds as $out)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>OUTBOUND</td>
                    <td>{{ $out->number }}</td>
                    <td>{{ $out->client->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="amount-section">
        <strong>Total Amount:</strong>
        <span style="color: #1e40af; font-size: 20px; font-weight: bold;">
            IDR {{ number_format($invoice->amount, 0, ',', '.') }}
        </span>
    </div>

    <div class="footer">
        Printed on {{ date('d/m/Y H:i:s') }}<br>
        This is a system generated document.
    </div>
</body>

</html>
