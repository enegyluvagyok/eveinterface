<?php
$origins = array_values(array_filter(array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? ''))));
return [
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? '',
    'jwt_issuer' => $_ENV['JWT_ISSUER'] ?? '',
    'jwt_audience' => $_ENV['JWT_AUDIENCE'] ?? '',
    'jwt_ttl' => (int) ($_ENV['JWT_TTL'] ?? 3600),
    'cors_allowed_origins' => $origins,
    'rate_limit_max' => (int) ($_ENV['RATE_LIMIT_MAX'] ?? 60),
    'rate_limit_window' => (int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60),
    'password_reset_ttl' => (int) ($_ENV['PASSWORD_RESET_TTL'] ?? 86400),
];
