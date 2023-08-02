<?php

namespace Database\SQLite;

use PDO;

class CreateTable
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTables()
    {
        $sqls = [
            'urls' => "CREATE TABLE IF NOT EXISTS urls (
                        id  INTEGER PRIMARY KEY,
                        name    varchar(255),
                        created_at  timestamp
            );",
            'url_checks' => "CREATE TABLE IF NOT EXISTS url_checks (
                        id  INTEGER PRIMARY KEY,
                        url_id  INT,
                        status_code varchar(255),
                        h1  varchar(255),
                        title   varchar(255),
                        description TEXT,
                        created_at  timestamp
            );",
        ];
        foreach ($sqls as $sql) {
            $this->pdo->exec($sql);
        }
        return $this;
    }
}
