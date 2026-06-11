<?php

declare(strict_types=1);

namespace App\Application\Actions\Meta;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListLocationsAction
{
    public function __construct(private PDO $pdo)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $stmt = $this->pdo->query(
            'SELECT locationid, location_name, description
               FROM locations
              ORDER BY locationid ASC'
        );
        $locations = $stmt->fetchAll();

        // Sanitize output
        $locations = array_map(function (array $loc): array {
            foreach (['location_name', 'description'] as $f) {
                if (isset($loc[$f]) && is_string($loc[$f])) {
                    $loc[$f] = htmlspecialchars($loc[$f], ENT_QUOTES, 'UTF-8');
                }
            }
            return $loc;
        }, $locations);

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => ['locations' => $locations],
            'message' => 'Locations retrieved successfully.',
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
