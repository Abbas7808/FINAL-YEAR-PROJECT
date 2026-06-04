<?php

class Blog {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT b.*, u.name as author_name, c.name as category_name 
                                 FROM blogs b 
                                 LEFT JOIN users u ON b.author_id = u.id 
                                 LEFT JOIN blog_categories c ON b.category_id = c.id
                                 ORDER BY b.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT b.*, c.name as category_name 
                                   FROM blogs b 
                                   LEFT JOIN blog_categories c ON b.category_id = c.id 
                                   WHERE b.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO blogs (title, slug, content, excerpt, category_id, image, author_id) 
                VALUES (:title, :slug, :content, :excerpt, :category_id, :image, :author_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $sql = "UPDATE blogs SET 
                title = :title, 
                slug = :slug, 
                content = :content, 
                excerpt = :excerpt, 
                category_id = :category_id, 
                image = :image 
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM blogs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function generateSlug($title) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
}
