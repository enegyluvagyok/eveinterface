<?php
namespace App\Core;

use Closure;

final class Router
{
    private array $routes = [];

    public function get(string $path, array|Closure $handler, array $middleware = []): void { $this->add('GET', $path, $handler, $middleware); }
    public function post(string $path, array|Closure $handler, array $middleware = []): void { $this->add('POST', $path, $handler, $middleware); }

    private function add(string $method, string $path, array|Closure $handler, array $middleware): void
    {
        $this->routes[$method]['/' . trim($path, '/')] = compact('handler', 'middleware');
    }

    public function dispatch(Request $request): mixed
    {
        $route = $this->routes[$request->method()][$request->path()] ?? null;
        if (!$route) {
            http_response_code(404);
            view('errors.404');
            return null;
        }

        $core = fn() => $this->invoke($route['handler'], $request);
        $pipeline = array_reduce(
            array_reverse($route['middleware']),
            fn($next, $middleware) => fn() => (new $middleware())->handle($request, $next),
            $core
        );
        return $pipeline();
    }

    private function invoke(array|Closure $handler, Request $request): mixed
    {
        if ($handler instanceof Closure) return $handler($request);
        [$class, $method] = $handler;
        return (new $class())->{$method}($request);
    }
}
