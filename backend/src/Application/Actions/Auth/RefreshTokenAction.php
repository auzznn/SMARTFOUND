<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Persistence\PdoUserRepository;
use Firebase\JWT\ExpiredException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RefreshTokenAction
{
    public function __construct(
        private JwtService        $jwt,
        private ?PdoUserRepository $users = null
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        // Read refresh_token from httpOnly cookie
        $cookies = $request->getCookieParams();
        $token   = $cookies['refresh_token'] ?? '';

        if (empty($token)) {
            return $this->json($response, 401, [
                'success' => false,
                'error'   => 'Unauthorized',
                'message' => 'Refresh token cookie is missing.',
            ]);
        }

        try {
            $payload = $this->jwt->decodeRefreshToken($token);
        } catch (ExpiredException) {
            return $this->json($response, 401, [
                'success' => false,
                'error'   => 'Unauthorized',
                'message' => 'Refresh token has expired. Please log in again.',
            ]);
        } catch (\Throwable) {
            return $this->json($response, 401, [
                'success' => false,
                'error'   => 'Unauthorized',
                'message' => 'Refresh token is invalid.',
            ]);
        }

        $uuid = (int)$payload->sub;

        // Look up the user for fresh role info; fall back to payload values if unavailable
        $userData = $this->users ? $this->users->findById($uuid) : null;

        $newAccessToken = $this->jwt->createAccessToken([
            'uuid'     => $uuid,
            'username' => $userData['username'] ?? ($payload->username ?? ''),
            'roleid'   => $userData['roleid']   ?? ($payload->roleid   ?? 1),
            'rolename' => $userData['rolename']  ?? ($payload->rolename  ?? 'student'),
        ]);

        return $this->json($response, 200, [
            'success' => true,
            'data'    => ['access_token' => $newAccessToken],
            'message' => 'Access token refreshed.',
        ]);
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
