<?php
require_once 'models/Appointment.php';

class DoctorController extends Controller {
    public function __construct() {
        parent::__construct();
        // Allow receptionist to access print_prescription too
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?route=auth/login');
        }
    }

    public function dashboard() {
        if($_SESSION['role'] != 'doctor') die("Access Denied");
        $appointmentModel = new Appointment($this->db);

        $doctorId = $_SESSION['user_id'];
        // Appointments explicitly assigned to this doctor
        $doctorAppointments = $appointmentModel->getTodayAppointments($doctorId) ?: [];

        // Also include unassigned (walk-in) appointments for hospitals this doctor is affiliated with
        $stmt = $this->db->prepare("SELECT hospital_id FROM doctor_hospital_affiliations WHERE doctor_id = :did");
        $stmt->execute([':did' => $doctorId]);
        $hospitalIds = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $unassigned = [];
        foreach ($hospitalIds as $hid) {
            $apps = $appointmentModel->getTodayAppointments(null, $hid) ?: [];
            foreach ($apps as $a) {
                if (empty($a['doctor_id'])) {
                    $unassigned[$a['id']] = $a; // keyed by id to avoid duplicates
                }
            }
        }

        // Merge assigned + unassigned and sort by token_number
        $merged = [];
        foreach ($doctorAppointments as $a) $merged[$a['id']] = $a;
        foreach ($unassigned as $id => $a) $merged[$id] = $a;
        $appointments = array_values($merged);
        usort($appointments, function($x, $y) { return $x['token_number'] <=> $y['token_number']; });

        // Include upcoming appointments logic handled in Appointment model (date >= :date)

        $data['appointments'] = $appointments;
        $data['page_title'] = "Doctor Dashboard";
        $this->view('doctor/dashboard', $data);
    }

    public function my_patients() {
        if($_SESSION['role'] != 'doctor') die("Access Denied");
        
        $doctorId = $_SESSION['user_id'];
        
        $query = "SELECT p.*, MAX(a.date) as last_visit 
                  FROM patients_details p 
                  JOIN appointments a ON p.id = a.patient_id 
                  WHERE a.doctor_id = :did 
                  GROUP BY p.id
                  ORDER BY last_visit DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':did' => $doctorId]);
        
        $data['patients'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data['page_title'] = "My Patients";
        $this->view('doctor/my_patients', $data);
    }

    public function diagnose() {
        if($_SESSION['role'] != 'doctor') die("Access Denied");

        $id = $_GET['id'];
        // Get Appointment Details + Patient Details
        $stmt = $this->db->prepare("SELECT a.*, p.name, p.age, p.gender FROM appointments a JOIN patients_details p ON a.patient_id = p.id WHERE a.id = :id");
        $stmt->execute([':id' => $id]);
        $data['appointment'] = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['page_title'] = "Patient Diagnosis";
        $this->view('doctor/diagnose', $data);
    }

    public function store_diagnosis() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $appointment_id = $_POST['appointment_id'];
            $diagnosis = $_POST['diagnosis'];
            $medicines = $_POST['medicines'];
            $advice = $_POST['advice'];

            // Insert Prescription
            $stmt = $this->db->prepare("INSERT INTO prescriptions (appointment_id, diagnosis, medicines, advice) VALUES (:aid, :diag, :meds, :adv)");
            $stmt->execute([
                ':aid' => $appointment_id,
                ':diag' => $diagnosis,
                ':meds' => $medicines, // Storing as simple text for this task, could be JSON
                ':adv' => $advice
            ]);

            // Update Appointment Status
            $stmt = $this->db->prepare("UPDATE appointments SET status='completed' WHERE id=:id");
            $stmt->execute([':id' => $appointment_id]);

            $this->redirect('?route=doctor/dashboard');
        }
    }

    public function print_prescription() {
        $id = $_GET['id'];
        // Fetch everything needs for the A4 print
        $query = "SELECT p.*, a.token_number, a.date, pd.name as patient_name, pd.age, pd.gender, u.name as doctor_name, dd.specialization
                  FROM prescriptions p
                  JOIN appointments a ON p.appointment_id = a.id
                  JOIN patients_details pd ON a.patient_id = pd.id
                  LEFT JOIN users u ON a.doctor_id = u.id
                  LEFT JOIN doctors_details dd ON u.id = dd.user_id
                  WHERE a.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $data['prescription'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // This view is standalone, no sidebar/header usually for clean printing, but we can include if styled correctly with @media print
        $this->view('doctor/print_prescription', $data);
    }
}
