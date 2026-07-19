<?php
namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) return false;
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
        }
        self::loginAs((int)$user['id']);
        return true;
    }

    public static function loginAs(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    public static function check(): bool { return isset($_SESSION['user_id']); }
    public static function id(): ?int { return self::check() ? (int)$_SESSION['user_id'] : null; }
    public static function user(): ?array { return self::id() ? User::find(self::id()) : null; }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
