<?php
namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

final class JwtService
{
    public function issue(int $userId): string
    {
        $now = time();
        $payload = [
            'iss' => config('security.jwt_issuer'),
            'aud' => config('security.jwt_audience'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + config('security.jwt_ttl', 3600),
            'sub' => (string)$userId,
            'jti' => bin2hex(random_bytes(16)),
        ];
        return JWT::encode($payload, config('security.jwt_secret'), 'HS256');
    }

    public function decode(string $token): stdClass
    {
        return JWT::decode($token, new Key(config('security.jwt_secret'), 'HS256'));
    }
}
