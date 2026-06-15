<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

final class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $driver = env('DB_DRIVER', 'sqlite');

        try {
            if ($driver === 'sqlite') {
                $dbPath = dirname(__DIR__, 2) . '/' . env('DB_PATH', 'database/epsilon.sqlite');
                // Respect :memory: and absolute paths from env
                $envPath = env('DB_PATH', 'database/epsilon.sqlite');
                if ($envPath === ':memory:') {
                    $dbPath = ':memory:';
                } elseif (str_starts_with($envPath, '/')) {
                    $dbPath = $envPath;
                } else {
                    $dbPath = dirname(__DIR__, 2) . '/' . $envPath;
                }
                if ($dbPath !== ':memory:') {
                    $dbDir = dirname($dbPath);
                    if (!is_dir($dbDir)) {
                        mkdir($dbDir, 0755, true);
                    }
                }
                self::$instance = new PDO('sqlite:' . $dbPath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$instance->exec('PRAGMA journal_mode=WAL');
                self::$instance->exec('PRAGMA foreign_keys=ON');
            } elseif ($driver === 'mysql') {
                self::$instance = new PDO(
                    sprintf(
                        'mysql:host=%s;dbname=%s;charset=utf8mb4',
                        env('DB_HOST', 'localhost'),
                        env('DB_NAME', 'epsilon')
                    ),
                    env('DB_USER', 'root'),
                    env('DB_PASS', ''),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } else {
                throw new \RuntimeException("Driver de base de données non supporté: {$driver}");
            }

            return self::$instance;
        } catch (PDOException $e) {
            throw new \RuntimeException(
                'Erreur de connexion à la base de données : ' . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $sql, array $params = []): string
    {
        self::query($sql, $params);
        return self::connect()->lastInsertId();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function migrate(string $schemaFile): void
    {
        $sql = file_get_contents($schemaFile);
        if ($sql === false) {
            throw new \RuntimeException("Impossible de lire le fichier de schéma: {$schemaFile}");
        }
        self::connect()->exec($sql);
    }

    public static function reset(): void
    {
        if (self::$instance !== null) {
            self::$instance = null;
        }
    }

    public static function disconnect(): void
    {
        self::$instance = null;
    }
}
