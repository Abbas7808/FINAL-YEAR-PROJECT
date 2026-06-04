<?php

class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($usernameOrEmail, $password, $role = null) {
        $query = "SELECT id, name, email, phone, password, role FROM " . $this->table_name . " WHERE (email = :login OR phone = :login)";
        if ($role) {
            $query .= " AND role = :role";
        }
        $query .= " LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':login', $usernameOrEmail);
        if ($role) {
            $stmt->bindParam(':role', $role);
        }
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // In a real app, use password_verify($password, $row['password'])
            // For this demo/setup, we use plain text as per the SQL insert provided earlier
            // OR if you want secure, update the SQL insert to use hashed passwords.
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }
}
