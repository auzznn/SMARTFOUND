<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\UserRepositoryInterface;
use PDO;

class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.rolename
               FROM users u
               JOIN roles r ON r.roleid = u.roleid
              WHERE u.username = :username
              LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $uuid): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.rolename
               FROM users u
               JOIN roles r ON r.roleid = u.roleid
              WHERE u.uuid = :uuid
              LIMIT 1'
        );
        $stmt->execute([':uuid' => $uuid]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByGoogleId(string $googleId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.rolename
               FROM users u
               JOIN roles r ON r.roleid = u.roleid
              WHERE u.google_id = :google_id
              LIMIT 1'
        );
        $stmt->execute([':google_id' => $googleId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.rolename
               FROM users u
               JOIN roles r ON r.roleid = u.roleid
              WHERE u.email = :email
              LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create a new user.
     * Allowed keys: roleid, username, fullname, contactno, password_hash, google_id, email
     *
     * @return int New user UUID
     */
    public function create(array $data): int
    {
        // Allow-list columns
        $allowed = ['roleid', 'username', 'fullname', 'contactno', 'password_hash', 'google_id', 'email'];
        $data    = array_intersect_key($data, array_flip($allowed));

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));

        $stmt = $this->pdo->prepare(
            "INSERT INTO users ({$columns}) VALUES ({$placeholders}) RETURNING uuid"
        );
        $stmt->execute($data);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Update an existing user.
     * Allowed keys: fullname, contactno, password_hash, google_id, email, roleid
     */
    public function update(int $uuid, array $data): bool
    {
        $allowed = ['fullname', 'contactno', 'password_hash', 'google_id', 'email', 'roleid'];
        $data    = array_intersect_key($data, array_flip($allowed));

        if (empty($data)) {
            return false;
        }

        $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        $data[':uuid'] = $uuid;

        $stmt = $this->pdo->prepare("UPDATE users SET {$sets} WHERE uuid = :uuid");
        $stmt->execute($data);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $uuid): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE uuid = :uuid');
        $stmt->execute([':uuid' => $uuid]);
        return $stmt->rowCount() > 0;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT u.uuid, u.roleid, r.rolename, u.username, u.fullname, u.contactno, u.email, u.created_at
               FROM users u
               JOIN roles r ON r.roleid = u.roleid
              ORDER BY u.uuid ASC'
        );
        return $stmt->fetchAll();
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetch();
    }
}
