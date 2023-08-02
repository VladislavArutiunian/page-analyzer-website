<?php

namespace Database;

use Database\Interfaces\SeoCheckInterface;
use Database\Interfaces\SiteUrlInterface;
use PDO;

class DbServiceFactory
{
    private string $driver;

    public function __construct(private PDO $connection)
    {
        $this->driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function buildSiteUrl(): SiteUrlInterface
    {
        $mapping = [
            'pgsql' => PostgreSQL\SiteUrl::class,
            'sqlite' => SQLite\SiteUrl::class,
        ];

        return new $mapping[$this->driver]($this->connection);
    }

    public function buildSeoCheck(): SeoCheckInterface
    {
        $mapping = [
            'pgsql' => PostgreSQL\SEOCheck::class,
            'sqlite' => SQLite\SEOCheck::class,
        ];

        return new $mapping[$this->driver]($this->connection);
    }

    public function buildTableCreator(): mixed
    {
        $mapping = [
            'pgsql' => PostgreSQL\CreateTable::class,
            'sqlite' => SQLite\CreateTable::class,
        ];

        return new $mapping[$this->driver]($this->connection);
    }
}