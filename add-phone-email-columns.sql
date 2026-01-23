-- Add phone column after wechat
ALTER TABLE `cz_members` 
ADD COLUMN `phone` VARCHAR(255) NULL DEFAULT NULL 
COLLATE utf8mb4_unicode_ci 
AFTER `wechat`;

-- Add email column after phone
ALTER TABLE `cz_members` 
ADD COLUMN `email` VARCHAR(255) NULL DEFAULT NULL 
COLLATE utf8mb4_unicode_ci 
AFTER `phone`;
