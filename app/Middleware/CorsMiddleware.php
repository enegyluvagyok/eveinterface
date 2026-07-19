<?php
namespace App\Middleware;

use App\Core\Request;

final class CorsMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin && in_array($origin, config('security.cors_allowed_origins', []), true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
            header('Access-Control-Allow-Headers: Authorization, Content-Type');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        }
        if ($request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        return $next();
    }
}
