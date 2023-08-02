<?php

namespace Database;

use Exception;
use PDO;

final class Connection
{
    private const DEFAULTS = [
        'HOST' => 'localhost',
        'PORT' => '5432',
        'DB_MODE' => 'test',
        'SQLITE_FILE_PATH' => __DIR__ . '/sqlitephpconnect/db.sqlite3',
    ];

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

        $dbMode = $_ENV['DB_MODE'] ?? self::DEFAULTS['DB_MODE'];
        switch ($dbMode) {
            case 'test':
                $dbUrl = $_ENV['DB_URL'] ?? self::DEFAULTS['SQLITE_FILE_PATH'];
                $conUrl = "sqlite:$dbUrl";
                break;
            default:
                $host = !$_ENV['HOST'] ? self::DEFAULTS['HOST'] : $_ENV['HOST'];
                $port = !$_ENV['PORT'] ? self::DEFAULTS['PORT'] : $_ENV['PORT'];
                $conUrl = sprintf(
                    "pgsql:host=$host;port=$port;dbname=%s;user=%s;password=%s",
                    $_ENV['DATABASE'],
                    $_ENV['USER'],
                    $_ENV['PASSWORD']
                );
                break;
        }

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
