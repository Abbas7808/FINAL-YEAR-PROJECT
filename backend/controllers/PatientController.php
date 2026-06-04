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
        $user_id = $_SESSION['user_id'];
        
        // 1. Fetch Patient Profile Info (Users + Patients Details)
        $stmt = $this->db->prepare("
            SELECT u.name, u.username, u.phone, u.email, u.created_at as registration_date, 
                   pd.id as patient_id, pd.age, pd.gender, pd.blood_group, pd.address
            FROM users u
            LEFT JOIN patients_details pd ON u.id = pd.user_id
            WHERE u.id = :uid
            LIMIT 1
        ");
        $stmt->execute([':uid' => $user_id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$profile) {
            // Profile fallback if patients_details row is missing
            $profile = [
                'name' => $_SESSION['name'] ?? 'User',
                'username' => $_SESSION['username'] ?? 'patient',
                'phone' => $_SESSION['phone'] ?? '',
                'email' => $_SESSION['email'] ?? '',
                'registration_date' => date('Y-m-d H:i:s'),
                'patient_id' => null,
                'age' => '—',
                'gender' => '—',
                'blood_group' => '—',
                'address' => '—'
            ];
        }

        $patient_id = $profile['patient_id'];
        $phone = $profile['phone'];

        // 2. Fetch Appointments & separate into Upcoming vs Previous
        $upcoming_appointments = [];
        $previous_appointments = [];
        $history = []; // Keep for backward compatibility/total list

        if ($patient_id) {
            $query = "SELECT a.*, u.name as doctor_name, h.name as hospital_name 
                      FROM appointments a 
                      LEFT JOIN users u ON a.doctor_id = u.id 
                      LEFT JOIN hospitals h ON a.hospital_id = h.id 
                      WHERE a.patient_id = :pid 
                      ORDER BY a.date DESC, a.time_slot DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':pid' => $patient_id]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $current_date = date('Y-m-d');
            foreach ($history as $app) {
                if ($app['date'] >= $current_date && $app['status'] !== 'completed' && $app['status'] !== 'cancelled') {
                    $upcoming_appointments[] = $app;
                } else {
                    $previous_appointments[] = $app;
                }
            }
        }

        // 3. Fetch Prescriptions
        $prescriptions = [];
        if ($patient_id) {
            $query = "SELECT p.*, a.date as appointment_date, u.name as doctor_name, h.name as hospital_name
                      FROM prescriptions p
                      JOIN appointments a ON p.appointment_id = a.id
                      LEFT JOIN users u ON a.doctor_id = u.id
                      LEFT JOIN hospitals h ON a.hospital_id = h.id
                      WHERE a.patient_id = :pid
                      ORDER BY a.date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':pid' => $patient_id]);
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // 4. Fetch Diet Plans
        $diet_plans = [];
        if ($phone) {
            $query = "SELECT dp.*, h.name as hospital_name, u.name as doctor_name
                      FROM diet_plans dp
                      LEFT JOIN hospitals h ON dp.hospital_id = h.id
                      LEFT JOIN users u ON dp.doctor_id = u.id
                      WHERE dp.patient_phone = :phone
                      ORDER BY dp.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':phone' => $phone]);
            $diet_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // 5. Fetch Health Assessments
        $stmt = $this->db->prepare("
            SELECT * FROM health_assessments 
            WHERE user_id = :uid 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([':uid' => $user_id]);
        $health_assessment = $stmt->fetch(PDO::FETCH_ASSOC);

        // 6. Fetch Blood Donation Info (Donor profile + Requests)
        $donor_status = null;
        if ($phone) {
            $stmt = $this->db->prepare("
                SELECT bd.*, h.name as hospital_name 
                FROM blood_donors bd
                LEFT JOIN hospitals h ON bd.hospital_id = h.id
                WHERE bd.phone = :phone
                ORDER BY bd.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([':phone' => $phone]);
            $donor_status = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $blood_requests = [];
        if ($phone) {
            $query = "SELECT br.*, h.name as hospital_name 
                      FROM blood_requests br
                      LEFT JOIN hospitals h ON br.hospital_id = h.id
                      WHERE br.phone = :phone
                      ORDER BY br.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':phone' => $phone]);
            $blood_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Pack data array
        $data['profile'] = $profile;
        $data['history'] = $history; // Keep for backward-compatibility checks
        $data['upcoming_appointments'] = $upcoming_appointments;
        $data['previous_appointments'] = $previous_appointments;
        $data['prescriptions'] = $prescriptions;
        $data['diet_plans'] = $diet_plans;
        $data['health_assessment'] = $health_assessment;
        $data['donor_status'] = $donor_status;
        $data['blood_requests'] = $blood_requests;
        
        $data['page_title'] = "My Health Dashboard";
        $this->view('patient/dashboard', $data);
    }
}
