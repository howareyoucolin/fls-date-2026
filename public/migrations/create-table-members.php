<?php
declare(strict_types=1);

$pdo = require __DIR__ . '/db.php';

$sqlCreate = <<<SQL
CREATE TABLE IF NOT EXISTS `cz_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `birthday` date DEFAULT NULL COMMENT 'Date of birth in YYYY-MM-DD format',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Member name/title',
  `gender` enum('m','f') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wechat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Combined about_me and preference',
  `profile_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    // Create table if needed
    $pdo->exec($sqlCreate);

    // ---- Ensure profile_thumbnail exists AFTER profile_image ----
    $thumbExists = $pdo->query("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'cz_members'
          AND COLUMN_NAME = 'profile_thumbnail'
    ")->fetchColumn();

    if (!$thumbExists) {
        $pdo->exec("
            ALTER TABLE `cz_members`
            ADD COLUMN `profile_thumbnail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL
            COMMENT 'Thumbnail version of profile image'
            AFTER `profile_image`;
        ");
    }

    // ---- Ensure is_approved exists AFTER profile_thumbnail ----
    $approvedExists = $pdo->query("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'cz_members'
          AND COLUMN_NAME = 'is_approved'
    ")->fetchColumn();

    if (!$approvedExists) {
        $pdo->exec("
            ALTER TABLE `cz_members`
            ADD COLUMN `is_approved` TINYINT(1) NOT NULL DEFAULT 0
            COMMENT '0 = pending, 1 = approved'
            AFTER `profile_thumbnail`;
        ");
    }

    echo "✅ Table `cz_members` ready (profile_thumbnail + is_approved ensured in correct order).\n";
} catch (Throwable $e) {
    fwrite(STDERR, "❌ Migration failed: {$e->getMessage()}\n");
    exit(1);
}
