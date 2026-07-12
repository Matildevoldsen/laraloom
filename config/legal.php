<?php

return [
    'effective_date' => env('LEGAL_EFFECTIVE_DATE', '12 July 2026'),
    'terms_version' => env('LEGAL_TERMS_VERSION', '2026-07-12'),
    'privacy_version' => env('LEGAL_PRIVACY_VERSION', '2026-07-12'),

    'operator' => [
        'name' => env('LEGAL_OPERATOR_NAME'),
        'postal_address' => env('LEGAL_OPERATOR_ADDRESS'),
        'country' => env('LEGAL_OPERATOR_COUNTRY'),
        'legal_email' => env('LEGAL_CONTACT_EMAIL'),
        'privacy_email' => env('LEGAL_PRIVACY_EMAIL'),
    ],

    'minimum_age' => (int) env('LEGAL_MINIMUM_AGE', 18),

    'representatives' => [
        'eu' => env('LEGAL_EU_REPRESENTATIVE'),
        'uk' => env('LEGAL_UK_REPRESENTATIVE'),
    ],

    'additional_processors' => env('LEGAL_ADDITIONAL_PROCESSORS'),

    'retention' => [
        'accounts_and_public_content' => env('LEGAL_RETENTION_ACCOUNTS_CONTENT'),
        'direct_messages' => env('LEGAL_RETENTION_DIRECT_MESSAGES'),
        'security_logs' => env('LEGAL_RETENTION_SECURITY_LOGS'),
        'moderation_and_reports' => env('LEGAL_RETENTION_MODERATION_REPORTS'),
        'backups' => env('LEGAL_RETENTION_BACKUPS'),
    ],
];
