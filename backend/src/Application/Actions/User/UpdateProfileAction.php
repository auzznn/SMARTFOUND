<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateProfileAction
{
    public function __construct(private PdoUserRepository $users)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $jwt  = $request->getAttribute('jwt');
        $uuid = (int)$jwt->sub;

        $body      = (array)$request->getParsedBody();

        // Allow-list — only these two fields can be updated via this endpoint
        $updateData = [];

        if (array_key_exists('fullname', $body)) {
            $fullname = trim((string)$body['fullname']);
            if (strlen($fullname) > 150) {
                return $this->json($response, 422, [
                    'success' => false,
                    'error'   => 'Validation failed.',
                    'message' => 'Full name must not exceed 150 characters.',
                ]);
            }
            $updateData['fullname'] = $fullname ?: null;
        }

        if (array_key_exists('contactno', $body)) {
            $contactno = trim((string)$body['contactno']);
            if (!empty($contactno) && !preg_match('/^[+\d\s\-()]{6,20}$/', $contactno)) {
                return $this->json($response, 422, [
                    'success' => false,
                    'error'   => 'Validation failed.',
                    'message' => 'Contact number format is invalid.',
                ]);
            }
            $updateData['contactno'] = $contactno ?: null;
        }

        if (empty($updateData)) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Nothing to update.',
                'message' => 'Provide at least one field: fullname or contactno.',
            ]);
        }

        try {
            $this->users->update($uuid, $updateData);
        } catch (\Throwable $e) {
            return $this->json($response, 500, [
                'success' => false,
                'error'   => 'Database error.',
                'message' => 'Could not update profile.',
            ]);
        }

        $user = $this->users->findById($uuid);
        unset($user['password_hash'], $user['google_id']);

        foreach (['username', 'fullname', 'contactno', 'email'] as $field) {
            if (isset($user[$field]) && is_string($user[$field])) {
                $user[$field] = htmlspecialchars($user[$field], ENT_QUOTES, 'UTF-8');
            }
        }

        return $this->json($response, 200, [
            'success' => true,
            'data'    => ['user' => $user],
            'message' => 'Profile updated successfully.',
        ]);
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
