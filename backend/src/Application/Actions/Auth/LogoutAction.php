<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        // Clear the httpOnly refresh_token cookie by setting it expired
        $response = $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader(
                'Set-Cookie',
                'refresh_token=; HttpOnly; SameSite=Strict; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0'
            );

        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => null,
            'message' => 'Logged out successfully.',
        ]));

        return $response;
    }
}
