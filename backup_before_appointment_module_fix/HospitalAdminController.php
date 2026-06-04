<?php
/**
 * ==========================================================================
 * HOSPITAL ADMIN CONTROLLER (HospitalAdminController.php)
 * ==========================================================================
 * This controller manages the portal operations of a specific Hospital or Clinic.
 * It provides administrative features including:
 * 1. Live Notification Polling & Real-time alert counts
 * 2. Medical Staff Management (Doctors, Departments, Specializations)
 * 3. Daily Clinical Operations (Schedules, Appointments, Status Updates)
 * 4. Specialized Care Services (Diet Plan processing, Blood Donor networks)
 * 5. Facility Settings & Receptionist staff administration
 * 
 * Access Control Shield: Restricted to users with the 'hospital_admin' role.
 */

class HospitalAdminController extends Controller {
    private $hospital_id; // Unique identifier of the hospital profile linked to the logged-in admin

    /**
     * Constructor & Security Shield:
     * Enforces authentication and retrieves the hospital ID linked to the user account.
     */
    public function __construct() {
        parent::__construct();
        // Shield: Redirect unauthorized users back to the login screen
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hospital_admin') {
            $this->redirect('?route=auth/login');
        }
        
        // Link session to specific hospital ID for all scoped queries
        $this->hospital_id = $this->getHospitalId();
        if (!$this->hospital_id) {
            die("Error: No Hospital Profile Linked to this Account. Please contact Super Admin.");
        }
    }

    /**
     * Helper: Queries the hospital table to find the record linked to the logged-in user.
     */
    private function getHospitalId() {
        $stmt = $this->db->prepare("SELECT id FROM hospitals WHERE user_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        return $stmt->fetchColumn();
    }

    /**
     * Overrides base view() method:
     * Automatically injects live notifications list and handles "seen at" timestamps
     * so notifications badge counts update correctly on every page load.
     */
    public function view($view, $data = []) {
        // 1. Grab the previous "seen at" time (from last page load)
        $prevReadAt = $_SESSION['notif_read_at'] ?? null;

        // 2. Immediately stamp NOW using MySQL time (avoids PHP/MySQL timezone mismatch)
        try {
            $result = $this->db->query("SELECT NOW() as t")->fetch(PDO::FETCH_ASSOC);
            $ts = $result['t'];
            $_SESSION['notif_read_at'] = $ts;
        } catch (Exception $e) {
            $_SESSION['notif_read_at'] = date('Y-m-d H:i:s');
        }

        // 3. Pass both the notifications and the OLD read timestamp to the view
        $data['notifications']    = $this->getNotifications();
        $data['notif_prev_read_at'] = $prevReadAt;
        parent::view($view, $data);
    }

    /**
     * Helper: Retrieves notifications from the last 48 hours for the hospital.
     */
    private function getNotifications() {
        $notes = [];
        try {
            // Pull from unified notifications table (types: appointment, diet_plan, blood_request)
            $stmt = $this->db->prepare(
                "SELECT id, type, title, message, created_at, link, is_read
                 FROM notifications
                 WHERE (hospital_id = :hid OR hospital_id IS NULL) AND created_at >= NOW() - INTERVAL 48 HOUR
                 ORDER BY created_at DESC LIMIT 12"
            );
            $stmt->execute([':hid' => $this->hospital_id]);
            
            // Map types to premium icons and colors
            $typeMap = [
                'appointment' => ['color' => 'blue',   'icon' => 'fas fa-calendar-check'],
                'diet_plan'   => ['color' => 'green',  'icon' => 'fas fa-utensils'],
                'blood_request' => ['color' => 'orange', 'icon' => 'fas fa-ambulance'],
            ];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $meta = $typeMap[$row['type']] ?? ['color' => 'blue', 'icon' => 'fas fa-bell'];
                $notes[] = [
                    'id'      => $row['id'],
                    'is_read' => $row['is_read'],
                    'color'   => $meta['color'],
                    'icon'    => $meta['icon'],
                    'message' => htmlspecialchars($row['message'] ?? $row['title']),
                    'time'    => $row['created_at'],
                    'link'    => $row['link'] ?? '#',
                ];
            }

            // Also pull recent blood requests that came in directly without API: last 24h
            $stmt = $this->db->prepare(
                "SELECT patient_name, blood_group, created_at FROM blood_requests
                 WHERE hospital_id = :hid AND created_at >= NOW() - INTERVAL 24 HOUR
                 AND created_at NOT IN (SELECT created_at FROM notifications WHERE hospital_id = :hid2 AND type='blood_request')
                 ORDER BY created_at DESC LIMIT 5"
            );
            $stmt->execute([':hid' => $this->hospital_id, ':hid2' => $this->hospital_id]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $notes[] = [
                    'color'   => 'orange',
                    'icon'    => 'fas fa-ambulance',
                    'message' => 'Blood request: ' . htmlspecialchars($row['patient_name']) . ' (' . $row['blood_group'] . ')',
                    'time'    => $row['created_at'],
                    'link'    => '?route=hospital_admin/blood_requests',
                ];
            }

            // Sort all notifications chronologically descending
            usort($notes, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
        } catch (Exception $e) {
            // Silently fail to avoid breaking page load on notification errors
        }
        return array_slice($notes, 0, 10);
    }

    /**
     * AJAX Endpoint: Polled by the client navbar to retrieve new alert counts.
     */
    public function poll_notifications() {
        header('Content-Type: application/json');
        $since = $_GET['since'] ?? date('Y-m-d H:i:s', time() - 60);
        try {
            $stmt = $this->db->prepare(
                "SELECT type, title, message, created_at, link FROM notifications
                 WHERE hospital_id = :hid AND created_at > :since
                 ORDER BY created_at DESC LIMIT 10"
            );
            $stmt->execute([':hid' => $this->hospital_id, ':since' => $since]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $typeMap = [
                'appointment'  => ['icon' => 'fas fa-calendar-check', 'color' => 'blue'],
                'diet_plan'    => ['icon' => 'fas fa-utensils',        'color' => 'green'],
                'blood_request'=> ['icon' => 'fas fa-ambulance',       'color' => 'orange'],
            ];
            $items = [];
            foreach ($rows as $r) {
                $meta = $typeMap[$r['type']] ?? ['icon'=>'fas fa-bell','color'=>'blue'];
                $items[] = [
                    'icon'    => $meta['icon'],
                    'color'   => $meta['color'],
                    'message' => $r['message'],
                    'time'    => $r['created_at'],
                    'link'    => $r['link'] ?? '#',
                ];
            }

            // Count total unread notifications in the last 48 hours
            $stmt2 = $this->db->prepare(
                "SELECT COUNT(*) FROM notifications WHERE hospital_id = :hid AND created_at >= NOW() - INTERVAL 48 HOUR AND is_read = 0"
            );
            $stmt2->execute([':hid' => $this->hospital_id]);
            $unread = (int)$stmt2->fetchColumn();

            echo json_encode(['new_count' => count($rows), 'unread_total' => $unread, 'items' => $items, 'server_time' => date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            echo json_encode(['new_count' => 0, 'unread_total' => 0, 'items' => [], 'server_time' => date('Y-m-d H:i:s')]);
        }
        exit;
    }

    /**
     * Action: Hospital Admin Dashboard.
     * Aggregates stats scoped to this specific hospital.
     */
    public function dashboard() {
        try {
            // Fetch hospital details
            $stmt = $this->db->prepare("SELECT name, type FROM hospitals WHERE id = :hid");
            $stmt->execute([':hid' => $this->hospital_id]);
            $hospital_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $type = ($hospital_info && isset($hospital_info['type'])) ? $hospital_info['type'] : 'Hospital';
            $name = ($hospital_info && isset($hospital_info['name'])) ? $hospital_info['name'] : 'Facility';
            $data['page_title'] = "$name ($type) Dashboard";
            
            // Total affiliated Doctors
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM doctor_hospital_affiliations WHERE hospital_id = :hid");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['total_doctors'] = (int)$stmt->fetchColumn();

            // Total Appointments booked for Today
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE (hospital_id = :hid OR hospital_id IS NULL) AND DATE(date) = CURDATE()");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['appointments_today'] = (int)$stmt->fetchColumn();
            
            // Pending Appointments count
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE (hospital_id = :hid OR hospital_id IS NULL) AND status = 'pending'");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['pending_appointments'] = (int)$stmt->fetchColumn();

            // Total registered Blood Donors linked to this network
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM blood_donors WHERE hospital_id = :hid");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['total_donors'] = (int)$stmt->fetchColumn();

            // Pending Blood requests
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM blood_requests WHERE hospital_id = :hid AND status = 'pending'");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['pending_blood_requests'] = (int)$stmt->fetchColumn();

            // Total Diet Plans requested
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM diet_plans WHERE hospital_id = :hid");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['total_diet_plans'] = (int)$stmt->fetchColumn();

            $this->view('hospital_admin/dashboard', $data);

        } catch (PDOException $e) {
            $data['page_title'] = "Hospital Dashboard";
            $data['total_doctors'] = 0;
            $data['appointments_today'] = 0;
            $data['pending_diets'] = 0;
            $data['db_error'] = "DB Note: " . $e->getMessage();
            $this->view('hospital_admin/dashboard', $data);
        }
    }

    /**
     * Action: Lists all departments within the facility.
     */
    public function departments() {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE hospital_id = :hid ORDER BY name ASC");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['departments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Manage Departments";
        $this->view('hospital_admin/departments', $data);
    }

    /**
     * Action: Adds a new department to the facility.
     */
    public function store_department() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            
            if(!empty($name)) {
                try {
                    $stmt = $this->db->prepare("INSERT INTO departments (hospital_id, name, description) VALUES (:hid, :name, :desc)");
                    $stmt->execute([':hid' => $this->hospital_id, ':name' => $name, ':desc' => $description]);
                    $_SESSION['success'] = "Department added.";
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                         $_SESSION['error'] = "Department '$name' already exists.";
                    } else {
                         $_SESSION['error'] = "Error: " . $e->getMessage();
                    }
                }
            }
            $this->redirect('?route=hospital_admin/departments');
        }
    }
    
    /**
     * Action: Deletes a department from the facility.
     */
    public function delete_department() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $this->db->prepare("DELETE FROM departments WHERE id = :id AND hospital_id = :hid");
            $stmt->execute([':id' => $id, ':hid' => $this->hospital_id]);
        }
        $this->redirect('?route=hospital_admin/departments');
    }

    /**
     * Action: Updates doctor profiles and mappings to specializations & departments.
     */
    public function update_doctor() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_POST['user_id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $specialization_id = $_POST['specialization_id'];
            $department_id = $_POST['department_id'];
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $bio = $_POST['biography'] ?? '';
            
            try {
                $this->db->beginTransaction();
                
                // Fetch specialization name from the lookup table
                $stmt = $this->db->prepare("SELECT name FROM specializations WHERE id = :id");
                $stmt->execute([':id' => $specialization_id]);
                $spec_name = $stmt->fetchColumn(); 

                // 1. Update Core user login name/email
                $stmt = $this->db->prepare("UPDATE users SET name=:name, email=:email WHERE id=:id");
                $stmt->execute([':name'=>$name, ':email'=>$email, ':id'=>$user_id]);
                
                // 2. Update doctor metadata mapping tables
                $stmt = $this->db->prepare("UPDATE doctors_details SET specialization=:spec, specialization_id=:spec_id, department_id=:dept_id, phone=:phone, address=:addr, biography=:bio WHERE user_id=:uid");
                $stmt->execute([':spec'=>$spec_name, ':spec_id'=>$specialization_id, ':dept_id'=>$department_id, ':phone'=>$phone, ':addr'=>$address, ':bio'=>$bio, ':uid'=>$user_id]);
                
                $this->db->commit();
                $_SESSION['success'] = "Doctor updated successfully.";
            } catch(Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }
            $this->redirect('?route=hospital_admin/doctors');
        }
    }

    /**
     * Action: Activates or deactivates a doctor's account.
     */
    public function toggle_doctor_status() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $id = $_GET['id']; // User ID of the doctor
            $status = $_GET['status']; // 0 or 1
            
            // Verify this doctor belongs to this hospital
            $stmt = $this->db->prepare("
                SELECT u.id 
                FROM users u
                JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
                WHERE u.id = :uid AND dha.hospital_id = :hid
            ");
            $stmt->execute([':uid' => $id, ':hid' => $this->hospital_id]);
            
            if ($stmt->fetch()) {
                $stmt = $this->db->prepare("UPDATE users SET is_active = :status WHERE id = :uid");
                $stmt->execute([':status' => $status, ':uid' => $id]);
                $_SESSION['success'] = "Doctor status updated.";
            } else {
                 $_SESSION['error'] = "Doctor not found or not affiliated with your hospital.";
            }
        }
        $this->redirect('?route=hospital_admin/doctors');
    }

    /**
     * Action: Lists all system-wide specializations.
     */
    public function specializations() {
        $stmt = $this->db->query("SELECT * FROM specializations ORDER BY name ASC");
        $data['specializations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Manage Specializations";
        $this->view('hospital_admin/specializations', $data);
    }

    /**
     * Action: Creates a new specialization profile.
     */
    public function store_specialization() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            if(!empty($name)) {
                try {
                    $stmt = $this->db->prepare("INSERT INTO specializations (name, description) VALUES (:name, :desc)");
                    $stmt->execute([':name' => $name, ':desc' => $description]);
                    $_SESSION['success'] = "Specialization added.";
                } catch (PDOException $e) {
                     $_SESSION['error'] = "Error: " . $e->getMessage();
                }
            }
            $this->redirect('?route=hospital_admin/specializations');
        }
    }
    
    /**
     * Action: Deletes a specialization profile.
     */
    public function delete_specialization() {
         if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $this->db->prepare("DELETE FROM specializations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $_SESSION['success'] = "Specialization deleted.";
         }
         $this->redirect('?route=hospital_admin/specializations');
    }

    /**
     * Action: Renders settings, loads staff (receptionists), and toggle alerts.
     */
    public function settings() {
        // Fetch Hospital Details
        $stmt = $this->db->prepare("SELECT * FROM hospitals WHERE id = :hid");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['hospital'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch Staff (Receptionists) affiliated with this hospital
        $stmt = $this->db->prepare("
            SELECT u.*, h.name as hospital_name 
            FROM users u 
            JOIN receptionist_hospital_affiliations rha ON u.id = rha.receptionist_id
            JOIN hospitals h ON rha.hospital_id = h.id
            WHERE rha.hospital_id = :hid
        ");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['staff'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Hospital Settings";
        
        // Fetch email notification settings
        $data['email_notifications'] = $this->isEmailEnabledForHospital($this->hospital_id) ? 1 : 0;
        $this->view('hospital_admin/settings', $data);
    }

    /**
     * Action: Updates hospital settings such as email notification toggles.
     */
    public function update_settings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('?route=hospital_admin/settings'); }
        $enabled = isset($_POST['email_notifications']) && ($_POST['email_notifications'] === '1' || $_POST['email_notifications'] === 'on') ? 1 : 0;
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS hospital_settings (hospital_id INT PRIMARY KEY, email_notifications TINYINT(1) DEFAULT 1)");
            $stmt = $this->db->prepare("INSERT INTO hospital_settings (hospital_id, email_notifications) VALUES (:hid, :val) ON DUPLICATE KEY UPDATE email_notifications = :val2");
            $stmt->execute([':hid' => $this->hospital_id, ':val' => $enabled, ':val2' => $enabled]);
            $_SESSION['success'] = "Settings updated.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error saving settings: " . $e->getMessage();
        }
        $this->redirect('?route=hospital_admin/settings');
    }

    /**
     * Action: Helper redirect.
     */
    public function staff() {
        $this->redirect('?route=hospital_admin/settings');
    }

    /**
     * Action: Registers a new receptionist user and links them to the hospital network.
     */
    public function store_staff() {
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             $name = $_POST['name'];
             $email = $_POST['email'];
             $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
             
             try {
                $this->db->beginTransaction();
                // 1. Create User
                $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'receptionist')");
                $stmt->execute([':name' => $name, ':email' => $email, ':password' => $password]);
                $user_id = $this->db->lastInsertId();
                
                // 2. Link receptionist to this hospital
                $stmt = $this->db->prepare("INSERT INTO receptionist_hospital_affiliations (receptionist_id, hospital_id) VALUES (:rid, :hid)");
                $stmt->execute([':rid' => $user_id, ':hid' => $this->hospital_id]);
                
                $this->db->commit();
                $_SESSION['success'] = "Staff member added.";
             } catch (Exception $e) {
                 $this->db->rollBack();
                 $_SESSION['error'] = "Error: " . $e->getMessage();
              }
             $this->redirect('?route=hospital_admin/settings');
         }
    }

    /**
     * Action: Lists all doctors affiliated with this facility.
     */
    public function doctors() {
        try {
            $stmt = $this->db->prepare("
                SELECT d.*, u.name, u.email, u.is_active, u.id as doctor_user_id, 
                       s.name as specialization_name, dep.name as department_name
                FROM doctors_details d
                JOIN users u ON d.user_id = u.id
                JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN departments dep ON d.department_id = dep.id
                WHERE dha.hospital_id = :hid
            ");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch departments for doctor assign forms
            $stmt = $this->db->prepare("SELECT id, name FROM departments WHERE hospital_id = :hid ORDER BY name ASC");
            $stmt->execute([':hid' => $this->hospital_id]);
            $data['departments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch specializations for doctor assign forms
            $stmt = $this->db->query("SELECT id, name FROM specializations ORDER BY name ASC");
            $data['specializations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $data['doctors'] = [];
            $data['departments'] = [];
            $data['specializations'] = [];
        }
        $data['page_title'] = "My Doctors";
        $this->view('hospital_admin/doctors', $data);
    }
    
    /**
     * Action: Renders doctor addition view, verifying departments and specializations are set up first.
     */
    public function add_doctor() {
        $data['page_title'] = "Add New Doctor";
        // Fetch Specializations
        $stmt = $this->db->query("SELECT * FROM specializations ORDER BY name ASC");
        $data['specializations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch Departments
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE hospital_id = :hid");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['departments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data['specializations']) || empty($data['departments'])) {
            $_SESSION['error'] = "Please create Departments and Specializations first.";
        }

        $this->view('hospital_admin/add_doctor', $data);
    }

    /**
     * Action: Adds a new doctor, hashes password, and links them to the hospital network.
     */
    public function store_doctor() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             $name = $_POST['name'];
             $email = $_POST['email'];
             $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
             $specialization_id = $_POST['specialization_id']; 
             $department_id = $_POST['department_id'];
             
             try {
                $this->db->beginTransaction();

                // Fetch specialization name mapping
                $stmt = $this->db->prepare("SELECT name FROM specializations WHERE id = :id");
                $stmt->execute([':id' => $specialization_id]);
                $spec_name = $stmt->fetchColumn(); 
                
                $phone = $_POST['phone'] ?? '';
                $address = $_POST['address'] ?? '';
                $bio = $_POST['biography'] ?? '';
                $rating = 5.0;

                // 1. Create entry in users login table
                $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (:name, :email, :password, 'doctor', 1)");
                $stmt->execute([':name' => $name, ':email' => $email, ':password' => $password]);
                $user_id = $this->db->lastInsertId();

                // 2. Insert metadata linking department and specialization
                $stmt = $this->db->prepare("INSERT INTO doctors_details (user_id, specialization, specialization_id, department_id, phone, address, biography, rating, joining_date) VALUES (:uid, :spec, :spec_id, :dept_id, :phone, :addr, :bio, :rating, NOW())");
                $stmt->execute([':uid' => $user_id, ':spec' => $spec_name, ':spec_id' => $specialization_id, ':dept_id' => $department_id, ':phone' => $phone, ':addr' => $address, ':bio' => $bio, ':rating' => $rating]);

                // 3. Associate doctor with this hospital
                $stmt = $this->db->prepare("INSERT INTO doctor_hospital_affiliations (doctor_id, hospital_id) VALUES (:did, :hid)");
                $stmt->execute([':did' => $user_id, ':hid' => $this->hospital_id]);

                $this->db->commit();
                $_SESSION['success'] = "Doctor '$name' added successfully.";
             } catch(Exception $e) {
                  if ($this->db->inTransaction()) $this->db->rollBack();
                  $_SESSION['error'] = "Error: " . $e->getMessage();
             }
             $this->redirect('?route=hospital_admin/doctors');
        }
    }

    /**
     * Action: Renders schedule listings and loads staff for scheduler tools.
     */
    public function schedules() {
        // Fetch doctor duty schedules
        $stmt = $this->db->prepare("
            SELECT s.*, u.name as doctor_name 
            FROM schedules s
            JOIN users u ON s.doctor_id = u.id
            JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
            WHERE dha.hospital_id = :hid
            ORDER BY s.day_of_week, s.start_time
        ");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['schedules'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Doctors for schedule selection dropdowns
        $stmt = $this->db->prepare("
            SELECT u.id, u.name
            FROM users u
            JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
            WHERE dha.hospital_id = :hid
            ORDER BY u.name ASC
        ");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['page_title'] = "Doctor Schedules";
        $this->view('hospital_admin/schedules', $data);
    }

    /**
     * Action: Adds a doctor duty schedule.
     */
    public function store_schedule() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             $doctor_id = $_POST['doctor_id'];
             $day = $_POST['day_of_week'];
             $start = $_POST['start_time'];
             $end = $_POST['end_time'];
             
             try {
                $stmt = $this->db->prepare("INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time) VALUES (:did, :day, :start, :end)");
                $stmt->execute([':did' => $doctor_id, ':day' => $day, ':start' => $start, ':end' => $end]);
                $_SESSION['success'] = "Schedule added.";
             } catch (PDOException $e) {
                 $_SESSION['error'] = "Error: " . $e->getMessage();
             }
             $this->redirect('?route=hospital_admin/schedules');
        }
    }

    /**
     * Action: Removes a doctor duty schedule.
     */
    public function delete_schedule() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $this->db->prepare("
                DELETE s FROM schedules s
                JOIN users u ON s.doctor_id = u.id
                JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
                WHERE s.id = :id AND dha.hospital_id = :hid
            ");
            $stmt->execute([':id' => $id, ':hid' => $this->hospital_id]);
            $_SESSION['success'] = "Schedule removed.";
        }
        $this->redirect('?route=hospital_admin/schedules');
    }

    /**
     * Action: Updates clinical appointments status.
     */
    public function update_appointment() {
        $id     = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        $allowed = ['confirmed', 'cancelled', 'completed', 'pending'];
        
        if ($id && in_array($status, $allowed)) {
            try {
                // Ensure hospital_id is locked to the current admin's facility
                $stmt = $this->db->prepare("
                    UPDATE appointments 
                    SET status = :status, 
                        hospital_id = :hid 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':status' => $status, 
                    ':id'     => $id, 
                    ':hid'    => $this->hospital_id
                ]);
                
                $_SESSION['success'] = "Appointment status updated to " . ucfirst($status) . ".";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error updating appointment: " . $e->getMessage();
            }
        }
        $this->redirect('?route=hospital_admin/appointments');
    }

    /**
     * Action: Marks a notification as read and redirects the user to the linked page.
     */
    public function read_notification() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            try {
                // Fetch notification redirect link
                $stmt = $this->db->prepare("SELECT link FROM notifications WHERE id = :id AND (hospital_id = :hid OR hospital_id IS NULL)");
                $stmt->execute([':id' => $id, ':hid' => $this->hospital_id]);
                $link = $stmt->fetchColumn();

                // Mark notifications as read
                $upd = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
                $upd->execute([':id' => $id]);

                if ($link) {
                    $this->redirect($link);
                }
            } catch (Exception $e) { /* ignore error and continue */ }
        }
        $this->redirect('?route=hospital_admin/dashboard');
    }

    /**
     * Action: Renders paginated listings of appointments.
     */
    public function appointments() {
        $perPage = 50;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        try {
            // Count total appointments for pagination
            $countStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM appointments WHERE hospital_id = :hid OR hospital_id IS NULL"
            );
            $countStmt->execute([':hid' => $this->hospital_id]);
            $total = (int)$countStmt->fetchColumn();

            // Fetch paginated appointments
            $stmt = $this->db->prepare("
                SELECT a.id, a.date, a.status, a.type, a.token_number,
                       u.name  AS doctor_name,
                       pd.name AS patient_name,
                       pd.phone AS patient_phone
                FROM appointments a
                LEFT JOIN users u  ON a.doctor_id  = u.id
                LEFT JOIN patients_details pd ON a.patient_id = pd.id
                WHERE a.hospital_id = :hid OR a.hospital_id IS NULL
                ORDER BY a.date DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':hid',    $this->hospital_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit',  $perPage,           PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset,            PDO::PARAM_INT);
            $stmt->execute();
            $data['appointments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data['total_pages']  = (int)ceil($total / $perPage);
            $data['current_page'] = $page;
            $data['total_count']  = $total;
        } catch (Exception $e) {
            $data['appointments'] = [];
            $data['db_error']     = $e->getMessage();
            $data['total_pages']  = 1;
            $data['current_page'] = 1;
            $data['total_count']  = 0;
        }
        $data['page_title'] = "Appointments";
        $this->view('hospital_admin/appointments', $data);
    }

    /**
     * Action: Renders the notification page detailing history.
     */
    public function notifications() {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE hospital_id = :hid 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':hid' => $this->hospital_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['notifications_all'] = array_map(function($n) {
            $type = $n['type'] ?? 'info';
            $map = [
                'appointment'  => ['icon' => 'fas fa-calendar-check', 'color' => 'primary'],
                'diet_plan'    => ['icon' => 'fas fa-utensils',       'color' => 'success'],
                'blood_request'=> ['icon' => 'fas fa-tint',           'color' => 'danger'],
            ];
            $meta = $map[$type] ?? ['icon' => 'fas fa-info-circle', 'color' => 'secondary'];
            return array_merge($n, $meta, ['time' => $n['created_at']]);
        }, $rows);

        $data['page_title'] = "All Notifications";
        $this->view('hospital_admin/notifications', $data);
    }

    /**
     * Action: Search tool within the hospital workspace.
     */
    public function search() {
        $q = $_GET['q'] ?? '';
        $results = [];

        if (!empty($q)) {
            $search = "%$q%";

            // 1. Search Appointments
            $stmt = $this->db->prepare("
                SELECT a.*, pd.name as patient_name, u.name as doctor_name 
                FROM appointments a
                LEFT JOIN patients_details pd ON a.patient_id = pd.id
                LEFT JOIN users u ON a.doctor_id = u.id
                WHERE a.hospital_id = :hid 
                AND (pd.name LIKE :q OR a.token_number LIKE :q OR pd.phone LIKE :q)
                LIMIT 10
            ");
            $stmt->execute([':hid' => $this->hospital_id, ':q' => $search]);
            $results['appointments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Search Doctors
            $stmt = $this->db->prepare("
                SELECT u.*, d.specialization 
                FROM users u
                JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
                LEFT JOIN doctors_details d ON u.id = d.user_id
                WHERE dha.hospital_id = :hid AND u.name LIKE :q
                LIMIT 10
            ");
            $stmt->execute([':hid' => $this->hospital_id, ':q' => $search]);
            $results['doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Search Diet Plans
            $stmt = $this->db->prepare("
                SELECT * FROM diet_plans 
                WHERE hospital_id = :hid AND (patient_name LIKE :q OR patient_email LIKE :q)
                LIMIT 10
            ");
            $stmt->execute([':hid' => $this->hospital_id, ':q' => $search]);
            $results['diet_plans'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $data['search_query'] = $q;
        $data['results'] = $results;
        $data['page_title'] = "Search Results";
        $this->view('hospital_admin/search_results', $data);
    }

    /**
     * Action: Lists diet plan requests submitted to the hospital.
     */
    public function diet_plans() {
        $stmt = $this->db->prepare("
            SELECT dp.*, u.name as doctor_name 
            FROM diet_plans dp 
            LEFT JOIN users u ON dp.doctor_id = u.id 
            WHERE dp.hospital_id = :hid 
            ORDER BY dp.created_at DESC
        ");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['diet_plans'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Diet Plans";
        $this->view('hospital_admin/diet_plans', $data);
    }

    /**
     * Action: Updates the operational progress status of a diet plan request.
     */
    public function update_diet_plan_status() {
        $id     = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        $allowed = ['pending', 'in_progress', 'completed'];
        if ($id && in_array($status, $allowed)) {
            $stmt = $this->db->prepare("UPDATE diet_plans SET status = :status WHERE id = :id AND hospital_id = :hid");
            $stmt->execute([':status' => $status, ':id' => $id, ':hid' => $this->hospital_id]);
            $_SESSION['success'] = "Diet plan marked as " . ucfirst(str_replace('_', ' ', $status)) . ".";
        }
        $this->redirect('?route=hospital_admin/diet_plans');
    }

    /**
     * Action: Deletes a diet plan request.
     */
    public function delete_diet_plan() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM diet_plans WHERE id = :id AND hospital_id = :hid");
            $stmt->execute([':id' => $id, ':hid' => $this->hospital_id]);
            $_SESSION['success'] = "Diet plan request deleted.";
        }
        $this->redirect('?route=hospital_admin/diet_plans');
    }

    /**
     * Action: Lists blood donors registered at the hospital network.
     */
    public function blood_donors() {
        $stmt = $this->db->prepare("SELECT * FROM blood_donors WHERE hospital_id = :hid ORDER BY created_at DESC");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['donors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Blood Donors";
        $this->view('hospital_admin/blood_donors', $data);
    }

    /**
     * Action: Lists emergency blood donor requests broadcasted to the facility.
     */
    public function blood_requests() {
        $stmt = $this->db->prepare("SELECT * FROM blood_requests WHERE hospital_id = :hid ORDER BY created_at DESC");
        $stmt->execute([':hid' => $this->hospital_id]);
        $data['requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Blood Requests";
        $this->view('hospital_admin/blood_requests', $data);
    }

    /**
     * Action: Updates blood request statuses (Fulfilled, Pending).
     */
    public function update_blood_request() {
        $id     = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;

        $allowed = ['pending', 'fulfilled'];
        if ($id && in_array($status, $allowed)) {
            $stmt = $this->db->prepare("UPDATE blood_requests SET status = :status WHERE id = :id AND hospital_id = :hid");
            $stmt->execute([':status' => $status, ':id' => $id, ':hid' => $this->hospital_id]);
            $_SESSION['success'] = "Blood request marked as " . ucfirst($status) . ".";
        }
        $this->redirect('?route=hospital_admin/blood_requests');
    }

    /**
     * Action: AJAX endpoint to dismiss notifications and clear badge counters.
     */
    public function mark_notifications_read() {
        $readAt = date('Y-m-d H:i:s');
        $_SESSION['notif_read_at'] = $readAt;
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE hospital_id = :hid AND is_read = 0");
            $stmt->execute([':hid' => $this->hospital_id]);
        } catch (Exception $e) { /* silent */ }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'read_at' => $readAt]);
        exit;
    }

    /**
     * Action: Deletes a doctor from the hospital and clears references.
     */
    public function delete_doctor() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            
            // Verify doctor is associated with this facility
            $stmt = $this->db->prepare("
                SELECT u.id 
                FROM users u
                JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
                WHERE u.id = :uid AND dha.hospital_id = :hid
            ");
            $stmt->execute([':uid' => $id, ':hid' => $this->hospital_id]);
            
            if ($stmt->fetch()) {
                try {
                    $this->db->beginTransaction();
                    
                    // 1. Delete affiliations
                    $stmt = $this->db->prepare("DELETE FROM doctor_hospital_affiliations WHERE doctor_id = :uid AND hospital_id = :hid");
                    $stmt->execute([':uid' => $id, ':hid' => $this->hospital_id]);
                    
                    // 2. Delete details
                    $stmt = $this->db->prepare("DELETE FROM doctors_details WHERE user_id = :uid");
                    $stmt->execute([':uid' => $id]);
                    
                    // 3. Delete user account
                    $stmt = $this->db->prepare("DELETE FROM users WHERE id = :uid AND role = 'doctor'");
                    $stmt->execute([':uid' => $id]);
                    
                    $this->db->commit();
                    $_SESSION['success'] = "Doctor deleted successfully.";
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) $this->db->rollBack();
                    $_SESSION['error'] = "Error: " . $e->getMessage();
                }
            } else {
                 $_SESSION['error'] = "Doctor not found or not authorized.";
            }
        }
        $this->redirect('?route=hospital_admin/doctors');
    }
}