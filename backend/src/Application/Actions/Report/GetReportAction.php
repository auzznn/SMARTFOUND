<?php

declare(strict_types=1);

namespace App\Application\Actions\Report;

use App\Infrastructure\Persistence\PdoReportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetReportAction
{
    public function __construct(private PdoReportRepository $reports)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        array                  $args
    ): ResponseInterface {
        $reportid = (int)($args['id'] ?? 0);

        if ($reportid <= 0) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Invalid ID',
                'message' => 'Report ID must be a positive integer.',
            ]);
        }

        $report = $this->reports->findById($reportid);

        if (!$report) {
            return $this->json($response, 404, [
                'success' => false,
                'error'   => 'Not Found',
                'message' => "Report #{$reportid} not found.",
            ]);
        }

        return $this->json($response, 200, [
            'success' => true,
            'data'    => ['report' => $report],
            'message' => 'Report retrieved successfully.',
        ]);
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
