<?php
session_start();

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('ADMIN_EMAIL', 'admin@campuscart.com');
define('ADMIN_PASSWORD', 'admin123');

function db(): ?PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $exception) {
        $pdo = null;
    }

    return $pdo;
}

function flash_message(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = [
            'text' => $message,
            'type' => $type,
        ];
        return null;
    }

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
