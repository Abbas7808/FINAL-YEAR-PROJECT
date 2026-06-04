<?php

class Appointment {
    private $conn;
    private $table_name = "appointments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($patient_id, $doctor_id, $hospital_id, $date, $deficiency_details, $type = 'consultation') {
        // Generate Token Number (Auto increment for the date)
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE doctor_id = :doctor_id AND date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $token = $row['count'] + 1;

        $query = "INSERT INTO " . $this->table_name . " (`patient_id`, `doctor_id`, `hospital_id`, `token_number`, `date`, `status`, `deficiency_details`, `type`) VALUES (:patient_id, :doctor_id, :hospital_id, :token, :date, 'waiting', :deficiency_details, :type)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':hospital_id', $hospital_id);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':deficiency_details', $deficiency_details);
        $stmt->bindParam(':type', $type);

        if($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function getTodayAppointments($doctor_id = null, $hospital_id = null) {
        $date = date('Y-m-d');
        $query = "SELECT a.*, p.name as patient_name, u.name as doctor_name 
                  FROM " . $this->table_name . " a
                  JOIN patients_details p ON a.patient_id = p.id
                  LEFT JOIN users u ON a.doctor_id = u.id
                  WHERE a.date = :date";
        
        if($doctor_id) {
            $query .= " AND a.doctor_id = :doctor_id";
        }
        if($hospital_id) {
            $query .= " AND a.hospital_id = :hospital_id";
        }
        
        $query .= " ORDER BY a.token_number ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        if($doctor_id) $stmt->bindParam(':doctor_id', $doctor_id);
        if($hospital_id) $stmt->bindParam(':hospital_id', $hospital_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
