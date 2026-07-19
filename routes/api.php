<?php
use App\Controllers\ApiController;
use App\Middleware\ApiAuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;

$api = [CorsMiddleware::class, RateLimitMiddleware::class];
$router->post('/api/token', [ApiController::class, 'token'], $api);
$router->get('/api/me', [ApiController::class, 'me'], [...$api, ApiAuthMiddleware::class]);
$router->get('/api/employees', [ApiController::class, 'employees'], [...$api, ApiAuthMiddleware::class]);
$router->get('/api/employees/file', [ApiController::class, 'employeeFile'], [...$api, ApiAuthMiddleware::class]);
$router->post('/api/employees/ack', [ApiController::class, 'employeesAck'], [...$api, ApiAuthMiddleware::class]);
