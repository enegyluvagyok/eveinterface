<?php
namespace App\Models;

final class User
{
    public static function find(int $id): ?array
    {
        $stmt = app('db')->pdo()->prepare('SELECT id, name, email, phone, role, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = app('db')->pdo()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => mb_strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        $stmt = app('db')->pdo()->query('SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function create(string $name, string $email, string $password, string $role = 'user', ?string $phone = null): int
    {
        $stmt = app('db')->pdo()->prepare(
            'INSERT INTO users (name, email, phone, password_hash, role, created_at, updated_at) VALUES (:name, :email, :phone, :password, :role, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => trim($name),
            'email' => mb_strtolower(trim($email)),
            'phone' => $phone !== null && trim($phone) !== '' ? trim($phone) : null,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
        return (int)app('db')->pdo()->lastInsertId();
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $stmt = app('db')->pdo()->prepare('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public static function updatePhone(int $id, ?string $phone): void
    {
        $stmt = app('db')->pdo()->prepare('UPDATE users SET phone = :phone, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['phone' => $phone !== null && trim($phone) !== '' ? trim($phone) : null, 'id' => $id]);
    }

    public static function updateRole(int $id, string $role): void
    {
        $stmt = app('db')->pdo()->prepare('UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['role' => $role, 'id' => $id]);
    }

    public static function setPasswordResetToken(int $id, string $tokenHash, string $expiresAt): void
    {
        $stmt = app('db')->pdo()->prepare(
            'UPDATE users SET password_reset_token_hash = :hash, password_reset_expires_at = :expires, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['hash' => $tokenHash, 'expires' => $expiresAt, 'id' => $id]);
    }

    public static function findByValidResetTokenHash(string $tokenHash): ?array
    {
        $stmt = app('db')->pdo()->prepare(
            'SELECT * FROM users WHERE password_reset_token_hash = :hash AND password_reset_expires_at > NOW() LIMIT 1'
        );
        $stmt->execute(['hash' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public static function clearPasswordResetToken(int $id): void
    {
        $stmt = app('db')->pdo()->prepare(
            'UPDATE users SET password_reset_token_hash = NULL, password_reset_expires_at = NULL, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public static function contractorIds(int $userId): array
    {
        $stmt = app('db')->pdo()->prepare('SELECT contractor_id FROM user_contractors WHERE user_id = :id');
        $stmt->execute(['id' => $userId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'contractor_id'));
    }

    public static function subcontractorIds(int $userId): array
    {
        $stmt = app('db')->pdo()->prepare('SELECT subcontractor_id FROM user_subcontractors WHERE user_id = :id');
        $stmt->execute(['id' => $userId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'subcontractor_id'));
    }

    public static function syncContractors(int $userId, array $contractorIds): void
    {
        $pdo = app('db')->pdo();
        $pdo->prepare('DELETE FROM user_contractors WHERE user_id = :id')->execute(['id' => $userId]);
        $contractorIds = array_values(array_unique(array_filter(array_map('intval', $contractorIds))));
        if (!$contractorIds) return;
        $stmt = $pdo->prepare('INSERT INTO user_contractors (user_id, contractor_id) VALUES (:user_id, :contractor_id)');
        foreach ($contractorIds as $contractorId) {
            $stmt->execute(['user_id' => $userId, 'contractor_id' => $contractorId]);
        }
    }

    public static function syncSubcontractors(int $userId, array $subcontractorIds): void
    {
        $pdo = app('db')->pdo();
        $pdo->prepare('DELETE FROM user_subcontractors WHERE user_id = :id')->execute(['id' => $userId]);
        $subcontractorIds = array_values(array_unique(array_filter(array_map('intval', $subcontractorIds))));
        if (!$subcontractorIds) return;
        $stmt = $pdo->prepare('INSERT INTO user_subcontractors (user_id, subcontractor_id) VALUES (:user_id, :subcontractor_id)');
        foreach ($subcontractorIds as $subcontractorId) {
            $stmt->execute(['user_id' => $userId, 'subcontractor_id' => $subcontractorId]);
        }
    }

    /** @return array{} | array{allowed_contractor_ids: int[], allowed_subcontractor_ids: int[], created_by: int} empty = admin, unrestricted */
    public static function accessScope(array $user): array
    {
        if (($user['role'] ?? null) === 'admin') return [];
        return [
            'allowed_contractor_ids' => self::contractorIds((int)$user['id']),
            'allowed_subcontractor_ids' => self::subcontractorIds((int)$user['id']),
            'created_by' => (int)$user['id'],
        ];
    }
}
