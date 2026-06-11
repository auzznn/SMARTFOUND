<?php

declare(strict_types=1);

namespace App\Application\Actions\Report;

use App\Infrastructure\Persistence\PdoReportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteReportAction
{
    public function __construct(
        private PdoReportRepository $reports,
        private array               $uploadSettings
    ) {
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

        // Physical deletion of uploaded image if it exists
        if (!empty($report['png'])) {
            $this->deleteUploadedFile($report['png']);
        }

        $this->reports->delete($reportid);

        return $this->json($response, 200, [
            'success' => true,
            'data'    => null,
            'message' => "Report #{$reportid} deleted successfully.",
        ]);
    }

    private function deleteUploadedFile(string $urlOrPath): void
    {
        // The stored URL is like http://localhost:8080/uploads/abc.jpg
        // Extract just the filename
        $filename = basename(parse_url($urlOrPath, PHP_URL_PATH) ?? $urlOrPath);

        $uploadDir = $this->uploadSettings['dir'];
        if (!str_starts_with($uploadDir, '/') && !preg_match('/^[A-Z]:/i', $uploadDir)) {
            $uploadDir = __DIR__ . '/../../../../' . $uploadDir;
        }
        $fullPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
