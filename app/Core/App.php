<?php
namespace App\Core;

use RuntimeException;

final class App
{
    private static ?self $instance = null;
    private array $container = [];

    private function __construct() {}

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function set(string $key, mixed $value): void
    {
        $this->container[$key] = $value;
    }

    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->container)) {
            throw new RuntimeException("Container entry not found: {$key}");
        }
        return $this->container[$key];
    }
}
