<?php

declare(strict_types=1);

namespace App\Application\Actions\Report;

use App\Infrastructure\Persistence\PdoReportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListClosedReportsAction
{
    public function __construct(private PdoReportRepository $reports)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $q     = $request->getQueryParams();
        $page  = max(1, (int)($q['page']  ?? 1));
        $limit = min(100, max(1, (int)($q['limit'] ?? 10)));

        $data  = $this->reports->findClosed($page, $limit);
        $total = $this->reports->countClosed();

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => [
                'reports'    => $data,
                'pagination' => [
                    'page'        => $page,
                    'limit'       => $limit,
                    'total'       => $total,
                    'total_pages' => (int)ceil($total / $limit),
                ],
            ],
            'message' => 'Closed reports retrieved successfully.',
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
