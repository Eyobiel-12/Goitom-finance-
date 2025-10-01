<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maandelijks Rapport - {{ $monthName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 12px 12px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        .section {
            margin: 30px 0;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section h3 {
            color: #dc2626;
            border-bottom: 2px solid #fecaca;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .invoice-list, .expense-list {
            list-style: none;
            padding: 0;
        }
        .invoice-list li, .expense-list li {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .invoice-list li:last-child, .expense-list li:last-child {
            border-bottom: none;
        }
        .status-paid { color: #059669; font-weight: bold; }
        .status-draft { color: #d97706; font-weight: bold; }
        .status-overdue { color: #dc2626; font-weight: bold; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Maandelijks Rapport</h1>
        <p>{{ $monthName }}</p>
        <p>Goitom Finance</p>
    </div>

    <div class="content">
        <p>Beste {{ $user->name }},</p>
        
        <p>Hier is uw maandelijkse financiële rapport voor <strong>{{ $monthName }}</strong>. Dit rapport geeft u een overzicht van uw inkomsten, uitgaven en facturen.</p>

        <!-- Key Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">€{{ number_format($reportData['total_income'], 2, ',', '.') }}</div>
                <div class="stat-label">Totale Inkomsten</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€{{ number_format($reportData['total_expenses'], 2, ',', '.') }}</div>
                <div class="stat-label">Totale Uitgaven</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€{{ number_format($reportData['net_profit'], 2, ',', '.') }}</div>
                <div class="stat-label">Netto Winst</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $reportData['invoices_count'] }}</div>
                <div class="stat-label">Facturen</div>
            </div>
        </div>

        <!-- Invoices Section -->
        @if(count($reportData['invoices']) > 0)
        <div class="section">
            <h3>📄 Facturen deze maand</h3>
            <ul class="invoice-list">
                @foreach($reportData['invoices'] as $invoice)
                <li>
                    <span>
                        <strong>{{ $invoice['invoice_number'] }}</strong> - {{ $invoice['client_name'] }}
                        <br><small>{{ $invoice['issue_date'] }}</small>
                    </span>
                    <span>
                        €{{ number_format($invoice['total_amount'], 2, ',', '.') }}
                        <br><span class="status-{{ $invoice['status'] }}">{{ ucfirst($invoice['status']) }}</span>
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Expenses Section -->
        @if(count($reportData['expenses']) > 0)
        <div class="section">
            <h3>💰 Uitgaven deze maand</h3>
            <ul class="expense-list">
                @foreach($reportData['expenses'] as $expense)
                <li>
                    <span>
                        <strong>{{ $expense['description'] }}</strong>
                        @if($expense['vendor'])
                            <br><small>{{ $expense['vendor'] }}</small>
                        @endif
                    </span>
                    <span>
                        €{{ number_format($expense['amount'], 2, ',', '.') }}
                        <br><small>{{ $expense['category'] }}</small>
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Insights -->
        <div class="section">
            <h3>💡 Inzichten</h3>
            @if($reportData['net_profit'] > 0)
                <p>✅ Uw bedrijf heeft deze maand een winst van <strong>€{{ number_format($reportData['net_profit'], 2, ',', '.') }}</strong> behaald.</p>
            @else
                <p>⚠️ Uw bedrijf heeft deze maand een verlies van <strong>€{{ number_format(abs($reportData['net_profit']), 2, ',', '.') }}</strong> geleden.</p>
            @endif

            @if($reportData['overdue_invoices'] > 0)
                <p>🔔 U heeft <strong>{{ $reportData['overdue_invoices'] }}</strong> achterstallige facturen. Overweeg om herinneringen te versturen.</p>
            @endif

            @if($reportData['expenses_count'] > 0)
                <p>📈 U heeft <strong>{{ $reportData['expenses_count'] }}</strong> uitgaven geregistreerd voor een totaal van €{{ number_format($reportData['total_expenses'], 2, ',', '.') }}.</p>
            @endif
        </div>

        <p>Dit rapport helpt u om uw financiën beter te begrijpen en weloverwogen beslissingen te nemen voor uw bedrijf.</p>

        <p>Met vriendelijke groet,<br>
        Het Goitom Finance Team</p>
    </div>

    <div class="footer">
        <p>Dit is een automatisch gegenereerd rapport voor {{ $monthName }}.</p>
        <p>Log in op uw Goitom Finance account voor meer gedetailleerde rapporten en analyses.</p>
    </div>
</body>
</html>
