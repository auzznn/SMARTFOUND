<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class RegisterAction
{
    public function __construct(
        private PdoUserRepository $users,
        private JwtService        $jwt
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $body     = (array)$request->getParsedBody();

        // ── Input allow-list + sanitize ──────────────────────────────────────
        $username  = trim((string)($body['username']  ?? ''));
        $password  = (string)($body['password']  ?? '');
        $fullname  = trim((string)($body['fullname']  ?? ''));
        $contactno = trim((string)($body['contactno'] ?? ''));
        $email     = trim(strtolower((string)($body['email'] ?? '')));

        // ── Validation ───────────────────────────────────────────────────────
        $errors = [];

        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 100) {
            $errors[] = 'Username must be between 3 and 100 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors[] = 'Username may only contain letters, numbers, underscores, dots, and hyphens.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if (!empty($errors)) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Validation failed.',
                'message' => implode(' ', $errors),
            ]);
        }

        // ── Duplicate check ──────────────────────────────────────────────────
        if ($this->users->findByUsername($username)) {
            return $this->json($response, 409, [
                'success' => false,
                'error'   => 'Conflict',
                'message' => 'Username already exists.',
            ]);
        }

        // ── Create user ──────────────────────────────────────────────────────
        try {
            $uuid = $this->users->create([
                'roleid'        => 1, // student
                'username'      => $username,
                'fullname'      => $fullname ?: null,
                'contactno'     => $contactno ?: null,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'email'         => $email ?: null,
            ]);
        } catch (\Throwable $e) {
            return $this->json($response, 500, [
                'success' => false,
                'error'   => 'Database error',
                'message' => 'Could not create account. Please try again.',
            ]);
        }

        $user = $this->users->findById($uuid);

        $accessToken  = $this->jwt->createAccessToken($user);
        $refreshToken = $this->jwt->createRefreshToken($uuid);

        $resp = $this->json($response, 201, [
            'success'      => true,
            'data'         => [
                'access_token' => $accessToken,
                'user'         => [
                    'uuid'     => $user['uuid'],
                    'username' => htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'),
                    'roleid'   => $user['roleid'],
                    'rolename' => $user['rolename'],
                    'fullname' => $user['fullname'] ? htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') : null,
                ],
            ],
            'message' => 'Registration successful.',
        ]);

        return $this->setRefreshCookie($resp, $refreshToken);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }

    private function setRefreshCookie(ResponseInterface $r, string $token): ResponseInterface
    {
        $ttl     = $this->jwt->getRefreshTtl();
        $expires = gmdate('D, d M Y H:i:s T', time() + $ttl);
        return $r->withAddedHeader(
            'Set-Cookie',
            "refresh_token={$token}; HttpOnly; SameSite=Strict; Path=/; Expires={$expires}"
        );
    }
}
