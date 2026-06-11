<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?array;
    public function findById(int $uuid): ?array;
    public function findByGoogleId(string $googleId): ?array;
    public function findByEmail(string $email): ?array;
    public function create(array $data): int;
    public function update(int $uuid, array $data): bool;
    public function delete(int $uuid): bool;
    public function findAll(): array;
}
