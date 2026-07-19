<?php
namespace App\Core;

use RuntimeException;

final class View
{
    public function __construct(private readonly string $basePath) {}

    public function render(string $name, array $data = []): void
    {
        $viewFile = $this->basePath . '/' . str_replace('.', '/', $name) . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException("View not found: {$name}");
        }
        extract($data, EXTR_SKIP);
        require $viewFile;
        unset($_SESSION['_old'], $_SESSION['_errors'], $_SESSION['_flash'], $_SESSION['_invite_link']);
    }
}
