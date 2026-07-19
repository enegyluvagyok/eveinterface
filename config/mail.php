<?php
return [
    'host' => $_ENV['SMTP_HOST'] ?? '',
    'port' => (int) ($_ENV['SMTP_PORT'] ?? 587),
    'username' => $_ENV['SMTP_USERNAME'] ?? '',
    'password' => $_ENV['SMTP_PASSWORD'] ?? '',
    'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'EVE - Gewiss Gatepass Interface',
];
