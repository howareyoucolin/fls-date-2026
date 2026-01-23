<?php
// db.php
declare(strict_types=1);

// Load config
$configPath = __DIR__ . '/../config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

// Prefer config.php constants → env vars → defaults
$dbHost = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: '127.0.0.1');
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = defined('DB_NAME') ? DB_NAME : (getenv('DB_DATABASE') ?: 'app');
$dbUser = defined('DB_USERNAME') ? DB_USERNAME : (getenv('DB_USERNAME') ?: 'root');
$dbPass = defined('DB_PASSWORD') ? DB_PASSWORD : (getenv('DB_PASSWORD') ?: '');

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "❌ DB connection failed: {$e->getMessage()}\n");
    exit(1);
}

// Export $pdo
return $pdo;
