<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;

final class AdminMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if ((Auth::user()['role'] ?? null) !== 'admin') {
            http_response_code(403);
            view('errors.403');
            return null;
        }
        return $next();
    }
}
