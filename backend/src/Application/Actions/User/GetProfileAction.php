<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetProfileAction
{
    public function __construct(private PdoUserRepository $users)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $jwt  = $request->getAttribute('jwt');
        $uuid = (int)$jwt->sub;

        $user = $this->users->findById($uuid);

        if (!$user) {
            $response = $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode([
                'success' => false,
                'error'   => 'Not Found',
                'message' => 'User account not found.',
            ]));
            return $response;
        }

        // Remove sensitive fields
        unset($user['password_hash'], $user['google_id']);

        // Sanitize output
        foreach (['username', 'fullname', 'contactno', 'email'] as $field) {
            if (isset($user[$field]) && is_string($user[$field])) {
                $user[$field] = htmlspecialchars($user[$field], ENT_QUOTES, 'UTF-8');
            }
        }

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => ['user' => $user],
            'message' => 'Profile retrieved successfully.',
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
