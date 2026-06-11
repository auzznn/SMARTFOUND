<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Infrastructure\Auth\GoogleOAuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GoogleRedirectAction
{
    public function __construct(private GoogleOAuthService $google)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        [$authUrl, $state] = $this->google->getAuthorizationUrl();

        // Store state in session or return it to client — here we use a cookie
        $response = $response
            ->withStatus(302)
            ->withHeader('Location', $authUrl)
            ->withAddedHeader(
                'Set-Cookie',
                "oauth_state={$state}; HttpOnly; SameSite=Lax; Path=/; Max-Age=600"
            );

        return $response;
    }
}
