<?php

declare(strict_types=1);

namespace App\Domain\Models;

class Comment
{
    public int     $commentid;
    public int     $reportid;
    public int     $uuid;
    public string  $comment;
    public string  $createdat;
    public ?string $username;

    public static function fromRow(array $row): self
    {
        $c            = new self();
        $c->commentid = (int)$row['commentid'];
        $c->reportid  = (int)$row['reportid'];
        $c->uuid      = (int)$row['uuid'];
        $c->comment   = $row['comment'];
        $c->createdat = $row['createdat'];
        $c->username  = $row['username'] ?? null;
        return $c;
    }

    public function toArray(): array
    {
        return [
            'commentid' => $this->commentid,
            'reportid'  => $this->reportid,
            'uuid'      => $this->uuid,
            'username'  => $this->username !== null ? htmlspecialchars($this->username, ENT_QUOTES, 'UTF-8') : null,
            'comment'   => htmlspecialchars($this->comment, ENT_QUOTES, 'UTF-8'),
            'createdat' => $this->createdat,
        ];
    }
}
