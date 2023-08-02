<?php

namespace Database;

use PDO;

class DbServiceFactory
{
    private string $driver;

    public function __construct(private PDO $connection)
    {
        $this->driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
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
