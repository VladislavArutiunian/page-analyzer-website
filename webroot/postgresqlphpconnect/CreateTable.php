<?php

namespace Postgre;

use PDO;

class CreateTable
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }
    
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS test2 (name varchar(255) id bigint)";
        $this->pdo->exec($sql);
        return $this;
    }
}
