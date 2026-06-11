<?php

declare(strict_types=1);

namespace App\Domain\Models;

class Item
{
    public int     $itemid;
    public int     $uuid;
    public int     $categoryid;
    public int     $locationid;
    public string  $itemname;
    public int     $totalitems;
    public ?string $png;
    public string  $datetime;

    public static function fromRow(array $row): self
    {
        $i             = new self();
        $i->itemid     = (int)$row['itemid'];
        $i->uuid       = (int)$row['uuid'];
        $i->categoryid = (int)$row['categoryid'];
        $i->locationid = (int)$row['locationid'];
        $i->itemname   = $row['itemname'];
        $i->totalitems = (int)($row['totalitems'] ?? 1);
        $i->png        = $row['png'] ?? null;
        $i->datetime   = $row['datetime'] ?? '';
        return $i;
    }
}
