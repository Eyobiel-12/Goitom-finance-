<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Uw verificatiecode' }}</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .otp-code {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            text-align: center;
        }
        .otp-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Goitom Finance</h1>
        @if($type === 'login')
            <h2>Uw inlogcode</h2>
        @elseif($type === 'registration')
            <h2>Verifieer uw account</h2>
        @elseif($type === 'password_reset')
            <h2>Reset uw wachtwoord</h2>
        @else
            <h2>Uw verificatiecode</h2>
        @endif
    </div>
    
    <div class="content">
        @if($type === 'login')
            <p>Beste gebruiker,</p>
            <p>U heeft een inlogcode aangevraagd voor uw Goitom Finance account. Gebruik de onderstaande code om in te loggen:</p>
        @elseif($type === 'registration')
            <p>Welkom bij Goitom Finance!</p>
            <p>Bedankt voor uw registratie. Gebruik de onderstaande code om uw account te verifiëren:</p>
        @elseif($type === 'password_reset')
            <p>Beste gebruiker,</p>
            <p>U heeft een wachtwoord reset aangevraagd. Gebruik de onderstaande code om uw wachtwoord te resetten:</p>
        @else
            <p>Beste gebruiker,</p>
            <p>Gebruik de onderstaande verificatiecode:</p>
        @endif
        
        <div class="otp-code">
            <p><strong>Uw verificatiecode:</strong></p>
            <div class="otp-number">{{ $otp->code }}</div>
            <p><small>Deze code is 10 minuten geldig</small></p>
        </div>

        <div class="warning">
            <strong>⚠️ Belangrijk:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Deel deze code nooit met anderen</li>
                <li>Deze code verloopt over 10 minuten</li>
                <li>Als u deze code niet heeft aangevraagd, negeer dan deze e-mail</li>
            </ul>
        </div>

        @if($type === 'registration')
            <p>Na verificatie kunt u direct aan de slag met uw financiële administratie!</p>
        @endif
        
        <p>Met vriendelijke groet,<br>
        Het Goitom Finance Team</p>
    </div>
    
    <div class="footer">
        <p>Dit bericht is verzonden via het Goitom Finance platform.</p>
        <p>© {{ date('Y') }} Goitom Finance. Alle rechten voorbehouden.</p>
    </div>
</body>
</html>
