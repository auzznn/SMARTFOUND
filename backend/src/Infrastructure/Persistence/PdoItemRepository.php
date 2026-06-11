<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;

class PdoItemRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $allowed = ['uuid', 'categoryid', 'locationid', 'itemname', 'totalitems', 'png'];
        $data    = array_intersect_key($data, array_flip($allowed));

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO items ({$columns}) VALUES ({$placeholders}) RETURNING itemid");
        $stmt->execute($data);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $itemid): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM items WHERE itemid = :itemid LIMIT 1');
        $stmt->execute([':itemid' => $itemid]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $itemid): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM items WHERE itemid = :itemid');
        $stmt->execute([':itemid' => $itemid]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $itemid, array $data): bool
    {
        $allowed = ['itemname', 'png', 'totalitems', 'categoryid', 'locationid'];
        $data    = array_intersect_key($data, array_flip($allowed));

        if (empty($data)) {
            return false;
        }

        $sets       = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        $data[':itemid'] = $itemid;

        $stmt = $this->pdo->prepare("UPDATE items SET {$sets} WHERE itemid = :itemid");
        $stmt->execute($data);
        return $stmt->rowCount() > 0;
    }
}
