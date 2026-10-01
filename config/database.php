<?php

$localConfigPath = __DIR__ . '/database.local.php';
$localConfig = is_file($localConfigPath) ? require $localConfigPath : [];

define('DB_HOST', getenv('DB_HOST') ?: ($localConfig['host'] ?? 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: ($localConfig['name'] ?? 'db_laundry'));
define('DB_USER', getenv('DB_USER') ?: ($localConfig['user'] ?? 'root'));
define('DB_PASS', getenv('DB_PASS') ?: ($localConfig['pass'] ?? ''));
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[DB ERROR] ' . $e->getMessage());
            die('Koneksi database gagal. Hubungi administrator.');
        }
    }

    return $pdo;
}
