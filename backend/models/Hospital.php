<?php
class Hospital {
    private $conn;
    private $table = 'hospitals';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($region_id = null) {
        if ($region_id) {
            $query = "SELECT * FROM " . $this->table . " WHERE region_id = :region_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':region_id', $region_id);
        } else {
            $query = "SELECT * FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
