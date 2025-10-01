<?php

declare(strict_types=1);

return [
    // Standaard prefix voor factuurnummers
    'invoice_prefix' => env('INVOICE_PREFIX', 'INV-'),

    // Standaard aantal dagen tot vervaldatum vanaf issue_date
    'invoice_due_days' => (int) env('INVOICE_DUE_DAYS', 14),
];


