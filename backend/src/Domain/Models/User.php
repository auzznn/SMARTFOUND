<?php

declare(strict_types=1);

namespace App\Domain\Models;

class User
{
    public int     $uuid;
    public int     $roleid;
    public string  $username;
    public ?string $fullname;
    public ?string $contactno;
    public ?string $passwordHash;
    public ?string $googleId;
    public ?string $email;
    public string  $createdAt;
    public ?string $rolename;

    public static function fromRow(array $row): self
    {
        $user               = new self();
        $user->uuid         = (int)$row['uuid'];
        $user->roleid       = (int)$row['roleid'];
        $user->username     = $row['username'];
        $user->fullname     = $row['fullname']      ?? null;
        $user->contactno    = $row['contactno']     ?? null;
        $user->passwordHash = $row['password_hash'] ?? null;
        $user->googleId     = $row['google_id']     ?? null;
        $user->email        = $row['email']         ?? null;
        $user->createdAt    = $row['created_at']    ?? '';
        $user->rolename     = $row['rolename']      ?? null;
        return $user;
    }

    public function toPublicArray(): array
    {
        return [
            'uuid'       => $this->uuid,
            'roleid'     => $this->roleid,
            'rolename'   => $this->rolename,
            'username'   => htmlspecialchars($this->username, ENT_QUOTES, 'UTF-8'),
            'fullname'   => $this->fullname !== null ? htmlspecialchars($this->fullname, ENT_QUOTES, 'UTF-8') : null,
            'contactno'  => $this->contactno !== null ? htmlspecialchars($this->contactno, ENT_QUOTES, 'UTF-8') : null,
            'email'      => $this->email !== null ? htmlspecialchars($this->email, ENT_QUOTES, 'UTF-8') : null,
            'created_at' => $this->createdAt,
        ];
    }
}
