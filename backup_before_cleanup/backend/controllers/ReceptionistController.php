<?php
require_once 'models/Appointment.php';

class ReceptionistController extends Controller {
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'receptionist') {
            $this->redirect('?route=auth/login');
        }
    }

    public function dashboard() {
        $appointmentModel = new Appointment($this->db);
        
        // Fetch affiliated hospital
        $stmt = $this->db->prepare("SELECT hospital_id FROM receptionist_hospital_affiliations WHERE receptionist_id = :rid");
        $stmt->execute([':rid' => $_SESSION['user_id']]);
        $hospital_id = $stmt->fetchColumn() ?: null;

        $data['appointments'] = $appointmentModel->getTodayAppointments(null, $hospital_id);
        $data['page_title'] = "Receptionist Dashboard";
        $this->view('receptionist/dashboard', $data);
    }

    public function add_patient() {
        // Fetch Doctors for the dropdown
        $stmt = $this->db->query("SELECT * FROM users WHERE role='doctor'");
        $data['doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Add Patient";
        $this->view('receptionist/add_patient', $data);
    }

    public function store_patient() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $phone = $_POST['phone']; // Optional login, maybe just store in details
            $age = $_POST['age'];
            $gender = $_POST['gender'];
            $doctor_id = $_POST['doctor_id'];
            $date = date('Y-m-d');

            // 1. Create Patient Entry in patients_details (Simplified: No User account for now unless requested, but prompt said "add a user ( patient)")
            // Let's create a User account too so they can login.
            // Password defaults to phone number or something simple.
            $email = str_replace(' ', '', strtolower($name)) . rand(100,999) . "@mednoa.com"; // Fake email generation
            $password = "123456"; 

            // Create User
            $stmt = $this->db->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (:name, :email, :phone, :password, 'patient')");
            $stmt->execute([':name' => $name, ':email' => $email, ':phone' => $phone, ':password' => $password]);
            $user_id = $this->db->lastInsertId();

            // Create Patient Details
            $stmt = $this->db->prepare("INSERT INTO patients_details (user_id, name, age, gender, phone) VALUES (:uid, :name, :age, :gender, :phone)");
            $stmt->execute([':uid' => $user_id, ':name' => $name, ':age' => $age, ':gender' => $gender, ':phone' => $phone]);
            $patient_id = $this->db->lastInsertId();

            // 2. Fetch Hospital ID from Receptionist affiliation
            $stmt = $this->db->prepare("SELECT hospital_id FROM receptionist_hospital_affiliations WHERE receptionist_id = :rid");
            $stmt->execute([':rid' => $_SESSION['user_id']]);
            $hospital_id = $stmt->fetchColumn();

            if (!$hospital_id) {
                 die("Error: Receptionist not linked to any hospital.");
            }

            // 3. Create Appointment & Token
            $appointmentModel = new Appointment($this->db);
            // args: ($patient_id, $doctor_id, $hospital_id, $date, $deficiency_details, $type = 'consultation')
            $token = $appointmentModel->create($patient_id, $doctor_id, $hospital_id, $date, "Walk-in patient");

            if ($token) {
                // Redirect to dashboard with success message
                echo "<script>alert('Patient Added! Token Number: $token'); window.location='?route=receptionist/dashboard';</script>";
            } else {
                echo "Error generating token.";
            }
        }
    }
    public function print_token() {
         $id = $_GET['id'];
         // Fetch Appointment Details for Token
         $stmt = $this->db->prepare("
            SELECT a.token_number, a.date, a.time_slot,
                   p.name as patient_name, p.age, p.gender, 
                   d.name as doctor_name, 
                   h.name as hospital_name
            FROM appointments a
            JOIN patients_details p ON a.patient_id = p.id
            LEFT JOIN users d ON a.doctor_id = d.id
            JOIN hospitals h ON a.hospital_id = h.id
            WHERE a.id = :id
         ");
         $stmt->execute([':id' => $id]);
         $data['token'] = $stmt->fetch(PDO::FETCH_ASSOC);
         
         if (!$data['token']) die("Token not found.");

         $this->view('receptionist/print_token', $data);
    }
}
