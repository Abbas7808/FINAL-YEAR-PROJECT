<?php

class Appointment {
    private $conn;
    private $table_name = "appointments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($patient_id, $doctor_id, $hospital_id, $date, $time_slot = null, $type = 'consultation') {
        // Generate Token Number (total appointments for this hospital+date + 1)
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE hospital_id = :hospital_id AND date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hospital_id', $hospital_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        $token = (int)$stmt->fetchColumn() + 1;

        // Use 'pending' status (consistent with API booking)
        $query = "INSERT INTO " . $this->table_name . " (patient_id, doctor_id, hospital_id, token_number, date, time_slot, status, type) 
                  VALUES (:patient_id, :doctor_id, :hospital_id, :token, :date, :time_slot, 'pending', :type)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':hospital_id', $hospital_id);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time_slot', $time_slot);
        $stmt->bindParam(':type', $type);

        if($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function getTodayAppointments($doctor_id = null, $hospital_id = null) {
        $date = date('Y-m-d');
        // Include pending, waiting, and confirmed appointments in today's queue
        $query = "SELECT a.*, p.name as patient_name, u.name as doctor_name 
                  FROM " . $this->table_name . " a
                  JOIN patients_details p ON a.patient_id = p.id
                  LEFT JOIN users u ON a.doctor_id = u.id
                  WHERE a.date >= :date AND a.status IN ('pending', 'waiting', 'confirmed')";
        
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
