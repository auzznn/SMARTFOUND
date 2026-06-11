<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface ReportRepositoryInterface
{
    public function findAll(array $filters, int $page, int $limit): array;
    public function findClosed(int $page, int $limit): array;
    public function findByUser(int $uuid): array;
    public function findById(int $reportid): ?array;
    public function create(array $data): int;
    public function updateStatus(int $reportid, string $status): bool;
    public function delete(int $reportid): bool;
    public function countAll(array $filters): int;
    public function countClosed(): int;
}
