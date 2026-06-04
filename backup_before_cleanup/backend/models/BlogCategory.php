<?php
class BlogCategory {
    private $db;
    private $table = 'blog_categories';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name) {
        $slug = $this->generateSlug($name);
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, slug) VALUES (:name, :slug)");
        return $stmt->execute(['name' => $name, 'slug' => $slug]);
    }

    public function update($id, $name) {
        $slug = $this->generateSlug($name);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET name = :name, slug = :slug WHERE id = :id");
        return $stmt->execute(['name' => $name, 'slug' => $slug, 'id' => $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function generateSlug($title) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
}
