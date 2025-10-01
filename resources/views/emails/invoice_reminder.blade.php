<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herinnering Betaling Factuur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .invoice-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc2626;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #dc2626;
        }
        .overdue {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Herinnering Betaling Factuur</h1>
        <p>Factuur {{ $invoice->invoice_number }}</p>
    </div>

    <div class="content">
        <p>Beste {{ $invoice->client->name }},</p>

        @if($customMessage)
            <p>{{ $customMessage }}</p>
        @else
            <p>Wij willen u vriendelijk herinneren aan de betaling van factuur <strong>{{ $invoice->invoice_number }}</strong>.</p>
        @endif

        <div class="invoice-details">
            <h3>Factuur Details</h3>
            <p><strong>Factuurnummer:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Factuurdatum:</strong> {{ $invoice->issue_date->format('d-m-Y') }}</p>
            <p><strong>Vervaldatum:</strong> {{ $invoice->due_date->format('d-m-Y') }}</p>
            <p><strong>Bedrag:</strong> <span class="amount">€ {{ number_format($invoice->total_amount, 2, ',', '.') }}</span></p>
        </div>

        @if($daysOverdue > 0)
            <div class="overdue">
                <h3>⚠️ Achterstallig</h3>
                <p>Deze factuur is <strong>{{ $daysOverdue }} {{ $daysOverdue === 1 ? 'dag' : 'dagen' }}</strong> achterstallig.</p>
            </div>
        @endif

        @if($invoice->notes)
            <div style="margin: 20px 0;">
                <h3>Notities</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <p>Gelieve de betaling zo spoedig mogelijk te verrichten. Bij vragen kunt u altijd contact met ons opnemen.</p>

        <p>Met vriendelijke groet,<br>
        {{ $invoice->user->name }}</p>
    </div>

    <div class="footer">
        <p>Dit is een automatische herinnering voor factuur {{ $invoice->invoice_number }}.</p>
        <p>Indien u deze factuur reeds heeft betaald, kunt u deze e-mail negeren.</p>
    </div>
</body>
</html>
