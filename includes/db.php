<?php
/**
 * PDO database connection.
 *
 * Exposes a single shared $pdo instance and a db() accessor. Prepared
 * statements are used everywhere; emulation is disabled so parameter types
 * are handled natively.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
    } catch (PDOException $e) {
        // Never leak credentials or the raw DSN to the visitor.
        error_log('[db] connection failed: ' . $e->getMessage());
        http_response_code(503);
        exit('Database connection failed. Please try again later.');
    }

    return $pdo;
}

// Shared instance used across the app.
$pdo = db();
