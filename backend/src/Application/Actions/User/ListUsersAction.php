<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListUsersAction
{
    public function __construct(private PdoUserRepository $users)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $all = $this->users->findAll();

        // Strip sensitive fields and sanitize output
        $users = array_map(function (array $user): array {
            unset($user['password_hash'], $user['google_id']);
            foreach (['username', 'fullname', 'contactno', 'email'] as $field) {
                if (isset($user[$field]) && is_string($user[$field])) {
                    $user[$field] = htmlspecialchars($user[$field], ENT_QUOTES, 'UTF-8');
                }
            }
            return $user;
        }, $all);

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => [
                'users' => $users,
                'total' => count($users),
            ],
            'message' => 'Users retrieved successfully.',
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
