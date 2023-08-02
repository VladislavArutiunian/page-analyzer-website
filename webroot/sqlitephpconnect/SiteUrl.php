<?php

namespace Database\SQLite;

use Carbon\Carbon;
use Database\Helpers;
use Database\Interfaces\SiteUrlInterface;
use PDO;

class SiteUrl implements SiteUrlInterface
{
    public function __construct(private ?PDO $connection)
    {
    }

    public function getAll(): array
    {
        $urls = $this->selectAll($this->connection);
        $withLastCheck = [];
        foreach ($urls as $url) {
            $lastCheck = (new SEOCheck($this->connection))->selectLast(Helpers::getId($url));
            $url['last_check_created_at'] = $lastCheck['created_at'] ?? '';
            $url['last_check_response_code'] = $lastCheck['status_code'] ?? '';
            $withLastCheck[] = $url;
        }
        return $withLastCheck;
    }

    private function selectAll(PDO $connection): array
    {
        $sql = "SELECT * FROM urls ORDER BY created_at DESC";
        $stmt = $connection->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectByName(string $url): array
    {
        $sql = "SELECT * FROM urls WHERE name = :name";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':name', $url);

        $stmt->execute();

        return Helpers::normalizeFetchAll($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function selectById(string $id): array
    {
        $sql = "SELECT id, urls.name, created_at FROM urls WHERE id = :id";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        return Helpers::normalizeFetchAll($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function insertValue(string $url)
    {
        $sql = "INSERT INTO urls (name, created_at) VALUES (:name, :created_at)";
        $stmt = $this->connection->prepare($sql);

        $carbon = Carbon::now();


        $stmt->bindValue(':name', $url);
        $stmt->bindValue(':created_at', $carbon->format('Y-m-d H:i:s'));

        $stmt->execute();

        return $this->connection->lastInsertId();
    }
}
