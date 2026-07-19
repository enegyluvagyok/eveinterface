<?php
namespace App\Core;

final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return '/' . trim($path, '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function all(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode(file_get_contents('php://input') ?: '{}', true);
            return is_array($decoded) ? $decoded : [];
        }
        return array_merge($_GET, $_POST);
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        return preg_match('/^Bearer\s+(\S+)$/i', $header, $m) ? $m[1] : null;
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
