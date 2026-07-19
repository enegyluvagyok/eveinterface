<?php
namespace App\Core;

use App\Models\User;

final class PasswordReset
{
    public static function issue(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $ttl = config('security.password_reset_ttl', 86400);
        User::setPasswordResetToken($userId, self::hash($token), date('Y-m-d H:i:s', time() + $ttl));
        return $token;
    }

    public static function verify(string $token): ?array
    {
        if ($token === '') return null;
        return User::findByValidResetTokenHash(self::hash($token));
    }

    public static function consume(int $userId): void
    {
        User::clearPasswordResetToken($userId);
    }

    public static function url(string $token): string
    {
        return rtrim(config('app.url'), '/') . '/reset-password?token=' . urlencode($token);
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
