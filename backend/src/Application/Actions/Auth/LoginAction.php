<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginAction
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
        $username = trim((string)($body['username'] ?? ''));
        $password = (string)($body['password'] ?? '');

        if (empty($username) || empty($password)) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Validation failed.',
                'message' => 'Username and password are required.',
            ]);
        }

        $user = $this->users->findByUsername($username);

        // Constant-time failure for unknown users / wrong passwords
        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            return $this->json($response, 401, [
                'success' => false,
                'error'   => 'Unauthorized',
                'message' => 'Invalid username or password.',
            ]);
        }

        $accessToken  = $this->jwt->createAccessToken($user);
        $refreshToken = $this->jwt->createRefreshToken((int)$user['uuid']);

        $resp = $this->json($response, 200, [
            'success' => true,
            'data'    => [
                'access_token' => $accessToken,
                'user'         => [
                    'uuid'     => (int)$user['uuid'],
                    'username' => htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'),
                    'roleid'   => (int)$user['roleid'],
                    'rolename' => $user['rolename'],
                    'fullname' => $user['fullname']
                        ? htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8')
                        : null,
                ],
            ],
            'message' => 'Login successful.',
        ]);

        return $this->setRefreshCookie($resp, $refreshToken);
    }

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
