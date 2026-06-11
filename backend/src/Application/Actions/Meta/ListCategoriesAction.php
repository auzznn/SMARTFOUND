<?php

declare(strict_types=1);

namespace App\Application\Actions\Meta;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListCategoriesAction
{
    public function __construct(private PDO $pdo)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $stmt = $this->pdo->query(
            'SELECT categoryid, category_name, category_type, description
               FROM categories
              ORDER BY categoryid ASC'
        );
        $categories = $stmt->fetchAll();

        // Sanitize output
        $categories = array_map(function (array $cat): array {
            foreach (['category_name', 'category_type', 'description'] as $f) {
                if (isset($cat[$f]) && is_string($cat[$f])) {
                    $cat[$f] = htmlspecialchars($cat[$f], ENT_QUOTES, 'UTF-8');
                }
            }
            return $cat;
        }, $categories);

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'success' => true,
            'data'    => ['categories' => $categories],
            'message' => 'Categories retrieved successfully.',
        ], JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
