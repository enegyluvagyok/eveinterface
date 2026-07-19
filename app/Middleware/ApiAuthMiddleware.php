<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\JwtService;
use Throwable;

final class ApiAuthMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $token = $request->bearerToken();
        if (!$token) Response::json(['error' => 'Missing bearer token'], 401);
        try {
            $claims = (new JwtService())->decode($token);
            $_SERVER['API_USER_ID'] = (int)$claims->sub;
        } catch (Throwable) {
            Response::json(['error' => 'Invalid or expired token'], 401);
        }
        return $next();
    }
}
