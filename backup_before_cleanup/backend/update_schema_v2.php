<?php
require_once 'config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Create categories table
    $sql1 = "CREATE TABLE IF NOT EXISTS `blog_categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `slug` varchar(100) NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql1);

    // 2. Add some default categories
    $categories = [
        ['name' => 'Health Tips', 'slug' => 'health-tips'],
        ['name' => 'Medical News', 'slug' => 'medical-news'],
        ['name' => 'Lifestyle', 'slug' => 'lifestyle']
    ];
    $stmt = $db->prepare("INSERT IGNORE INTO blog_categories (name, slug) VALUES (:name, :slug)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }

    // 3. Update blogs table structure
    // Check if category_id already exists to avoid errors
    $checkColumn = $db->query("SHOW COLUMNS FROM `blogs` LIKE 'category_id'");
    if ($checkColumn->rowCount() == 0) {
        $db->exec("ALTER TABLE `blogs` ADD COLUMN `category_id` int(11) DEFAULT NULL AFTER `excerpt`;");
        $db->exec("ALTER TABLE `blogs` ADD CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE SET NULL;");
        
        // Remove old category column if it exists
        $checkOld = $db->query("SHOW COLUMNS FROM `blogs` LIKE 'category'");
        if ($checkOld->rowCount() > 0) {
            $db->exec("ALTER TABLE `blogs` DROP COLUMN `category`;");
        }
    }

    echo "<h3>Success!</h3>";
    echo "<p>Blog categories table created and blogs table updated.</p>";
    echo "<p><a href='index.php?route=admin/dashboard'>Go to Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h3>Error!</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
