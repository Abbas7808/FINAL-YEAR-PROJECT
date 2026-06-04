<?php

/**
 * ==========================================
 * MedNova REST API Controller (ApiController)
 * ==========================================
 * This controller serves as the primary endpoint for all asynchronous AJAX operations.
 * It provides structured endpoints for retrieving data (regions, hospitals, departments, doctors)
 * and submitting transactions (appointment bookings, contact forms, diet plans, blood donations).
 * Handles CORS headers and returns standardized JSON responses.
 */
class ApiController extends Controller {

    /**
     * Constructor: Initializes database connection and handles CORS preflight options.
     */
    public function __construct() {
        parent::__construct();
        $this->handleCors();
    }

    /**
     * Configures CORS headers to allow cross-origin requests from frontend layouts.
     */
    private function handleCors() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
        // Stop execution for HTTP OPTIONS preflight checks
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Helper to return standard JSON data structure and close response stream.
     */
    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Default Index route.
     */
    public function index() {
        $this->jsonResponse(['message' => 'Med-Nova API v1']);
    }

    /**
     * API: Fetches list of all regions sorted alphabetically.
     */
    public function regions() {
        try {
            $stmt = $this->db->query("SELECT * FROM regions ORDER BY name ASC");
            $this->jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Fetches list of active hospitals/clinics.
     * Supports filtering by region_id, slug, id, email, type, or search queries.
     */
    public function hospitals() {
        try {
            $region_id = $_GET['region_id'] ?? null;
            $slug = $_GET['slug'] ?? null;
            $id = $_GET['id'] ?? null;
            $email = $_GET['email'] ?? null;
            $search = $_GET['search'] ?? null;

            // Base SQL Query for active subscriptions
            $sql = "SELECT h.*, u.email as admin_email FROM hospitals h LEFT JOIN users u ON h.user_id = u.id WHERE h.subscription_status = 'active'";
            $params = [];

            // Apply filters if passed in GET request
            if ($slug) { $sql .= " AND h.slug = :slug"; $params[':slug'] = $slug; }
            if ($id) { $sql .= " AND h.id = :id"; $params[':id'] = $id; }
            if ($email) { $sql .= " AND u.email = :email"; $params[':email'] = $email; }
            if ($region_id) { $sql .= " AND h.region_id = :region_id"; $params[':region_id'] = $region_id; }
            if (!empty($_GET['type']) && $_GET['type'] !== 'all') {
                $sql .= " AND h.type = :type";
                $params[':type'] = $_GET['type'];
            }
            if ($search) {
                 $sql .= " AND (h.name LIKE :search OR h.address LIKE :search OR u.email LIKE :search)";
                 $params[':search'] = "%$search%";
            }
            
            $sql .= " ORDER BY h.name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // If fetching a single item via unique identifier, return object instead of array
            if ($slug || $id || $email) {
                 $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
                 if ($hospital) $this->jsonResponse($hospital);
                 else $this->jsonResponse(['error' => 'Hospital not found'], 404);
            } else {
                 $this->jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'DB Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Fetches unique list of departments.
     * Supports optional hospital_id parameter.
     */
    public function departments() {
        try {
            $hospital_id = $_GET['hospital_id'] ?? null;
            if ($hospital_id) {
                $sql = "SELECT MIN(id) as id, name, MIN(description) as description, hospital_id 
                        FROM departments 
                        WHERE hospital_id = :hospital_id 
                        GROUP BY name 
                        ORDER BY name ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':hospital_id' => $hospital_id]);
            } else {
                $sql = "SELECT MIN(id) as id, name, MIN(description) as description, hospital_id 
                        FROM departments 
                        GROUP BY name 
                        ORDER BY name ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            $this->jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Fetches list of registered doctors.
     * Integrates custom base-url paths for images and implements fallback placeholders.
     */
    public function doctors() {
        try {
            $hospital_id = $_GET['hospital_id'] ?? null;
            $sql = "
                SELECT u.id, u.name, d.specialization as specialty, d.profile_image as image, 
                       u.email, d.phone, d.rating, d.biography as bio,
                       d.reviews_count
                FROM users u 
                JOIN doctors_details d ON u.id = d.user_id 
                JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id
                WHERE u.role = 'doctor'
            ";
            if ($hospital_id) $sql .= " AND dha.hospital_id = :hospital_id";
            $sql .= " GROUP BY u.id ORDER BY d.rating DESC";
            $stmt = $this->db->prepare($sql);
            if ($hospital_id) $stmt->bindParam(':hospital_id', $hospital_id);
            $stmt->execute();
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Construct dynamic host URL to serve images globally
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                     . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                     . rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/\\');

            $fallbacks = [
                'assets/uploads/dr-fatima-zahra.jpg',
                'assets/uploads/dr-muhammad-bilal.jpg',
                'assets/uploads/dr-aisha-noor.jpg',
                'assets/uploads/dr-usman-tariq.jpg',
                'assets/uploads/dr-zainab-malik.jpg',
                'assets/uploads/dr-abdul-rahman.jpg',
            ];

            // Map static image URLs and handle placeholders
            foreach ($doctors as $idx => &$doctor) {
                $img = $doctor['image'];
                if (!empty($img)) {
                    $doctor['image'] = $baseUrl . '/' . ltrim($img, '/');
                } else {
                    $fb = $fallbacks[$idx % count($fallbacks)];
                    $doctor['image'] = $baseUrl . '/' . $fb;
                }
            }
            unset($doctor);
            $this->jsonResponse($doctors);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Returns dynamic listing of medical services (Departments).
     */
    public function services() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT name, description FROM departments LIMIT 6");
            $this->jsonResponse(array_map(function($dept) {
                return [
                    'name' => $dept['name'],
                    'description' => $dept['description'] ?? 'Expert care provided.',
                    'icon' => 'assets/service-icon-default.svg'
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC)));
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Books an appointment.
     * Operates inside a secure database transaction:
     * 1. Validates details.
     * 2. Auto-registers the user as a Patient if they don't already exist (by phone lookup).
     * 3. Calculates token number (queue order) for the selected day.
     * 4. Persists the appointment and triggers notifications.
     */
    public function appointment() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = $input['name'] ?? null; 
        $phone = $input['phone'] ?? null;
        $doctorId = !empty($input['doctorId']) ? $input['doctorId'] : null; 
        $hospitalId = $input['hospitalId'] ?? null; 
        $date = $input['date'] ?? null;
        $timeSlot = $input['time'] ?? null;

        if (!$name || !$phone || !$date || !$hospitalId) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields: name, phone, date, and hospital are required'], 400);
        }

        // Validate hospital exists before starting transaction
        try {
            $chk = $this->db->prepare("SELECT id FROM hospitals WHERE id = :hid LIMIT 1");
            $chk->execute([':hid' => $hospitalId]);
            if (!$chk->fetch()) {
                $this->jsonResponse(['success' => false, 'message' => 'Selected hospital not found'], 404);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Database error validating hospital'], 500);
        }

        try {
            $this->db->beginTransaction();

            // Validate that the doctor is actually a doctor
            if ($doctorId) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE id = :id AND role='doctor'");
                $stmt->execute([':id' => $doctorId]);
                if (!$stmt->fetch()) {
                     $this->db->rollBack(); 
                     $this->jsonResponse(['success' => false, 'message' => "Doctor not found"], 404);
                }
            }

            // Look up user by phone number, or create a minimal patient record
            $stmt = $this->db->prepare("SELECT id FROM users WHERE phone = :phone LIMIT 1");
            $stmt->execute([':phone' => $phone]);
            $userId = $stmt->fetchColumn();
            if (!$userId) {
                $password = password_hash("123456", PASSWORD_DEFAULT);
                $emailInput = !empty($input['email']) ? trim($input['email']) : null;
                $dummyEmail = $emailInput ?: ("patient_" . preg_replace('/[^0-9]/', '', $phone) . "_" . time() . "@mednova.pk");
                $stmt = $this->db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :password, 'patient')");
                $stmt->execute([':name' => $name, ':phone' => $phone, ':email' => $dummyEmail, ':password' => $password]);
                $userId = $this->db->lastInsertId();
            } else {
                // Update patient name in case it changed
                $this->db->prepare("UPDATE users SET name = :name WHERE id = :id")->execute([':name' => $name, ':id' => $userId]);
            }

            // Ensure patient profile is fully bound to the patient_details table
            $stmt = $this->db->prepare("SELECT id FROM patients_details WHERE user_id = :uid");
            $stmt->execute([':uid' => $userId]);
            $patientId = $stmt->fetchColumn();
            if (!$patientId) {
                $stmt = $this->db->prepare("INSERT INTO patients_details (user_id, name, phone, age, gender) VALUES (:uid, :name, :phone, 0, 'Other')");
                $stmt->execute([':uid' => $userId, ':name' => $name, ':phone' => $phone]);
                $patientId = $this->db->lastInsertId();
            }

            // Compute queue token number using hospital scope for consistency
            $type = $input['type'] ?? 'consultation';
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = :hid AND date = :date");
            $stmt->execute([':hid' => $hospitalId, ':date' => $date]);
            $token = (int)$stmt->fetchColumn() + 1;

            // Record the appointment — doctor_id may be NULL for walk-in/hospital-level bookings
            $stmt = $this->db->prepare(
                "INSERT INTO appointments (patient_id, doctor_id, hospital_id, token_number, date, time_slot, status, type)
                 VALUES (:pid, :did, :hid, :token, :date, :time_slot, 'pending', :type)"
            );
            $stmt->execute([
                ':pid'       => $patientId,
                ':did'       => $doctorId,
                ':hid'       => $hospitalId,
                ':token'     => $token,
                ':date'      => $date,
                ':time_slot' => $timeSlot,
                ':type'      => $type
            ]);
            $appointmentId = $this->db->lastInsertId();
            $this->db->commit();

            // Send notification triggers
            if ($hospitalId) {
                try {
                    // Save in-app notification for hospital dashboard
                    $notifStmt = $this->db->prepare(
                        "INSERT INTO notifications (hospital_id, type, title, message, link) VALUES (:hid, 'appointment', :title, :msg, '?route=hospital_admin/appointments')"
                    );
                    $notifStmt->execute([
                        ':hid'   => $hospitalId,
                        ':title' => 'New Appointment Booked',
                        ':msg'   => "$name booked an appointment on $date (Token #$token)"
                    ]);
                    
                    // Dispatch email notification to hospital administrator
                    try {
                        if ($this->isEmailEnabledForHospital($hospitalId)) {
                            $stmt = $this->db->prepare("SELECT u.email FROM hospitals h JOIN users u ON h.user_id = u.id WHERE h.id = :hid LIMIT 1");
                            $stmt->execute([':hid' => $hospitalId]);
                            $adminEmail = $stmt->fetchColumn();
                            if ($adminEmail) {
                                $title = 'New Appointment Booked';
                                $msg = "$name booked an appointment on $date (Token #$token)";
                                $html = "<p>{$msg}</p><p><a href='" . (isset($_SERVER['HTTP_HOST']) ? ('http://' . $_SERVER['HTTP_HOST']) : '') . "/backend/?route=hospital_admin/appointments'>View Appointments</a></p>";
                                $this->sendEmailNotification($adminEmail, $title, $html);
                            }
                        }
                    } catch (Exception $ee) { /* Fail silently */ }
                } catch (Exception $ne) { /* Non-critical notification error */ }
            }

            $this->jsonResponse(['success' => true, 'message' => 'Appointment booked successfully', 'token' => $token, 'appointment_id' => $appointmentId]);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Records public contact form inquiries.
     */
    public function contact() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? ''; $phone = $input['phone'] ?? ''; $message = $input['message'] ?? '';
        
        if (!$name || !$phone || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO contact_messages (name, phone, subject, message) VALUES (:name, :phone, 'General Inquiry', :msg)");
            $stmt->execute([':name' => $name, ':phone' => $phone, ':msg' => $message]);
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully']);
        } catch (Exception $e) { 
            $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500); 
        }
    }

    /**
     * API: Handles Diet Plan generation requests.
     * Records goals, body stats (age, weight, height), conditions, and routes to selected hospital.
     */
    public function dietPlan() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name       = $input['name'] ?? null;
        $phone      = $input['phone'] ?? null;
        $hospitalId = $input['hospitalId'] ?? null ?: null;
        $doctorId   = $input['doctorId'] ?? null ?: null;
        $age        = $input['age'] ?? null ?: null;
        $weight     = $input['weight'] ?? null ?: null;
        $height     = $input['height'] ?? null ?: null;
        $goal       = $input['goal'] ?? null;
        $conditions = $input['conditions'] ?? '';

        if (!$name || !$phone || !$goal) {
            $this->jsonResponse(['success' => false, 'message' => 'Please fill in your name, phone number and health goal'], 400);
        }

        try {
            // Save diet plan request record into the database
            $sql = "INSERT INTO diet_plans (hospital_id, doctor_id, patient_name, patient_phone, age, weight, height, goal, conditions, status) 
                    VALUES (:hid, :did, :name, :phone, :age, :weight, :height, :goal, :cond, 'pending')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hid'    => $hospitalId,
                ':did'    => $doctorId,
                ':name'   => $name,
                ':phone'  => $phone,
                ':age'    => $age,
                ':weight' => $weight,
                ':height' => $height,
                ':goal'   => $goal,
                ':cond'   => $conditions
            ]);

            // Notify the corresponding hospital
            if ($hospitalId) {
                try {
                    $goalLabel = str_replace('_', ' ', ucfirst($goal));
                    $notifStmt = $this->db->prepare(
                        "INSERT INTO notifications (hospital_id, type, title, message, link) VALUES (:hid, 'diet_plan', :title, :msg, '?route=hospital_admin/diet_plans')"
                    );
                    $notifStmt->execute([
                        ':hid'   => $hospitalId,
                        ':title' => 'New Diet Plan Request',
                        ':msg'   => "$name requested a $goalLabel diet plan"
                    ]);
                    
                    // Dispatch email notification to hospital administrator
                    try {
                        if ($this->isEmailEnabledForHospital($hospitalId)) {
                            $s = $this->db->prepare("SELECT u.email FROM hospitals h JOIN users u ON h.user_id = u.id WHERE h.id = :hid LIMIT 1");
                            $s->execute([':hid' => $hospitalId]);
                            $adminEmail = $s->fetchColumn();
                            if ($adminEmail) {
                                $title = 'New Diet Plan Request';
                                $msg = "$name requested a $goalLabel diet plan";
                                $html = "<p>{$msg}</p><p><a href='" . (isset($_SERVER['HTTP_HOST']) ? ('http://' . $_SERVER['HTTP_HOST']) : '') . "/backend/?route=hospital_admin/diet_plans'>View Diet Plans</a></p>";
                                $this->sendEmailNotification($adminEmail, $title, $html);
                            }
                        }
                    } catch (Exception $ee) {}
                } catch (Exception $ne) {
                    // Prevent notifications failure from stopping the operation
                }
            }

            $this->jsonResponse(['success' => true, 'message' => 'Diet plan request submitted successfully']);
        } catch (Exception $e) {
            file_put_contents('api_debug.log', "[" . date('Y-m-d H:i:s') . "] DietPlan Error: " . $e->getMessage() . "\nPayload: " . json_encode($input) . "\n", FILE_APPEND);
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Registers blood donors linked optionally to a hospital network.
     */
    public function registerDonor() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = $input['name'] ?? null; 
        $bg = $input['blood_group'] ?? null; 
        $phone = $input['phone'] ?? null;
        $hospital_id = $input['hospitalId'] ?? null;
        
        if (!$name || !$bg || !$phone) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO blood_donors (name, age, blood_group, phone, location, hospital_id) VALUES (:name, :age, :bg, :phone, :loc, :hid)");
            $stmt->execute([':name' => $name, ':age' => $input['age']??null, ':bg' => $bg, ':phone' => $phone, ':loc' => $input['location']??null, ':hid' => $hospital_id]);
            $this->jsonResponse(['success' => true, 'message' => 'Donor registered successfully']);
        } catch (Exception $e) { 
            $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500); 
        }
    }

    /**
     * API: Broadcasts emergency blood requests.
     * Logs request, links to network, and triggers alert notifications.
     */
    public function requestBlood() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = $input['patient_name'] ?? null; 
        $bg = $input['blood_group'] ?? null; 
        $phone = $input['phone'] ?? null;
        $hospital_id = $input['hospitalId'] ?? null;
        
        if (!$name || !$bg || !$phone) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO blood_requests (patient_name, blood_group, urgency, phone, hospital_id) VALUES (:name, :bg, :urgency, :phone, :hid)");
            $stmt->execute([':name' => $name, ':bg' => $bg, ':urgency' => $input['urgency']??'Normal', ':phone' => $phone, ':hid' => $hospital_id]);

            // Notify target hospital system
            if ($hospital_id) {
                try {
                    $urgency = $input['urgency'] ?? 'Normal';
                    $notifStmt = $this->db->prepare(
                        "INSERT INTO notifications (hospital_id, type, title, message, link) VALUES (:hid, 'blood_request', :title, :msg, '?route=hospital_admin/blood_requests')"
                    );
                    $notifStmt->execute([
                        ':hid'   => $hospital_id,
                        ':title' => 'New Blood Request',
                        ':msg'   => "$name needs $bg blood ($urgency urgency)"
                    ]);
                    
                    // Dispatch email notification to hospital administrator
                    try {
                        if ($this->isEmailEnabledForHospital($hospital_id)) {
                            $s = $this->db->prepare("SELECT u.email FROM hospitals h JOIN users u ON h.user_id = u.id WHERE h.id = :hid LIMIT 1");
                            $s->execute([':hid' => $hospital_id]);
                            $adminEmail = $s->fetchColumn();
                            if ($adminEmail) {
                                $title = 'New Blood Request';
                                $msg = "$name needs $bg blood ($urgency urgency)";
                                $html = "<p>{$msg}</p><p><a href='" . (isset($_SERVER['HTTP_HOST']) ? ('http://' . $_SERVER['HTTP_HOST']) : '') . "/backend/?route=hospital_admin/blood_requests'>View Blood Requests</a></p>";
                                $this->sendEmailNotification($adminEmail, $title, $html);
                            }
                        }
                    } catch (Exception $ee) { }
                } catch (Exception $ne) { /* Non-critical notification failure */ }
            }

            $this->jsonResponse(['success' => true, 'message' => 'Blood request broadcasted successfully']);
        } catch (Exception $e) { 
            $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500); 
        }
    }

    /**
     * API: Saves health assessment results to profile.
     */
    public function saveAssessment() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $bmiScore = $input['bmi_score'] ?? null;
        $bmiCategory = $input['bmi_category'] ?? null;
        $caloriesTarget = $input['calories_target'] ?? null;
        $waterTarget = $input['water_target'] ?? null;

        try {
            // Check if user already has an assessment record
            $stmt = $this->db->prepare("SELECT id FROM health_assessments WHERE user_id = :uid LIMIT 1");
            $stmt->execute([':uid' => $_SESSION['user_id']]);
            $id = $stmt->fetchColumn();

            if ($id) {
                // Update existing record
                $sql = "UPDATE health_assessments SET ";
                $updates = [];
                $params = [':id' => $id];
                if ($bmiScore !== null) { $updates[] = "bmi_score = :bmi"; $params[':bmi'] = $bmiScore; }
                if ($bmiCategory !== null) { $updates[] = "bmi_category = :cat"; $params[':cat'] = $bmiCategory; }
                if ($caloriesTarget !== null) { $updates[] = "calories_target = :cal"; $params[':cal'] = $caloriesTarget; }
                if ($waterTarget !== null) { $updates[] = "water_target = :water"; $params[':water'] = $waterTarget; }
                
                if (!empty($updates)) {
                    $sql .= implode(", ", $updates) . " WHERE id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);
                }
            } else {
                // Insert new record
                $stmt = $this->db->prepare("INSERT INTO health_assessments (user_id, bmi_score, bmi_category, calories_target, water_target) VALUES (:uid, :bmi, :cat, :cal, :water)");
                $stmt->execute([
                    ':uid' => $_SESSION['user_id'],
                    ':bmi' => $bmiScore,
                    ':cat' => $bmiCategory,
                    ':cal' => $caloriesTarget,
                    ':water' => $waterTarget
                ]);
            }
            $this->jsonResponse(['success' => true, 'message' => 'Assessment saved successfully']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Database error'], 500);
        }
    }
}

