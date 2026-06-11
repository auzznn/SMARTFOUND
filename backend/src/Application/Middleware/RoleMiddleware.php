<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddleware implements MiddlewareInterface
{
    /** @param string[] $allowedRoles */
    public function __construct(private array $allowedRoles)
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $jwt = $request->getAttribute('jwt');

        if ($jwt === null) {
            return $this->forbidden('Authentication required.');
        }

        $rolename = $jwt->rolename ?? '';

        if (!in_array($rolename, $this->allowedRoles, true)) {
            return $this->forbidden(
                sprintf(
                    'Access denied. Required role(s): %s.',
                    implode(' or ', $this->allowedRoles)
                )
            );
        }

        return $handler->handle($request);
    }

    private function forbidden(string $message): ResponseInterface
    {
        $response = new Response(403);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => false,
            'error'   => 'Forbidden',
            'message' => $message,
        ]));
        return $response;
    }
}
