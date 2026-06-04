<?php
class Doctor {
    private $conn;
    private $table = 'doctors_details';
    private $users_table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get doctor profile by User ID
    public function getProfile($user_id) {
        $query = "SELECT d.*, u.name, u.email 
                  FROM " . $this->table . " d
                  JOIN " . $this->users_table . " u ON d.user_id = u.id
                  WHERE d.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update Profile
    public function updateProfile($user_id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET specialization = :spec, 
                      qualification = :qual, 
                      experience_years = :exp, 
                      bio = :bio, 
                      contact_number = :phone
                  WHERE user_id = :uid";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':spec' => $data['specialization'],
            ':qual' => $data['qualification'],
            ':exp' => $data['experience_years'],
            ':bio' => $data['bio'],
            ':phone' => $data['contact_number'],
            ':uid' => $user_id
        ]);
        return true;
    }
    // Get All Doctors with User info
    public function getAllDoctors() {
        $query = "SELECT d.*, u.name, u.email, h.name as hospital_name 
                  FROM " . $this->table . " d
                  JOIN " . $this->users_table . " u ON d.user_id = u.id
                  LEFT JOIN doctor_hospital_affiliations dha ON d.user_id = dha.doctor_id
                  LEFT JOIN hospitals h ON dha.hospital_id = h.id
                  GROUP BY d.user_id"; 
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDoctorById($id) {
        $query = "SELECT d.*, u.name, u.email 
                  FROM " . $this->table . " d
                  JOIN " . $this->users_table . " u ON d.user_id = u.id
                  WHERE d.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
