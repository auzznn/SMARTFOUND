<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use League\OAuth2\Client\Provider\Google;

class GoogleOAuthService
{
    private Google $provider;

    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri
    ) {
        $this->provider = new Google([
            'clientId'     => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri'  => $this->redirectUri,
        ]);
    }

    /**
     * Get the Google OAuth authorization URL.
     * Returns [url, state].
     */
    public function getAuthorizationUrl(): array
    {
        $url   = $this->provider->getAuthorizationUrl([
            'scope' => ['openid', 'profile', 'email'],
        ]);
        $state = $this->provider->getState();
        return [$url, $state];
    }

    /**
     * Exchange authorization code for user profile.
     *
     * @return array{sub: string, email: string, name: string}
     * @throws \Exception on failure
     */
    public function getUserProfile(string $code): array
    {
        $token       = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
        $googleUser  = $this->provider->getResourceOwner($token);
        $googleArray = $googleUser->toArray();

        return [
            'sub'   => (string)($googleArray['sub'] ?? $googleUser->getId()),
            'email' => (string)($googleArray['email'] ?? ''),
            'name'  => (string)($googleArray['name'] ?? ''),
        ];
    }
}
