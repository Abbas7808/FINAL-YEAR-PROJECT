<?php
require_once 'models/Appointment.php';

class PatientController extends Controller {
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
            $this->redirect('?route=auth/login');
        }
    }

    public function dashboard() {
        // Fetch My Appointments
        $user_id = $_SESSION['user_id'];
        
        // Find patient_id from patients_details using user_id
        $stmt = $this->db->prepare("SELECT id FROM patients_details WHERE user_id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $history = [];
        if($row) {
            $patient_id = $row['id'];
            $query = "SELECT a.*, u.name as doctor_name, h.name as hospital_name 
                      FROM appointments a 
                      LEFT JOIN users u ON a.doctor_id = u.id 
                      LEFT JOIN hospitals h ON a.hospital_id = h.id 
                      WHERE a.patient_id = :pid 
                      ORDER BY a.date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':pid' => $patient_id]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $data['history'] = $history;
        $data['page_title'] = "My Health Dashboard";
        $this->view('patient/dashboard', $data);
    }
}
