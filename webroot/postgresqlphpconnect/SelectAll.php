<?php

namespace Postgre;

class SelectAll
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function selectAll()
    {
        $sql = "SELECT * FROM urls ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        // возврат полученного значения id
        return $stmt->fetchAll();
    }
}
