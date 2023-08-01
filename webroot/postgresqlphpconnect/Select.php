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
//var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
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

    public static function selectAllChecks(PDO $connection, string $url_id)
    {
        $sql = "SELECT id, url_id, status_code, h1, title, description, created_at
                FROM url_checks
                WHERE url_id = :url_id
                ORDER BY id DESC";
        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':url_id', $url_id);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function selectLastCheck(PDO $connection, ?int $urlId)
    {
        $sql = "SELECT id, url_id, status_code, created_at FROM url_checks 
                                           WHERE url_id = :url_id 
                                           ORDER BY id DESC LIMIT 1";
        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':url_id', $urlId);

        $stmt->execute();

        return self::normalizeFetchAll($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getAllUrls(PDO $connection)
    {
        $urls = Select::selectAllUrls($connection);
        $withLastCheck = [];
        foreach ($urls as $url) {
            $lastCheck = self::selectLastCheck($connection, self::getId($url));
            $url['last_check_created_at'] = $lastCheck['created_at'] ?? '';
            $url['last_check_response_code'] = $lastCheck['status_code'] ?? '';
            $withLastCheck[] = $url;
        }
        return $withLastCheck;
    }

    public static function normalizeFetchAll($fetchAll): array
    {
        if ($fetchAll === []) {
            return [];
        }
        [$row] = $fetchAll;
        return $row;
    }

    public static function getId(array $result): int
    {
        return $result['id'];
    }

    public static function getName(array $result): string
    {
        return $result['name'];
    }
}
