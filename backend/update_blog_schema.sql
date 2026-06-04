CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Update blogs table to use category_id
ALTER TABLE `blogs` DROP COLUMN `category`;
ALTER TABLE `blogs`
ADD COLUMN `category_id` int(11) DEFAULT NULL
AFTER `excerpt`;
ALTER TABLE `blogs`
ADD CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE
SET NULL;