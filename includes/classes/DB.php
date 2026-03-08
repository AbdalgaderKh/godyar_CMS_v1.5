<?php
namespace Godyar;

use PDO;
use PDOException;

final class DB
{
    private static ?self $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        // Ensure .env loaded
        if (!function_exists('env')) {
            @require_once __DIR__ . '/../env.php';
        }

        $host = (string)(env('DB_HOST') ?: 'localhost');
        $name = (string)(env('DB_NAME') ?: '');
        $user = (string)(env('DB_USER') ?: '');
        $pass = (string)(env('DB_PASS') ?: '');
        $charset = (string)(env('DB_CHARSET') ?: 'utf8mb4');

        if ($name === '' || $user === '') {
            error_log('[DB connect failed] Missing DB_NAME or DB_USER (check .env)');
            $this->connection = null;
            return;
        }

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";

        try {
            $this->connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('[DB connect failed] ' . $e->getMessage());
            $this->connection = null;
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Strict PDO accessor (throws if unavailable). */
    public static function pdo(): PDO
    {
        $pdo = self::pdoOrNull();
        if (!$pdo instanceof PDO) {
            throw new \RuntimeException('فشل الاتصال بقاعدة البيانات.');
        }
        return $pdo;
    }

    /** Safe PDO accessor (returns null). */
    public static function pdoOrNull(): ?PDO
    {
        return self::getInstance()->connection;
    }

    /**
     * Quote a SQL identifier (table/column) safely.
     * Accepts only A-Z a-z 0-9 and underscore; otherwise throws.
     */
    public static function quoteIdent(string $ident): string
    {
        $ident = trim($ident);
        if ($ident === '' || !preg_match('/^[A-Za-z0-9_]+$/', $ident)) {
            throw new \InvalidArgumentException('Invalid identifier');
        }
        // MySQL-style quoting
        return '`' . str_replace('`', '``', $ident) . '`';
    }

    /**
     * Convenience: run a prepared statement and fetch all rows.
     *
     * @param array<int|string, mixed> $params
     * @return array<int, array<string,mixed>>
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $pdo = self::pdo();
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Convenience: run a prepared statement and fetch first row.
     *
     * @param array<int|string, mixed> $params
     * @return array<string,mixed>|null
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $pdo = self::pdo();
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

}
