<?php

declare(strict_types=1);

namespace App\Application\Actions\Comment;

use App\Infrastructure\Persistence\PdoCommentRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListCommentsAction
{
    public function __construct(private PdoCommentRepository $comments)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        array                  $args
    ): ResponseInterface {
        $reportid = (int)($args['id'] ?? 0);

        if ($reportid <= 0) {
            $response = $response->withStatus(422)->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode([
                'success' => false,
                'error'   => 'Invalid ID',
                'message' => 'Report ID must be a positive integer.',
            ]));
            return $response;
        }

        $data = $this->comments->findByReport($reportid);

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => ['comments' => $data],
            'message' => 'Comments retrieved successfully.',
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
