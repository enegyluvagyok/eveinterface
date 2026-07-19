<?php
namespace App\Core;

final class Locale
{
    private const COOKIE = 'locale';

    public static function resolve(): string
    {
        $cookie = $_COOKIE[self::COOKIE] ?? null;
        $supported = config('locale.supported');
        if ($cookie && in_array($cookie, $supported, true)) return $cookie;
        return config('locale.default');
    }

    public static function set(string $locale): void
    {
        if (!in_array($locale, config('locale.supported'), true)) return;
        setcookie(self::COOKIE, $locale, time() + 60 * 60 * 24 * 365, '/', '', false, true);
    }
}
