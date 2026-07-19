<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;

final class AuthMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!Auth::check()) redirect('/login');
        return $next();
    }
}
