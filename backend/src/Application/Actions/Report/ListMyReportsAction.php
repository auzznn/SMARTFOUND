<?php

declare(strict_types=1);

namespace App\Application\Actions\Report;

use App\Infrastructure\Persistence\PdoReportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListMyReportsAction
{
    public function __construct(private PdoReportRepository $reports)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $jwt  = $request->getAttribute('jwt');
        $uuid = (int)$jwt->sub;

        $data = $this->reports->findByUser($uuid);

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => ['reports' => $data],
            'message' => 'Your reports retrieved successfully.',
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
