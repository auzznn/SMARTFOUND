<?php

declare(strict_types=1);

namespace App\Domain\Models;

class Report
{
    public int     $reportid;
    public int     $uuid;
    public int     $categoryid;
    public int     $locationid;
    public int     $itemid;
    public string  $reporttype;
    public string  $date;
    public string  $status;
    public ?string $itemname;
    public ?string $png;
    public ?string $categoryName;
    public ?string $locationName;
    public ?string $username;
    public ?string $contactno;

    public static function fromRow(array $row): self
    {
        $r               = new self();
        $r->reportid     = (int)$row['reportid'];
        $r->uuid         = (int)$row['uuid'];
        $r->categoryid   = (int)$row['categoryid'];
        $r->locationid   = (int)$row['locationid'];
        $r->itemid       = (int)$row['itemid'];
        $r->reporttype   = $row['reporttype'];
        $r->date         = $row['date'];
        $r->status       = $row['status'];
        $r->itemname     = $row['itemname']       ?? null;
        $r->png          = $row['png']            ?? null;
        $r->categoryName = $row['category_name']  ?? null;
        $r->locationName = $row['location_name']  ?? null;
        $r->username     = $row['username']       ?? null;
        $r->contactno    = $row['contactno']      ?? null;
        return $r;
    }
}
