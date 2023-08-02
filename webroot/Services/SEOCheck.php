<?php

namespace Database\Services;

use Carbon\Carbon;
use PDO;

class SEOCheck
{
    public function __construct(private PDO $connection)
    {
    }

    public function selectAll(string $url_id): bool|array
    {
        $sql = "SELECT id, url_id, status_code, h1, title, description, created_at
                FROM url_checks
                WHERE url_id = :url_id
                ORDER BY id DESC";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':url_id', $url_id);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectLast(?int $urlId): array
    {
        $sql = "SELECT id, url_id, status_code, created_at FROM url_checks 
                                           WHERE url_id = :url_id 
                                           ORDER BY id DESC LIMIT 1";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':url_id', $urlId);

        $stmt->execute();

        return Helpers::normalizeFetchAll($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function insertCheck(string $urlId, array $checkParams): bool|string
    {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
                VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)";
        $stmt = $this->connection->prepare($sql);

        $checkParams['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $checkParams['url_id'] = $urlId;
        foreach ($checkParams as $key => $checkParam) {
            $stmt->bindValue(":$key", $checkParam);
        }
        $stmt->execute();

        return $this->connection->lastInsertId();
    }
}
