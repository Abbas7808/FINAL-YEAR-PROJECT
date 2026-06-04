<?php
require_once 'config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS `blogs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `content` text NOT NULL,
        `excerpt` text DEFAULT NULL,
        `category` varchar(100) DEFAULT NULL,
        `image` varchar(255) DEFAULT NULL,
        `author_id` int(11) DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->exec($sql);
    echo "<h3>Success!</h3>";
    echo "<p>The 'blogs' table has been created successfully.</p>";
    echo "<p><a href='?route=admin/dashboard'>Go to Dashboard</a></p>";
    echo "<p><em>Note: You can delete this file (fix_blogs.php) now.</em></p>";

} catch (Exception $e) {
    echo "<h3>Error!</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
