<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style> body { font-family: Arial, sans-serif; color:#0f172a; } .btn{background:#dc2626;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none;} .muted{color:#64748b;font-size:12px;} </style>
</head>
<body>
    <h2>Quote {{ $quote->number }}</h2>
    <p>Beste {{ $quote->client?->name ?? 'relatie' }},</p>
    <p>Bijgevoegd vindt u de offerte. Neem gerust contact op bij vragen.</p>
    <p class="muted">Verzonden door Goitom Finance.</p>
</body>
</html>


