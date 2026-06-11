<?php

declare(strict_types=1);

namespace App\Application\Actions\Comment;

use App\Infrastructure\Persistence\PdoCommentRepository;
use App\Infrastructure\Persistence\PdoReportRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CreateCommentAction
{
    public function __construct(
        private PdoCommentRepository $comments,
        private PdoReportRepository  $reports
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        array                  $args
    ): ResponseInterface {
        $reportid = (int)($args['id'] ?? 0);
        $jwt      = $request->getAttribute('jwt');
        $uuid     = (int)$jwt->sub;

        if ($reportid <= 0) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Invalid ID',
                'message' => 'Report ID must be a positive integer.',
            ]);
        }

        // Verify report exists
        $report = $this->reports->findById($reportid);
        if (!$report) {
            return $this->json($response, 404, [
                'success' => false,
                'error'   => 'Not Found',
                'message' => "Report #{$reportid} not found.",
            ]);
        }

        $body    = (array)$request->getParsedBody();
        $comment = trim((string)($body['comment'] ?? ''));

        if (empty($comment)) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Validation failed.',
                'message' => 'Comment text is required.',
            ]);
        }

        if (strlen($comment) > 2000) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Validation failed.',
                'message' => 'Comment must not exceed 2000 characters.',
            ]);
        }

        try {
            $commentid = $this->comments->create([
                'reportid' => $reportid,
                'uuid'     => $uuid,
                'comment'  => $comment,
            ]);
        } catch (\Throwable $e) {
            return $this->json($response, 500, [
                'success' => false,
                'error'   => 'Database error.',
                'message' => 'Could not save comment.',
            ]);
        }

        $saved = $this->comments->findById($commentid);

        return $this->json($response, 201, [
            'success' => true,
            'data'    => ['comment' => $saved],
            'message' => 'Comment posted successfully.',
        ]);
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
