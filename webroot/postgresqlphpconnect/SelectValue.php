<?php

namespace Postgre;

class SelectValue
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function selectValue(string $url)
    {
        $sql = "SELECT * FROM urls WHERE name = :name";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':name', $url);
//        ['name' => $url]
        $stmt->execute();

        // возврат полученного значения id
        return $stmt->rowCount();
    }
}