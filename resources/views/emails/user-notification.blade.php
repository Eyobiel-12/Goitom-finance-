<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
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
        .message {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Goitom Finance</h1>
        <h2>{{ $subject }}</h2>
    </div>
    
    <div class="content">
        <p>Beste gebruiker,</p>
        
        <div class="message">
            {!! nl2br(e($message)) !!}
        </div>
        
        <p>Met vriendelijke groet,<br>
        Het Goitom Finance Team</p>
    </div>
    
    <div class="footer">
        <p>Dit bericht is verzonden via het Goitom Finance platform.</p>
        <p>© {{ date('Y') }} Goitom Finance. Alle rechten voorbehouden.</p>
    </div>
</body>
</html>
