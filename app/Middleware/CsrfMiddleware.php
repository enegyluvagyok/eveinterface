<?php
namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;

final class CsrfMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!Csrf::verify($request->input('_token'))) {
            http_response_code(419);
            exit('CSRF token mismatch.');
        }
        return $next();
    }
}
