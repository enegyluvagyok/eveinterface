<?php
return [
    'name' => $_ENV['APP_NAME'] ?? 'EVE - Gewiss Gatepass Interface',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'key' => $_ENV['APP_KEY'] ?? '',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
];
