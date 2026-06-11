<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\ReportRepositoryInterface;
use PDO;

class PdoReportRepository implements ReportRepositoryInterface
{
    private string $baseSelect = <<<SQL
        SELECT
            r.reportid,
            r.uuid,
            r.categoryid,
            r.locationid,
            r.itemid,
            r.reporttype,
            r.date,
            r.status,
            i.itemname,
            i.png,
            c.category_name,
            l.location_name,
            u.username,
            u.contactno
        FROM reports r
        JOIN items      i ON i.itemid     = r.itemid
        JOIN categories c ON c.categoryid = r.categoryid
        JOIN locations  l ON l.locationid = r.locationid
        JOIN users      u ON u.uuid       = r.uuid
    SQL;

    public function __construct(
        private PDO    $pdo,
        private string $appUrl = 'http://localhost:8080'
    ) {
    }

    public function findAll(array $filters, int $page, int $limit): array
    {
        [$where, $params] = $this->buildOpenFilters($filters);

        $offset = ($page - 1) * $limit;
        $sql    = "{$this->baseSelect} WHERE r.status = 'open'{$where} ORDER BY r.reportid DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->hydrateRows($stmt->fetchAll());
    }

    public function countAll(array $filters): int
    {
        [$where, $params] = $this->buildOpenFilters($filters);
        $sql  = "SELECT COUNT(*) FROM reports r WHERE r.status = 'open'{$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findClosed(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        $sql    = "{$this->baseSelect} WHERE r.status = 'closed' ORDER BY r.reportid DESC LIMIT :limit OFFSET :offset";
        $stmt   = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->hydrateRows($stmt->fetchAll());
    }

    public function countClosed(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'closed'");
        return (int)$stmt->fetchColumn();
    }

    public function findByUser(int $uuid): array
    {
        $sql  = "{$this->baseSelect} WHERE r.uuid = :uuid ORDER BY r.reportid DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uuid' => $uuid]);
        return $this->hydrateRows($stmt->fetchAll());
    }

    public function findById(int $reportid): ?array
    {
        $sql  = "{$this->baseSelect} WHERE r.reportid = :reportid LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':reportid' => $reportid]);
        $row  = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return $this->hydrateRow($row);
    }

    public function create(array $data): int
    {
        $allowed = ['uuid', 'categoryid', 'locationid', 'itemid', 'reporttype', 'date', 'status'];
        $data    = array_intersect_key($data, array_flip($allowed));

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO reports ({$columns}) VALUES ({$placeholders}) RETURNING reportid");
        $stmt->execute($data);
        return (int)$stmt->fetchColumn();
    }

    public function updateStatus(int $reportid, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE reports SET status = :status WHERE reportid = :reportid"
        );
        $stmt->execute([':status' => $status, ':reportid' => $reportid]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $reportid): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM reports WHERE reportid = :reportid');
        $stmt->execute([':reportid' => $reportid]);
        return $stmt->rowCount() > 0;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function buildOpenFilters(array $filters): array
    {
        $where  = '';
        $params = [];

        if (!empty($filters['type']) && in_array($filters['type'], ['lost', 'found'], true)) {
            $where             .= ' AND r.reporttype = :reporttype';
            $params[':reporttype'] = $filters['type'];
        }
        if (!empty($filters['categoryid']) && is_numeric($filters['categoryid'])) {
            $where               .= ' AND r.categoryid = :categoryid';
            $params[':categoryid'] = (int)$filters['categoryid'];
        }
        if (!empty($filters['locationid']) && is_numeric($filters['locationid'])) {
            $where               .= ' AND r.locationid = :locationid';
            $params[':locationid'] = (int)$filters['locationid'];
        }

        return [$where, $params];
    }

    private function hydrateRows(array $rows): array
    {
        return array_map(fn($r) => $this->hydrateRow($r), $rows);
    }

    private function hydrateRow(array $row): array
    {
        // Build full image URL
        if (!empty($row['png'])) {
            $row['png'] = rtrim($this->appUrl, '/') . '/' . ltrim($row['png'], '/');
        }

        // htmlspecialchars on string fields
        $stringFields = ['itemname', 'category_name', 'location_name', 'username', 'contactno'];
        foreach ($stringFields as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $row[$field] = htmlspecialchars($row[$field], ENT_QUOTES, 'UTF-8');
            }
        }

        return $row;
    }
}
