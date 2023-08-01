<?php

namespace Postgre;

use Exception;
use PDO;
use Dotenv\Dotenv;

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
        $conUrl = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $_ENV['HOST'],
            $_ENV['PORT'],
            $_ENV['DATABASE'],
            $_ENV['USER'],
            $_ENV['PASSWORD']
        );
        $pdo = new PDO($conUrl);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
