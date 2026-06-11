<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Infrastructure\Auth\JwtService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(private JwtService $jwtService)
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $this->unauthorized('Authorization header is missing.');
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Authorization header must use Bearer scheme.');
        }

        $token = trim(substr($authHeader, 7));

        if (empty($token)) {
            return $this->unauthorized('Token is empty.');
        }

        try {
            $payload = $this->jwtService->decodeAccessToken($token);
        } catch (ExpiredException) {
            return $this->unauthorized('Token has expired.');
        } catch (SignatureInvalidException) {
            return $this->unauthorized('Token signature is invalid.');
        } catch (\Throwable) {
            return $this->unauthorized('Token is invalid.');
        }

        // Attach decoded payload to request attribute
        $request = $request->withAttribute('jwt', $payload);

        return $handler->handle($request);
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = new Response(401);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => false,
            'error'   => 'Unauthorized',
            'message' => $message,
        ]));
        return $response;
    }
}
