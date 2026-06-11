<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;

class PdoCommentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByReport(int $reportid): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.commentid, c.reportid, c.uuid, c.comment, c.createdat, u.username
               FROM comments c
               JOIN users u ON u.uuid = c.uuid
              WHERE c.reportid = :reportid
              ORDER BY c.createdat ASC'
        );
        $stmt->execute([':reportid' => $reportid]);
        $rows = $stmt->fetchAll();

        return array_map(function (array $row): array {
            $row['username'] = htmlspecialchars($row['username'] ?? '', ENT_QUOTES, 'UTF-8');
            $row['comment']  = htmlspecialchars($row['comment'],        ENT_QUOTES, 'UTF-8');
            return $row;
        }, $rows);
    }

    public function create(array $data): int
    {
        $allowed = ['reportid', 'uuid', 'comment'];
        $data    = array_intersect_key($data, array_flip($allowed));

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO comments ({$columns}) VALUES ({$placeholders}) RETURNING commentid");
        $stmt->execute($data);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $commentid): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, u.username
               FROM comments c
               JOIN users u ON u.uuid = c.uuid
              WHERE c.commentid = :commentid
              LIMIT 1'
        );
        $stmt->execute([':commentid' => $commentid]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
