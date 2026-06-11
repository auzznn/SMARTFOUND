<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Auth\GoogleOAuthService;
use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GoogleCallbackAction
{
    public function __construct(
        private GoogleOAuthService $google,
        private PdoUserRepository  $users,
        private JwtService         $jwt,
        private string             $frontendUrl
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $params = $request->getQueryParams();
        $code   = $params['code']  ?? '';
        $state  = $params['state'] ?? '';

        if (empty($code)) {
            return $this->redirectWithError('Missing OAuth code.');
        }

        try {
            $profile = $this->google->getUserProfile($code);
        } catch (\Throwable $e) {
            return $this->redirectWithError('Google OAuth failed: ' . $e->getMessage());
        }

        $googleId = $profile['sub'];
        $email    = strtolower($profile['email']);
        $name     = $profile['name'];

        // ── Upsert user ──────────────────────────────────────────────────────
        $user = $this->users->findByGoogleId($googleId);

        if (!$user) {
            // Check if email already registered (without Google)
            $user = $this->users->findByEmail($email);
            if ($user) {
                // Link Google ID to existing account
                $this->users->update((int)$user['uuid'], [
                    'google_id' => $googleId,
                    'fullname'  => $user['fullname'] ?: $name,
                ]);
                $user = $this->users->findById((int)$user['uuid']);
            } else {
                // Create new student account
                $username = $this->generateUsername($email);
                $uuid     = $this->users->create([
                    'roleid'    => 1, // student
                    'username'  => $username,
                    'fullname'  => $name,
                    'email'     => $email,
                    'google_id' => $googleId,
                ]);
                $user = $this->users->findById($uuid);
            }
        }

        $accessToken  = $this->jwt->createAccessToken($user);
        $refreshToken = $this->jwt->createRefreshToken((int)$user['uuid']);

        // Redirect to frontend with access_token in query param
        // (In production consider using a short-lived code exchange instead)
        $redirectUrl = rtrim($this->frontendUrl, '/') . '/auth/google/success'
            . '?token=' . urlencode($accessToken);

        $ttl     = $this->jwt->getRefreshTtl();
        $expires = gmdate('D, d M Y H:i:s T', time() + $ttl);

        return $response
            ->withStatus(302)
            ->withHeader('Location', $redirectUrl)
            ->withAddedHeader(
                'Set-Cookie',
                "refresh_token={$refreshToken}; HttpOnly; SameSite=Strict; Path=/; Expires={$expires}"
            )
            ->withAddedHeader(
                'Set-Cookie',
                'oauth_state=; HttpOnly; SameSite=Lax; Path=/; Max-Age=0'
            );
    }

    private function generateUsername(string $email): string
    {
        $prefix    = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]));
        $prefix    = $prefix ?: 'user';
        $candidate = $prefix;
        $i         = 1;
        while ($this->users->usernameExists($candidate)) {
            $candidate = $prefix . $i;
            $i++;
        }
        return $candidate;
    }

    private function redirectWithError(string $message): ResponseInterface
    {
        $url = rtrim($this->frontendUrl, '/') . '/auth/google/error'
            . '?message=' . urlencode($message);
        return (new \Slim\Psr7\Response(302))->withHeader('Location', $url);
    }
}
