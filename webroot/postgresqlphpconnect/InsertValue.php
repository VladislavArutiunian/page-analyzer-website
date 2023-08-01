<?php

namespace Postgre;

use Carbon\CarbonTimeZone;
use PDO;
use Carbon\Carbon;

class InsertValue
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function insertValue(string $table, string $url)
    {
        $sql = "INSERT INTO urls (name, created_at) VALUES (:name, :created_at)";
        $stmt = $this->pdo->prepare($sql);

        $tz = new CarbonTimeZone('Europe/Moscow'); // instance way
        $carbon = Carbon::now($tz);


        $stmt->bindValue(':name', $url);
        $stmt->bindValue(':created_at', $carbon->format('Y-m-d H:i:s.u'));

        $stmt->execute();

        // возврат полученного значения id
        return $this->pdo->lastInsertId();
    }

    public function insertCheck(
        string $url_id,
        string $status_code,
        string $h1 = null,
        string $title = null,
        string $description = null
    ) {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
                VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)";
        $stmt = $this->pdo->prepare($sql);

        $tz = new CarbonTimeZone(); // instance way
        $carbon = Carbon::now($tz);

        $stmt->bindValue(':url_id', $url_id);
        $stmt->bindValue(':status_code', $status_code);
        $stmt->bindValue(':h1', $h1);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':created_at', $carbon->format('Y-m-d H:i:s.u'));

        $stmt->execute();

        // возврат полученного значения id
        return $this->pdo->lastInsertId();
    }
}
