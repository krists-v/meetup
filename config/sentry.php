<?php

// The Sentry service provider merges the rest of its options over this file.
// Reporting stays off until SENTRY_LARAVEL_DSN is set. See bootstrap/app.php.

return [

    'dsn' => env('SENTRY_LARAVEL_DSN'),

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0),

    'send_default_pii' => (bool) env('SENTRY_SEND_DEFAULT_PII', false),

];
