<?php

namespace Database;

use PDO;
use PDOException;

/**
 * Database connection class for the Healthcare Management System
 * Updated to use PostgreSQL (Replit built-in database)
 */
class DatabaseConnection
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $databaseUrl = getenv('DATABASE_URL');

        try {
            if ($databaseUrl) {
                $parsed = parse_url($databaseUrl);
                $host = $parsed['host'];
                $port = $parsed['port'] ?? 5432;
                $dbname = ltrim($parsed['path'], '/');
                $username = $parsed['user'];
                $password = $parsed['pass'];

                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                $this->connection = new PDO($dsn, $username, $password);
            } elseif (getenv('PGHOST')) {
                $host = getenv('PGHOST');
                $port = getenv('PGPORT') ?: 5432;
                $dbname = getenv('PGDATABASE');
                $username = getenv('PGUSER');
                $password = getenv('PGPASSWORD');

                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                $this->connection = new PDO($dsn, $username, $password);
            } else {
                throw new \Exception("No database connection environment variables found.");
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new DatabaseConnection();
        }

        return self::$instance;
    }

    public static function resetInstance()
    {
        self::$instance = null;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function exec($sql)
    {
        return $this->connection->exec($sql);
    }

    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }
}
