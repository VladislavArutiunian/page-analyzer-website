<?php

namespace Postgre;

use Exception;
use PDO;

final class Connection
{
    private static Connection|null $connect = null;

    /**
     * @return PDO
     * @throws Exception
     */
    public static function connect(): PDO
    {
        if (!$_ENV) {
            throw new Exception('Environment variables don\'t isset');
        }
        $host = !$_ENV['HOST'] ? '0.0.0.0' : $_ENV['HOST'];
        $port = !$_ENV['PORT'] ? '5432' : $_ENV['PORT'];

        $conUrl = sprintf(
            "pgsql:host=$host;port=$port;dbname=%s;user=%s;password=%s",
            $_ENV['DATABASE'],
            $_ENV['USER'],
            $_ENV['PASSWORD']
        );
        $pdo = new PDO($conUrl);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public static function get(): Connection
    {
        if (null === Connection::$connect) {
            Connection::$connect = new self();
        }

        return Connection::$connect;
    }

    protected function __construct()
    {
    }
}
