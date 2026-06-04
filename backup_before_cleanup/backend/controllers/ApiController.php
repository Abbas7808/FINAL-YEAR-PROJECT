<?php

class ApiController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->handleCors();
    }

    private function handleCors() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function index() {
        $this->jsonResponse(['message' => 'Med-Nova API v1']);
    }

    public function regions() {
        try {
            $stmt = $this->db->query("SELECT * FROM regions ORDER BY name ASC");
            $this->jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function hospitals() {
        try {
            $region_id = $_GET['region_id'] ?? null;
            $slug = $_GET['slug'] ?? null;
            $id = $_GET['id'] ?? null;
            $email = $_GET['email'] ?? null;
            $search = $_GET['search'] ?? null;

            $sql = "SELECT h.*, u.email as admin_email FROM hospitals h LEFT JOIN users u ON h.user_id = u.id WHERE h.subscription_status = 'active'";
            $params = [];

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

            // Build absolute base URL for images
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

            foreach ($doctors as $idx => &$doctor) {
                $img = $doctor['image'];
                if (!empty($img)) {
                    // Make the image path absolute
                    $doctor['image'] = $baseUrl . '/' . ltrim($img, '/');
                } else {
                    // Use round-robin fallback
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

    public function appointment() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? null; $phone = $input['phone'] ?? null;
        $doctorId = $input['doctorId'] ?? null ?: null; $hospitalId = $input['hospitalId'] ?? null; $date = $input['date'] ?? null;
        $timeSlot = $input['time'] ?? null;
        if (!$name || !$phone || !$date || !$hospitalId) $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);

        try {
            $this->db->beginTransaction();
            if ($doctorId) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE id = :id AND role='doctor'");
                $stmt->execute([':id' => $doctorId]);
                if (!$stmt->fetch()) {
                     $this->db->rollBack(); $this->jsonResponse(['success' => false, 'message' => "Doctor not found"], 404);
                }
            }
            $stmt = $this->db->prepare("SELECT id FROM users WHERE phone = :phone");
            $stmt->execute([':phone' => $phone]);
            $userId = $stmt->fetchColumn();
            if (!$userId) {
                $password = password_hash("123456", PASSWORD_DEFAULT);
                $dummyEmail = "patient_" . preg_replace('/[^0-9]/', '', $phone) . "_" . time() . "@mednova.pk";
                $stmt = $this->db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :password, 'patient')");
                $stmt->execute([':name' => $name, ':phone' => $phone, ':email' => $dummyEmail, ':password' => $password]);
                $userId = $this->db->lastInsertId();
            }
            $stmt = $this->db->prepare("SELECT id FROM patients_details WHERE user_id = :uid");
            $stmt->execute([':uid' => $userId]);
            $patientId = $stmt->fetchColumn();
            if (!$patientId) {
                $stmt = $this->db->prepare("INSERT INTO patients_details (user_id, name, phone, age, gender) VALUES (:uid, :name, :phone, 0, 'Other')");
                $stmt->execute([':uid' => $userId, ':name' => $name, ':phone' => $phone]);
                $patientId = $this->db->lastInsertId();
            }
            $type = $input['type'] ?? 'consultation';
            if ($doctorId) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = :did AND date = :date");
                $stmt->execute([':did' => $doctorId, ':date' => $date]);
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM appointments WHERE hospital_id = :hid AND date = :date");
                $stmt->execute([':hid' => $hospitalId, ':date' => $date]);
            }
            $token = $stmt->fetchColumn() + 1;
            $stmt = $this->db->prepare("INSERT INTO appointments (patient_id, doctor_id, hospital_id, token_number, date, time_slot, status, type) VALUES (:pid, :did, :hid, :token, :date, :time_slot, 'pending', :type)");
            $stmt->execute([':pid' => $patientId, ':did' => $doctorId, ':hid' => $hospitalId, ':token' => $token, ':date' => $date, ':time_slot' => $timeSlot, ':type' => $type]);
            $this->db->commit();

            // Notify the hospital
            if ($hospitalId) {
                try {
                    $notifStmt = $this->db->prepare(
                        "INSERT INTO notifications (hospital_id, type, title, message, link) VALUES (:hid, 'appointment', :title, :msg, '?route=hospital_admin/appointments')"
                    );
                    $notifStmt->execute([
                        ':hid'   => $hospitalId,
                        ':title' => 'New Appointment Booked',
                        ':msg'   => "$name booked an appointment on $date (Token #$token)"
                    ]);
                    
                    // Try sending email to hospital admin (non-critical)
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
                    } catch (Exception $ee) { /* ignore email errors */ }
                } catch (Exception $ne) { /* non-critical */ }
            }

            $this->jsonResponse(['success' => true, 'message' => 'Appointment booked successfully']);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function contact() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? ''; $phone = $input['phone'] ?? ''; $message = $input['message'] ?? '';
        if (!$name || !$phone || !$message) $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        try {
            $stmt = $this->db->prepare("INSERT INTO contact_messages (name, phone, subject, message) VALUES (:name, :phone, 'General Inquiry', :msg)");
            $stmt->execute([':name' => $name, ':phone' => $phone, ':msg' => $message]);
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully']);
        } catch (Exception $e) { $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500); }
    }

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

            // Send notification to the hospital if one was selected
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
                    // Email the hospital admin
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
                    // Notification failure shouldn't fail the whole request
                }
            }

            $this->jsonResponse(['success' => true, 'message' => 'Diet plan request submitted successfully']);
        } catch (Exception $e) {
            file_put_contents('api_debug.log', "[" . date('Y-m-d H:i:s') . "] DietPlan Error: " . $e->getMessage() . "\nPayload: " . json_encode($input) . "\n", FILE_APPEND);
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function registerDonor() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? null; $bg = $input['blood_group'] ?? null; $phone = $input['phone'] ?? null;
        $hospital_id = $input['hospitalId'] ?? null;
        if (!$name || !$bg || !$phone) $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        try {
            $stmt = $this->db->prepare("INSERT INTO blood_donors (name, age, blood_group, phone, location, hospital_id) VALUES (:name, :age, :bg, :phone, :loc, :hid)");
            $stmt->execute([':name' => $name, ':age' => $input['age']??null, ':bg' => $bg, ':phone' => $phone, ':loc' => $input['location']??null, ':hid' => $hospital_id]);
            $this->jsonResponse(['success' => true, 'message' => 'Donor registered successfully']);
        } catch (Exception $e) { $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500); }
    }

    public function requestBlood() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['patient_name'] ?? null; $bg = $input['blood_group'] ?? null; $phone = $input['phone'] ?? null;
        $hospital_id = $input['hospitalId'] ?? null;
        if (!$name || !$bg || !$phone) $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        try {
            $stmt = $this->db->prepare("INSERT INTO blood_requests (patient_name, blood_group, urgency, phone, hospital_id) VALUES (:name, :bg, :urgency, :phone, :hid)");
            $stmt->execute([':name' => $name, ':bg' => $bg, ':urgency' => $input['urgency']??'Normal', ':phone' => $phone, ':hid' => $hospital_id]);

            // Notify the hospital
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
                    // Email hospital admin
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
                } catch (Exception $ne) { /* non-critical */ }
            }

            $this->jsonResponse(['success' => true, 'message' => 'Blood request broadcasted successfully']);
        } catch (Exception $e) { $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500); }
    }
}
