<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class RateLimitMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $max = config('security.rate_limit_max', 60);
        $window = config('security.rate_limit_window', 60);
        $bucket = floor(time() / $window);
        $key = hash('sha256', $request->ip() . '|' . $request->path() . '|' . $bucket);
        $file = dirname(__DIR__, 2) . '/storage/cache/rl_' . $key;
        $count = is_file($file) ? (int)file_get_contents($file) : 0;
        if ($count >= $max) Response::json(['error' => 'Too many requests'], 429);
        file_put_contents($file, (string)($count + 1), LOCK_EX);
        header('X-RateLimit-Limit: ' . $max);
        header('X-RateLimit-Remaining: ' . max(0, $max - $count - 1));
        return $next();
    }
}
