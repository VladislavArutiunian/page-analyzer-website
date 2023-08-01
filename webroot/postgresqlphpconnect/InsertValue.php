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
        $stmt->bindValue(':created_at', $carbon->format('Y-m-d H:i:s'));

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    public function insertCheck(string $urlId, array $checkParams) {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
                VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)";
        $stmt = $this->pdo->prepare($sql);

        $checkParams['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $checkParams['url_id'] = $urlId;
        foreach ($checkParams as $key => $checkParam) {
            $stmt->bindValue(":$key", $checkParam);
        }
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }
}
