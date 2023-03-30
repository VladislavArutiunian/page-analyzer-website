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
        $params = parse_ini_file('database.ini');
        if (!$params) {
            throw new Exception('Cannot read configuration file');
        }
        $conUrl = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $params['host'],
            $params['port'],
            $params['database'],
            $params['user'],
            $params['password']
        );
        $pdo = new PDO($conUrl);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public static function get()
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
