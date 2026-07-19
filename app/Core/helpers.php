<?php
use App\Core\App;
use App\Core\Csrf;

function app(?string $key = null): mixed
{
    return $key === null ? App::instance() : App::instance()->get($key);
}

function config(string $key, mixed $default = null): mixed
{
    [$file, $item] = array_pad(explode('.', $key, 2), 2, null);
    $config = app('config')[$file] ?? [];
    return $item === null ? $config : ($config[$item] ?? $default);
}

function view(string $name, array $data = []): void
{
    app('view')->render($name, $data);
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 302);
    exit;
}

function old(string $key, string $default = ''): string
{
    return htmlspecialchars($_SESSION['_old'][$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function t(string $key): string
{
    static $cache = [];
    $locale = app('locale');
    $cache[$locale] ??= require dirname(__DIR__, 2) . "/resources/lang/{$locale}.php";
    return $cache[$locale][$key] ?? $key;
}
