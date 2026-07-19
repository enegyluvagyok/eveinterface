<?php
namespace App\Models;

final class Contractor
{
    public static function all(): array
    {
        $stmt = app('db')->pdo()->query('SELECT * FROM contractors ORDER BY name');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = app('db')->pdo()->prepare('SELECT * FROM contractors WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $id, string $name): void
    {
        $stmt = app('db')->pdo()->prepare(
            'INSERT INTO contractors (id, name, created_at, updated_at) VALUES (:id, :name, NOW(), NOW())'
        );
        $stmt->execute(['id' => $id, 'name' => trim($name)]);
    }
}
