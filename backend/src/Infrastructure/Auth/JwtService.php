<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private const ALGORITHM = 'HS256';

    public function __construct(
        private string $secret,
        private int    $accessTtl,
        private int    $refreshTtl
    ) {
    }

    /**
     * Create a short-lived access token (15 min).
     *
     * @param array{uuid: int, username: string, roleid: int, rolename: string} $user
     */
    public function createAccessToken(array $user): string
    {
        $now = time();
        $payload = [
            'iss'      => 'smartfound',
            'aud'      => 'smartfound-client',
            'iat'      => $now,
            'exp'      => $now + $this->accessTtl,
            'type'     => 'access',
            'sub'      => (int)$user['uuid'],
            'username' => $user['username'],
            'roleid'   => (int)$user['roleid'],
            'rolename' => $user['rolename'],
        ];
        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    /**
     * Create a long-lived refresh token (7 days).
     */
    public function createRefreshToken(int $uuid): string
    {
        $now = time();
        $payload = [
            'iss'  => 'smartfound',
            'iat'  => $now,
            'exp'  => $now + $this->refreshTtl,
            'type' => 'refresh',
            'sub'  => $uuid,
        ];
        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    /**
     * Decode and verify an access token.
     * Throws on invalid/expired.
     */
    public function decodeAccessToken(string $token): object
    {
        $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));
        if (($decoded->type ?? '') !== 'access') {
            throw new \InvalidArgumentException('Not an access token.');
        }
        return $decoded;
    }

    /**
     * Decode and verify a refresh token.
     * Throws on invalid/expired.
     */
    public function decodeRefreshToken(string $token): object
    {
        $decoded = JWT::decode($token, new Key($this->secret, self::ALGORITHM));
        if (($decoded->type ?? '') !== 'refresh') {
            throw new \InvalidArgumentException('Not a refresh token.');
        }
        return $decoded;
    }

    public function getRefreshTtl(): int
    {
        return $this->refreshTtl;
    }
}
