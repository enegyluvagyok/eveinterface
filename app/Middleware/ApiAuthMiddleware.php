<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
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
            $userId = (int)$claims->sub;
            if (!User::find($userId)) {
                Response::json(['error' => 'Invalid or expired token'], 401);
            }
            $_SERVER['API_USER_ID'] = $userId;
        } catch (Throwable) {
            Response::json(['error' => 'Invalid or expired token'], 401);
        }
        return $next();
    }
}
