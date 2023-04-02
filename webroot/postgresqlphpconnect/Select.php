<?php

namespace Postgre;

use PDO;

class Select
{
    public static function selectAllUrls(PDO $connection): array
    {
        $sql = "SELECT * FROM urls ORDER BY created_at DESC";
        $stmt = $connection->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function selectUrlByName(PDO $connection, string $url)
    {
        $sql = "SELECT * FROM urls WHERE name = :name";
        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':name', $url);

        $stmt->execute();

        return self::normalizeFetchAll($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function selectUrlById(PDO $connection, string $id)
    {
        $sql = "SELECT id, urls.name, created_at FROM urls WHERE id = :id";
        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        return self::normalizeFetchAll($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function normalizeFetchAll($fetchAll): array
    {
        if ($fetchAll === []) {
            return [];
        }
        [$row] = $fetchAll;
        return $row;
        //add checking indexes (only string type ones allowed)
    }

    public static function getId(array $result): string
    {
        return $result['id'];
    }
}
