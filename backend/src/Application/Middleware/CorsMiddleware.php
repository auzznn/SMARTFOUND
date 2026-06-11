<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private string $frontendUrl;

    public function __construct()
    {
        $this->frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Handle OPTIONS preflight — return 200 immediately before any other middleware
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response(200);
            return $this->addCorsHeaders($response);
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response);
    }

    private function addCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin',      $this->frontendUrl)
            ->withHeader('Access-Control-Allow-Methods',     'GET, POST, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers',     'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Max-Age',           '86400');
    }
}
