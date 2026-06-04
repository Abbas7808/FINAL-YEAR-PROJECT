<?php
require_once 'config/Database.php';
require_once 'models/Blog.php';
require_once 'models/BlogCategory.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $blogModel = new Blog($db);
    $catModel = new BlogCategory($db);

    // 0. Clean old broken data for a fresh start
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE blogs;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 1. Categories Mapping
    $categories = [
        'Platform', 'Appointments', 'Mental Health', 'Blood', 'Nutrition'
    ];
    
    $catMap = [];
    foreach ($categories as $catName) {
        $stmt = $db->prepare("SELECT id FROM blog_categories WHERE name = :name");
        $stmt->execute(['name' => $catName]);
        $existingId = $stmt->fetchColumn();

        if (!$existingId) {
            $catModel->create($catName);
            $stmt->execute(['name' => $catName]);
            $existingId = $stmt->fetchColumn();
        }
        $catMap[$catName] = $existingId;
    }

    // 2. Blogs Data with Placeholders
    $blogs = [
        [
            'title' => 'Hospital & Clinic SaaS: Onboarding Made Simple',
            'content' => 'Med Nova helps facilities digitize appointment scheduling, staff workflows, and patient handling—without complicated setup.',
            'category' => 'Platform',
            'image' => 'https://placehold.co/600x400/2f7bff/ffffff?text=SaaS+Onboarding',
            'date' => '2026-01-15 10:00:00'
        ],
        [
            'title' => 'Smart Booking: From Search to Appointment',
            'content' => 'Patients can search facilities, select doctors, and submit appointment requests with clear steps and confirmation.',
            'category' => 'Appointments',
            'image' => 'https://placehold.co/600x400/27ae60/ffffff?text=Smart+Booking',
            'date' => '2026-01-18 10:00:00'
        ],
        [
            'title' => 'Separate Psychological Centers Workflow',
            'content' => 'A dedicated module for psychological centers with privacy-aware access, scheduling, and session documentation.',
            'category' => 'Mental Health',
            'image' => 'https://placehold.co/600x400/9b59b6/ffffff?text=Mental+Health',
            'date' => '2026-01-20 10:00:00'
        ],
        [
            'title' => 'Blood Donation & Requests Registry',
            'content' => 'Register donors, manage blood stock, and broadcast urgent blood requests across hospitals and clinics.',
            'category' => 'Blood',
            'image' => 'https://placehold.co/600x400/e74c3c/ffffff?text=Blood+Registry',
            'date' => '2026-01-22 10:00:00'
        ],
        [
            'title' => 'Diet Planner: Personalized Nutrition Requests',
            'content' => 'Submit your health profile and goals to receive a tailored diet plan aligned with clinical guidance and follow-up.',
            'category' => 'Nutrition',
            'image' => 'https://placehold.co/600x400/f39c12/ffffff?text=Diet+Planner',
            'date' => '2026-01-10 10:00:00'
        ]
    ];

    $importedCount = 0;
    foreach ($blogs as $blog) {
        $slug = $blogModel->generateSlug($blog['title']);
        
        // Remove old broken entry if exists to replace with placeholder
        $db->prepare("DELETE FROM blogs WHERE slug = :slug AND image LIKE 'assets/%'")->execute(['slug' => $slug]);

        // Check if blog already exists
        $check = $db->prepare("SELECT id FROM blogs WHERE slug = :slug");
        $check->execute(['slug' => $slug]);
        if ($check->rowCount() > 0) continue; 

        $data = [
            'title' => $blog['title'],
            'slug' => $slug,
            'content' => $blog['content'],
            'excerpt' => substr(strip_tags($blog['content']), 0, 150),
            'category_id' => $catMap[$blog['category']],
            'image' => $blog['image'],
            'author_id' => 1 // Default Super Admin
        ];
        
        $sql = "INSERT INTO blogs (title, slug, content, excerpt, category_id, image, author_id, created_at) 
                VALUES (:title, :slug, :content, :excerpt, :category_id, :image, :author_id, :created_at)";
        $data['created_at'] = $blog['date'];
        $stmt = $db->prepare($sql);
        if ($stmt->execute($data)) {
            $importedCount++;
        }
    }

    echo "<h3>Success!</h3>";
    echo "<p>$importedCount blogs have been updated/migrated with placeholders.</p>";
    echo "<p><a href='?route=admin/blogs'>View Blogs in Admin</a></p>";

} catch (Exception $e) {
    echo "<h3>Error!</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
