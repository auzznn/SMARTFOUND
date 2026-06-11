<?php

declare(strict_types=1);

namespace App\Application\Actions\Report;

use App\Infrastructure\Persistence\PdoReportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateReportStatusAction
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
        $jwt      = $request->getAttribute('jwt');
        $callerUuid = (int)$jwt->sub;

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

        // Only the report owner can change the status
        if ((int)$report['uuid'] !== $callerUuid) {
            return $this->json($response, 403, [
                'success' => false,
                'error'   => 'Forbidden',
                'message' => 'Only the report owner can update the status.',
            ]);
        }

        $body      = (array)$request->getParsedBody();
        $newStatus = trim((string)($body['status'] ?? ''));

        // Only valid transition: open → closed
        if ($newStatus !== 'closed') {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Invalid status',
                'message' => 'Status can only be updated to "closed".',
            ]);
        }

        if ($report['status'] === 'closed') {
            return $this->json($response, 409, [
                'success' => false,
                'error'   => 'Conflict',
                'message' => 'Report is already closed.',
            ]);
        }

        $this->reports->updateStatus($reportid, 'closed');
        $updated = $this->reports->findById($reportid);

        return $this->json($response, 200, [
            'success' => true,
            'data'    => ['report' => $updated],
            'message' => 'Report closed successfully.',
        ]);
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
