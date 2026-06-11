<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteUserAction
{
    public function __construct(private PdoUserRepository $users)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response,
        array                  $args
    ): ResponseInterface {
        $targetUuid = (int)($args['id'] ?? 0);
        $jwt        = $request->getAttribute('jwt');
        $callerUuid = (int)$jwt->sub;

        if ($targetUuid <= 0) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Invalid ID',
                'message' => 'User ID must be a positive integer.',
            ]);
        }

        // Prevent admin from deleting themselves
        if ($targetUuid === $callerUuid) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Invalid operation',
                'message' => 'You cannot delete your own account via this endpoint.',
            ]);
        }

        $user = $this->users->findById($targetUuid);
        if (!$user) {
            return $this->json($response, 404, [
                'success' => false,
                'error'   => 'Not Found',
                'message' => "User #{$targetUuid} not found.",
            ]);
        }

        try {
            $this->users->delete($targetUuid);
        } catch (\Throwable $e) {
            return $this->json($response, 500, [
                'success' => false,
                'error'   => 'Database error.',
                'message' => 'Could not delete user. They may have associated records.',
            ]);
        }

        return $this->json($response, 200, [
            'success' => true,
            'data'    => null,
            'message' => "User #{$targetUuid} deleted successfully.",
        ]);
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
