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
}
