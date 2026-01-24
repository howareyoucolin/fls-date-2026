<?php
declare(strict_types=1);

$pdo = require __DIR__ . '/db.php';

$sqlCreate = <<<SQL
CREATE TABLE IF NOT EXISTS `cz_contacts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wechat` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = unread, 1 = read',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($sqlCreate);
    echo "✅ Table `cz_contacts` ready.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "❌ Migration failed: {$e->getMessage()}\n");
    exit(1);
}
