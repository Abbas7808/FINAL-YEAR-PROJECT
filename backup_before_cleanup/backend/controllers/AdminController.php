<?php
/**
 * ==========================================================================
 * SUPER ADMIN CONTROLLER (AdminController.php)
 * ==========================================================================
 * This controller manages the entire Med Nova platform. It handles:
 * 1. Global Dashboard Analytics & Stats
 * 2. Doctor Management (Approval, Verification, Lists)
 * 3. Hospital & Clinic Management (Subscriptions, Data)
 * 4. Regional & Settings Configuration
 * ==========================================================================
 */

class AdminController extends Controller {
    /* --- CONSTRUCTOR & AUTH SHIELD --- */
    public function __construct() {
        parent::__construct();
        // Shield: Only authenticated super-admins can access this controller
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            $this->redirect('?route=auth/login');
        }
    }

    /* --- 1. GLOBAL DASHBOARD ANALYTICS --- */
    public function dashboard() {
        // 1. Total Hospitals
        $stmt = $this->db->query("SELECT COUNT(*) FROM hospitals WHERE type = 'Hospital'");
        $data['total_hospitals'] = $stmt->fetchColumn();

        // 2. Total Clinics
        $stmt = $this->db->query("SELECT COUNT(*) FROM hospitals WHERE type = 'Clinic'");
        $data['total_clinics'] = $stmt->fetchColumn();

        // 3. Active Subscriptions
        $stmt = $this->db->query("SELECT COUNT(*) FROM hospitals WHERE subscription_status = 'active'");
        $data['active_subscriptions'] = $stmt->fetchColumn();

        // 4. Total Doctors
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'");
        $data['total_doctors'] = $stmt->fetchColumn();

        // 4. Recent Hospitals (Top 5)
        $stmt = $this->db->query("SELECT * FROM hospitals ORDER BY created_at DESC LIMIT 5");
        $data['recent_hospitals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Recent Doctors (Top 5)
        $stmt = $this->db->query("SELECT u.name, u.email, d.specialization FROM users u JOIN doctors_details d ON u.id = d.user_id WHERE u.role = 'doctor' ORDER BY u.created_at DESC LIMIT 5");
        $data['recent_doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['page_title'] = "Super Admin Dashboard";
        $this->view('admin/dashboard', $data);
    }



    /* --- 2. DOCTOR MANAGEMENT (SUPER ADMIN OVERVIEW) --- */
    public function doctors() {
        $filter_type = $_GET['type'] ?? ''; // Hospital or Clinic
        $filter_hospital_id = $_GET['hospital_id'] ?? '';
        
        $sql = "SELECT d.*, u.email, u.name, u.role, h.name as hospital_name, h.type as hospital_type 
                FROM doctors_details d 
                JOIN users u ON d.user_id = u.id 
                LEFT JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id 
                LEFT JOIN hospitals h ON dha.hospital_id = h.id 
                WHERE 1=1";
        
        $params = [];

        if (!empty($filter_type)) {
            $sql .= " AND h.type = :type";
            $params[':type'] = $filter_type;
        }

        if (!empty($filter_hospital_id)) {
            $sql .= " AND h.id = :hid";
            $params[':hid'] = $filter_hospital_id;
        }

        $sql .= " ORDER BY u.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data['doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch Hospitals/Clinics for Filter Dropdown
        $stmt = $this->db->query("SELECT id, name, type FROM hospitals ORDER BY name ASC");
        $data['hospitals_list'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['page_title'] = "Doctors List";
        $data['filter_type'] = $filter_type;
        $data['filter_hospital_id'] = $filter_hospital_id;

        $this->view('admin/doctors', $data);
    }

    public function doctor_details() {
        if (!isset($_GET['id'])) $this->redirect('?route=admin/doctors');
        $id = $_GET['id'];
        
        $stmt = $this->db->prepare("SELECT d.*, u.email, u.name, u.role, u.created_at as join_date FROM doctors_details d JOIN users u ON d.user_id = u.id WHERE d.id = :id");
        $stmt->execute([':id' => $id]);
        $data['doctor'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Initialize empty reviews array for now (To be implemented with Reviews table later)
        $data['reviews'] = [];
        
        $data['page_title'] = "Doctor Details";
        $this->view('admin/doctor_details', $data);
    }

    public function add_doctor() {
        // Fetch Hospitals for Dropdown
        $stmt = $this->db->query("SELECT * FROM hospitals ORDER BY name ASC");
        $data['hospitals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data['page_title'] = "Add New Doctor";
        $this->view('admin/add_doctor', $data);
    }

    public function store_doctor() {
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             $name = $_POST['name'];
             $email = $_POST['email'];
             $phone = $_POST['phone'];
             $password = $_POST['password'];
             $specialization = $_POST['specialization'];
             $phone_details = $_POST['phone_details'] ?? '';
             $address = $_POST['address'];
             $bio = $_POST['biography'];
             $rating = $_POST['rating'] ?? 5.0;
             $hospital_id = $_POST['hospital_id'] ?? null;

             // Create User
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $stmt = $this->db->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (:name, :email, :phone, :pass, 'doctor')");
             $stmt->execute([':name' => $name, ':email' => $email, ':phone' => $phone, ':pass' => $hashed_password]);
             $user_id = $this->db->lastInsertId();

             // Create Doctor Details
             $stmt = $this->db->prepare("INSERT INTO doctors_details (user_id, specialization, phone, address, biography, rating, joining_date) VALUES (:uid, :spec, :phone, :addr, :bio, :rating, NOW())");
             $stmt->execute([
                 ':uid' => $user_id, 
                 ':spec' => $specialization,
                 ':phone' => $phone_details ?: $phone,
                 ':addr' => $address,
                 ':bio' => $bio,
                 ':rating' => $rating
             ]);
             
             // Create Hospital Affiliation
             if ($hospital_id) {
                 $stmt = $this->db->prepare("INSERT INTO doctor_hospital_affiliations (doctor_id, hospital_id) VALUES (:did, :hid)");
                 $stmt->execute([':did' => $user_id, ':hid' => $hospital_id]);
             }

             $this->redirect('?route=admin/doctors');
         }
    }

    public function edit_doctor() {
        if (!isset($_GET['id'])) $this->redirect('?route=admin/doctors');
        $id = $_GET['id'];
        
        $sql = "SELECT d.*, u.email, u.name as user_name, u.phone as auth_phone, dha.hospital_id 
                FROM doctors_details d 
                JOIN users u ON d.user_id = u.id 
                LEFT JOIN doctor_hospital_affiliations dha ON u.id = dha.doctor_id 
                WHERE d.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data['doctor'] = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt2 = $this->db->query("SELECT * FROM hospitals ORDER BY name ASC");
        $data['hospitals'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        $data['page_title'] = "Edit Doctor Profile";
        $this->view('admin/edit_doctor', $data);
    }

    public function update_doctor() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $user_id = $_POST['user_id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $phone_details = $_POST['phone_details'];
            $specialization = $_POST['specialization'];
            $address = $_POST['address'];
            $bio = $_POST['biography'];
            $rating = $_POST['rating'] ?? 5.0;
            $hospital_id = $_POST['hospital_id'] ?? null;
            $password = $_POST['password'] ?? '';

            try {
                $this->db->beginTransaction();
                
                // Update User
                $u_sql = "UPDATE users SET name = :name, email = :email, phone = :phone";
                $u_params = [':name' => $name, ':email' => $email, ':phone' => $phone, ':uid' => $user_id];
                if (!empty($password)) {
                    $u_sql .= ", password = :pass";
                    $u_params[':pass'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $u_sql .= " WHERE id = :uid";
                $stmt = $this->db->prepare($u_sql);
                $stmt->execute($u_params);

                // Update Doctor Details
                $d_sql = "UPDATE doctors_details SET specialization = :spec, phone = :phone_det, address = :addr, biography = :bio, rating = :rating WHERE id = :id";
                $stmt = $this->db->prepare($d_sql);
                $stmt->execute([':spec' => $specialization, ':phone_det' => $phone_details, ':addr' => $address, ':bio' => $bio, ':rating' => $rating, ':id' => $id]);

                // Transfer / Assign Hospital Affiliation
                // Delete old affiliation first
                $stmt = $this->db->prepare("DELETE FROM doctor_hospital_affiliations WHERE doctor_id = :uid");
                $stmt->execute([':uid' => $user_id]);
                
                if ($hospital_id) {
                    $stmt = $this->db->prepare("INSERT INTO doctor_hospital_affiliations (doctor_id, hospital_id) VALUES (:uid, :hid)");
                    $stmt->execute([':uid' => $user_id, ':hid' => $hospital_id]);
                }

                $this->db->commit();
                $_SESSION['success'] = "Doctor profile updated and transferred successfully.";
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = "Failed to update doctor: " . $e->getMessage();
            }
            $this->redirect('?route=admin/doctors');
        }
    }

    public function delete_doctor() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            try {
                $this->db->beginTransaction();
                $stmt = $this->db->prepare("SELECT user_id FROM doctors_details WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $user_id = $stmt->fetchColumn();

                if ($user_id) {
                    $st1 = $this->db->prepare("DELETE FROM doctor_hospital_affiliations WHERE doctor_id = :uid");
                    $st1->execute([':uid' => $user_id]);
                    
                    $st2 = $this->db->prepare("DELETE FROM doctors_details WHERE id = :id");
                    $st2->execute([':id' => $id]);

                    $st3 = $this->db->prepare("DELETE FROM users WHERE id = :uid AND role='doctor'");
                    $st3->execute([':uid' => $user_id]);
                    
                    $_SESSION['success'] = "Doctor permanently deleted.";
                }
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = "Could not delete doctor.";
            }
        }
        $this->redirect('?route=admin/doctors');
    }

    public function store_receptionist() {
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
             $name = $_POST['name'];
             $phone = $_POST['phone'];
             $password = $_POST['password'];

             // Create User
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $stmt = $this->db->prepare("INSERT INTO users (name, phone, password, role) VALUES (:name, :phone, :pass, 'receptionist')");
             $stmt->execute([':name' => $name, ':phone' => $phone, ':pass' => $hashed_password]);

             $this->redirect('?route=admin/receptionists');
         }
    }

    public function receptionists() {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'receptionist' ORDER BY created_at DESC");
        $data['receptionists'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Manage Receptionists";
        $this->view('admin/receptionists', $data);
    }

    public function delete_receptionist() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id AND role = 'receptionist'");
            if ($stmt->execute()) {
                $_SESSION['success'] = "Receptionist deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete receptionist.";
            }
        }
        $this->redirect('?route=admin/receptionists');
    }

    // --- Region Management ---
    public function regions() {
        $stmt = $this->db->query("SELECT * FROM regions ORDER BY created_at DESC");
        $data['regions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['page_title'] = "Manage Regions";
        $this->view('admin/regions', $data);
    }

    public function store_region() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            if(!empty($name)){
                $stmt = $this->db->prepare("INSERT INTO regions (name, created_at) VALUES (:name, NOW())");
                $stmt->execute([':name' => $name]);
            }
            $this->redirect('?route=admin/regions');
        }
    }

    public function delete_region() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $this->db->prepare("DELETE FROM regions WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        $this->redirect('?route=admin/regions');
    }

    // --- Hospital Management ---
    // --- Hospital Management ---
    public function hospitals() {
        // Fetch Only Hospitals
        $stmt = $this->db->query("SELECT h.*, r.name as region_name FROM hospitals h LEFT JOIN regions r ON h.region_id = r.id WHERE h.type = 'Hospital' ORDER BY h.id DESC");
        $data['hospitals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("SELECT * FROM regions ORDER BY name ASC");
        $data['regions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['page_title'] = "Manage Hospitals";
        $this->view('admin/hospitals', $data);
    }

    public function clinics() {
        // Fetch Only Clinics
        $stmt = $this->db->query("SELECT h.*, r.name as region_name FROM hospitals h LEFT JOIN regions r ON h.region_id = r.id WHERE h.type = 'Clinic' ORDER BY h.id DESC");
        $data['clinics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("SELECT * FROM regions ORDER BY name ASC");
        $data['regions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['page_title'] = "Manage Clinics";
        $this->view('admin/clinics', $data);
    }

    public function store_hospital() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $password = $_POST['password'];
            $region_id = $_POST['region_id'];
            $address = $_POST['address'];
            $contact = $_POST['contact_info'];
            $description = $_POST['description'] ?? '';
            $type = $_POST['type'] ?? 'Hospital';
            
            // Slug Generation
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            
            // Image Upload
            $image_path = '';
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
                $target_dir = "assets/uploads/hospitals/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $new_filename = $slug . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                }
            }

            // Default Subscription
            $plan_type = 'monthly';
            $status = 'active';
            $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

            if(!empty($name) && !empty($phone) && !empty($password)){
                try {
                    $this->db->beginTransaction();

                    // 1. Create User
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $email = $slug . "@mednova.pk";
                    $checkEmail = $this->db->prepare("SELECT id FROM users WHERE email = :email AND role = 'hospital_admin'");
                    $checkEmail->execute([':email' => $email]);
                    if ($checkEmail->fetch()) {
                        $email = $slug . "_" . time() . "@mednova.pk";
                    }
                    
                    $stmt = $this->db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :pass, 'hospital_admin')");
                    $stmt->execute([':name' => $name, ':phone' => $phone, ':email' => $email, ':pass' => $hashed_password]);
                    $user_id = $this->db->lastInsertId();

                    // 2. Create Hospital
                    $stmt = $this->db->prepare("INSERT INTO hospitals (name, slug, type, region_id, address, contact_info, description, image, user_id, subscription_status, plan_type, plan_expires_at) VALUES (:name, :slug, :type, :rid, :addr, :contact, :desc, :img, :uid, :status, :plan, :exp)");
                    $stmt->execute([
                        ':name' => $name,
                        ':slug' => $slug,
                        ':type' => $type,
                        ':rid' => $region_id,
                        ':addr' => $address,
                        ':contact' => $contact,
                        ':desc' => $description,
                        ':img' => $image_path,
                        ':uid' => $user_id,
                        ':status' => $status,
                        ':plan' => $plan_type,
                        ':exp' => $expires_at
                    ]);

                    $this->db->commit();
                    $login_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/?route=auth/login&slug=" . $slug;
                    $_SESSION['success'] = "<b>" . ($type ?? 'Hospital') . " Created Successfully!</b><br>Login URL: <a href='$login_url' target='_blank'>$login_url</a>";
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $_SESSION['error'] = "Error creating account: " . $e->getMessage();
                }
            } else {
                 $_SESSION['error'] = "All fields are required!";
            }
            if ($type == 'Clinic') {
                $this->redirect('?route=admin/clinics');
            } else {
                $this->redirect('?route=admin/hospitals');
            }
        }
    }

    public function delete_hospital() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $this->db->prepare("DELETE FROM hospitals WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        // Redirect back to referring page or default to hospitals
        if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'clinics') !== false) {
             $this->redirect('?route=admin/clinics');
        } else {
             $this->redirect('?route=admin/hospitals');
        }
    }

    public function update_hospital() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $address = $_POST['address'];
            $contact = $_POST['contact_info'];
            $description = $_POST['description'];
            $region_id = $_POST['region_id'];
            
            // Slug update (optional, usually kept static to avoid breaking links, but let's allow re-generation if name changes drastically or keep same)
            // For now, let's keep slug static or manual update? Let's just update other fields.
            
            $sql = "UPDATE hospitals SET name = :name, address = :addr, contact_info = :contact, description = :desc, region_id = :rid";
            $params = [
                ':name' => $name,
                ':addr' => $address,
                ':contact' => $contact,
                ':desc' => $description,
                ':rid' => $region_id,
                ':id' => $id
            ];

            // Image Upload
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
                $target_dir = "assets/uploads/hospitals/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $new_filename = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))) . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $sql .= ", image = :img";
                    $params[':img'] = $target_file;
                }
            }

            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Redirect based on type (fetch type first to be sure?)
            // Or just check referrer.
             if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'clinics') !== false) {
                 $this->redirect('?route=admin/clinics');
            } else {
                 $this->redirect('?route=admin/hospitals');
            }
        }
    }

    public function toggle_status() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            // Get current status
            $stmt = $this->db->prepare("SELECT subscription_status FROM hospitals WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $current = $stmt->fetchColumn();

            $new_status = ($current == 'active') ? 'inactive' : 'active';

            $update = $this->db->prepare("UPDATE hospitals SET subscription_status = :status WHERE id = :id");
            $update->execute([':status' => $new_status, ':id' => $id]);
        }
        
        if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'clinics') !== false) {
             $this->redirect('?route=admin/clinics');
        } else {
             $this->redirect('?route=admin/hospitals');
        }
    }

    // --- Hospital Stats for Super Admin ---
    public function view_hospital_stats() {
        if (!isset($_GET['id'])) $this->redirect('?route=admin/hospitals');
        $hid = $_GET['id'];

        // Get Hospital Info
        $stmt = $this->db->prepare("SELECT * FROM hospitals WHERE id = :id");
        $stmt->execute([':id' => $hid]);
        $data['hospital'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Stats
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM doctor_hospital_affiliations WHERE hospital_id = :hid");
        $stmt->execute([':hid' => $hid]);
        $data['total_doctors'] = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = :hid");
        $stmt->execute([':hid' => $hid]);
        $data['total_appointments'] = $stmt->fetchColumn();

        $data['page_title'] = "Hospital Statistics";
        $this->view('admin/hospital_stats', $data);
    }

    // --- Universal Search for Super Admin ---
    public function search() {
        $q = $_GET['q'] ?? '';
        $data['query'] = $q;
        
        if (empty(trim($q))) {
            $data['results'] = [];
        } else {
            $searchTerm = "%$q%";
            
            // Search Hospitals & Clinics
            $stmt = $this->db->prepare("SELECT id, name, type, 'hospital' as category FROM hospitals WHERE name LIKE :q OR address LIKE :q");
            $stmt->execute([':q' => $searchTerm]);
            $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Search Doctors
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, dd.specialization as type, 'doctor' as category 
                FROM users u 
                JOIN doctors_details dd ON u.id = dd.user_id 
                WHERE (u.name LIKE :q OR u.email LIKE :q OR dd.specialization LIKE :q) AND u.role = 'doctor'
            ");
            $stmt->execute([':q' => $searchTerm]);
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data['results'] = array_merge($hospitals, $doctors);
        }

        $data['page_title'] = "Search Results";
        $this->view('admin/search_results', $data);
    }

    // --- Users & Access Management ---
    public function users() {
        $stmt = $this->db->query("
            SELECT u.id, u.name, u.email, u.phone, u.role, u.created_at, h.name as hospital_name, h.id as hospital_id
            FROM users u
            LEFT JOIN hospitals h ON u.id = h.user_id
            ORDER BY u.role ASC, u.name ASC
        ");
        $data['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all clinics and hospitals for dropdown selection
        $stmt_h = $this->db->query("SELECT id, name, type FROM hospitals ORDER BY name ASC");
        $data['hospitals'] = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

        $data['page_title'] = "Users & Access";
        $this->view('admin/users', $data);
    }

    public function add_user() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'] ?? null;
            if (empty($email)) {
                $email = null;
            }
            $password = $_POST['password'];
            $role = $_POST['role'] ?? 'patient';
            $hospital_id = $_POST['hospital_id'] ?? null;

            // Check if user exists
            $check = $this->db->prepare("SELECT id FROM users WHERE phone = :phone");
            $check->execute([':phone' => $phone]);
            if ($check->fetch()) {
                $_SESSION['error'] = "User with this phone number already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $this->db->beginTransaction();

                    $stmt = $this->db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :pass, :role)");
                    $stmt->execute([':name' => $name, ':phone' => $phone, ':email' => $email, ':pass' => $hashed_password, ':role' => $role]);
                    $user_id = $this->db->lastInsertId();

                    if ($role === 'hospital_admin' && !empty($hospital_id)) {
                        // Clear user from any other hospitals first
                        $stmt_clear = $this->db->prepare("UPDATE hospitals SET user_id = NULL WHERE user_id = :uid");
                        $stmt_clear->execute([':uid' => $user_id]);

                        // Assign user to new hospital
                        $stmt_link = $this->db->prepare("UPDATE hospitals SET user_id = :uid WHERE id = :hid");
                        $stmt_link->execute([':uid' => $user_id, ':hid' => $hospital_id]);
                    }

                    $this->db->commit();
                    $_SESSION['success'] = "<b>$name</b> has been created with role <b>$role</b>.";
                } catch(Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    $_SESSION['error'] = "Failed to create user: " . $e->getMessage();
                }
            }
            $this->redirect('?route=admin/users');
        }
    }

    public function update_user_role() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_POST['user_id'];
            $role = $_POST['role'];
            $hospital_id = $_POST['hospital_id'] ?? null;
            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
                $stmt->execute([':role' => $role, ':id' => $user_id]);

                // Clear user from any existing hospital associations
                $stmt_clear = $this->db->prepare("UPDATE hospitals SET user_id = NULL WHERE user_id = :uid");
                $stmt_clear->execute([':uid' => $user_id]);

                // If role is hospital_admin, link to the new hospital
                if ($role === 'hospital_admin' && !empty($hospital_id)) {
                    $stmt_link = $this->db->prepare("UPDATE hospitals SET user_id = :uid WHERE id = :hid");
                    $stmt_link->execute([':uid' => $user_id, ':hid' => $hospital_id]);
                }

                $this->db->commit();
                $_SESSION['success'] = "User role updated successfully.";
            } catch(Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $_SESSION['error'] = "Failed to update role: " . $e->getMessage();
            }
            $this->redirect('?route=admin/users');
        }
    }

    // --- Settings Page ---
    public function settings() {
        // Fetch all users for password management
        $stmt = $this->db->query("SELECT id, name, phone, role, created_at FROM users ORDER BY role ASC, name ASC");
        $data['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Database stats
        $data['stats'] = [];
        $tables = [
            'users' => 'users',
            'hospitals' => 'hospitals',
            'doctors' => 'doctors_details',
            'appointments' => 'appointments',
            'regions' => 'regions',
            'diet_plans' => 'diet_plans'
        ];
        foreach ($tables as $key => $table) {
            try {
                $s = $this->db->query("SELECT COUNT(*) FROM `$table`");
                $data['stats'][$key] = $s->fetchColumn();
            } catch (Exception $e) {
                $data['stats'][$key] = 0;
            }
        }

        $data['page_title'] = "Settings";
        $this->view('admin/settings', $data);
    }

    // --- Export Data / Backup ---
    public function export_data() {
        $type = $_GET['type'] ?? 'full';
        $export = ['exported_at' => date('Y-m-d H:i:s'), 'type' => $type];

        try {
            if ($type === 'full' || $type === 'users') {
                $stmt = $this->db->query("SELECT id, name, phone, role, created_at FROM users ORDER BY id ASC");
                $export['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($type === 'full' || $type === 'hospitals') {
                $stmt = $this->db->query("SELECT * FROM hospitals ORDER BY id ASC");
                $export['hospitals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $this->db->query("SELECT * FROM regions ORDER BY id ASC");
                $export['regions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($type === 'full' || $type === 'doctors') {
                $stmt = $this->db->query("SELECT d.*, u.name, u.phone, u.role FROM doctors_details d JOIN users u ON d.user_id = u.id ORDER BY d.id ASC");
                $export['doctors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $this->db->query("SELECT * FROM doctor_hospital_affiliations ORDER BY id ASC");
                $export['affiliations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($type === 'full') {
                try {
                    $stmt = $this->db->query("SELECT * FROM appointments ORDER BY id ASC");
                    $export['appointments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $export['appointments'] = []; }

                try {
                    $stmt = $this->db->query("SELECT * FROM departments ORDER BY id ASC");
                    $export['departments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $export['departments'] = []; }

                try {
                    $stmt = $this->db->query("SELECT * FROM diet_plans ORDER BY id ASC");
                    $export['diet_plans'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $export['diet_plans'] = []; }

                try {
                    $stmt = $this->db->query("SELECT * FROM blood_donors ORDER BY id ASC");
                    $export['blood_donors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $export['blood_donors'] = []; }

                try {
                    $stmt = $this->db->query("SELECT * FROM blood_requests ORDER BY id ASC");
                    $export['blood_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $export['blood_requests'] = []; }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Export failed: " . $e->getMessage();
            $this->redirect('?route=admin/settings');
            return;
        }

        $filename = "mednova_backup_{$type}_" . date('Y-m-d_His') . ".json";
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    // --- Import Data ---
    public function import_data() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?route=admin/settings');
            return;
        }

        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== 0) {
            $_SESSION['error'] = "Please select a valid backup file.";
            $this->redirect('?route=admin/settings');
            return;
        }

        $json = file_get_contents($_FILES['backup_file']['tmp_name']);
        $data = json_decode($json, true);

        if (!$data) {
            $_SESSION['error'] = "Invalid JSON file. Please check the file format.";
            $this->redirect('?route=admin/settings');
            return;
        }

        $imported = [];

        try {
            $this->db->beginTransaction();

            // Import Regions
            if (isset($_POST['import_regions']) && isset($data['regions'])) {
                $count = 0;
                foreach ($data['regions'] as $r) {
                    $check = $this->db->prepare("SELECT id FROM regions WHERE name = :name");
                    $check->execute([':name' => $r['name']]);
                    if (!$check->fetch()) {
                        $stmt = $this->db->prepare("INSERT INTO regions (name, created_at) VALUES (:name, :created)");
                        $stmt->execute([':name' => $r['name'], ':created' => $r['created_at'] ?? date('Y-m-d H:i:s')]);
                        $count++;
                    }
                }
                $imported[] = "$count regions";
            }

            // Import Hospitals
            if (isset($_POST['import_hospitals']) && isset($data['hospitals'])) {
                $count = 0;
                foreach ($data['hospitals'] as $h) {
                    $check = $this->db->prepare("SELECT id FROM hospitals WHERE slug = :slug");
                    $check->execute([':slug' => $h['slug'] ?? '']);
                    if (!$check->fetch()) {
                        $stmt = $this->db->prepare("INSERT INTO hospitals (name, slug, type, region_id, address, contact_info, description, image, subscription_status) VALUES (:name, :slug, :type, :rid, :addr, :contact, :desc, :img, :status)");
                        $stmt->execute([
                            ':name' => $h['name'],
                            ':slug' => $h['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($h['name']))),
                            ':type' => $h['type'] ?? 'Hospital',
                            ':rid' => $h['region_id'] ?? null,
                            ':addr' => $h['address'] ?? '',
                            ':contact' => $h['contact_info'] ?? '',
                            ':desc' => $h['description'] ?? '',
                            ':img' => $h['image'] ?? '',
                            ':status' => $h['subscription_status'] ?? 'active'
                        ]);
                        $count++;
                    }
                }
                $imported[] = "$count hospitals";
            }

            // Import Doctors
            if (isset($_POST['import_doctors']) && isset($data['doctors'])) {
                $count = 0;
                foreach ($data['doctors'] as $d) {
                    if (!empty($d['user_id'])) {
                        $check = $this->db->prepare("SELECT id FROM doctors_details WHERE user_id = :uid");
                        $check->execute([':uid' => $d['user_id']]);
                        if (!$check->fetch()) {
                            $stmt = $this->db->prepare("INSERT INTO doctors_details (user_id, specialization, phone, address, biography, rating) VALUES (:uid, :spec, :phone, :addr, :bio, :rating)");
                            $stmt->execute([
                                ':uid' => $d['user_id'],
                                ':spec' => $d['specialization'] ?? '',
                                ':phone' => $d['phone'] ?? '',
                                ':addr' => $d['address'] ?? '',
                                ':bio' => $d['biography'] ?? '',
                                ':rating' => $d['rating'] ?? 5.0
                            ]);
                            $count++;
                        }
                    }
                }
                $imported[] = "$count doctors";
            }

            // Import Users
            if (isset($_POST['import_users']) && isset($data['users'])) {
                $count = 0;
                foreach ($data['users'] as $u) {
                    $check = $this->db->prepare("SELECT id FROM users WHERE phone = :phone");
                    $check->execute([':phone' => $u['phone'] ?? '']);
                    if (!$check->fetch()) {
                        $stmt = $this->db->prepare("INSERT INTO users (name, phone, password, role) VALUES (:name, :phone, :pass, :role)");
                        $stmt->execute([
                            ':name' => $u['name'],
                            ':phone' => $u['phone'] ?? '',
                            ':pass' => password_hash('mednova123', PASSWORD_DEFAULT),
                            ':role' => $u['role'] ?? 'patient'
                        ]);
                        $count++;
                    }
                }
                $imported[] = "$count users";
            }

            // Import Appointments
            if (isset($_POST['import_appointments']) && isset($data['appointments'])) {
                $count = 0;
                foreach ($data['appointments'] as $a) {
                    $stmt = $this->db->prepare("INSERT INTO appointments (patient_name, patient_phone, hospital_id, doctor_id, appointment_date, appointment_time, status, created_at) VALUES (:name, :phone, :hid, :did, :date, :time, :status, :created)");
                    $stmt->execute([
                        ':name' => $a['patient_name'] ?? '',
                        ':phone' => $a['patient_phone'] ?? '',
                        ':hid' => $a['hospital_id'] ?? null,
                        ':did' => $a['doctor_id'] ?? null,
                        ':date' => $a['appointment_date'] ?? date('Y-m-d'),
                        ':time' => $a['appointment_time'] ?? '09:00',
                        ':status' => $a['status'] ?? 'pending',
                        ':created' => $a['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $count++;
                }
                $imported[] = "$count appointments";
            }

            $this->db->commit();
            $_SESSION['success'] = "<b>Import completed!</b> Imported: " . implode(', ', $imported);
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Import failed: " . $e->getMessage();
        }

        $this->redirect('?route=admin/settings');
    }

    // --- Reset Password ---
    public function reset_password() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?route=admin/users');
            return;
        }

        $user_id = $_POST['user_id'] ?? null;
        $new_password = $_POST['new_password'] ?? '';

        if (!$user_id || strlen($new_password) < 4) {
            $_SESSION['error'] = "Invalid user or password too short (min 4 characters).";
            $this->redirect('?route=admin/users');
            return;
        }

        try {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = :pass WHERE id = :id");
            $stmt->execute([':pass' => $hashed, ':id' => $user_id]);

            // Get name for confirmation
            $stmt = $this->db->prepare("SELECT name FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $name = $stmt->fetchColumn();

            $_SESSION['success'] = "Password for <b>" . htmlspecialchars($name) . "</b> has been updated successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to reset password: " . $e->getMessage();
        }

        $this->redirect('?route=admin/users');
    }

    // --- Reset All Data (Danger Zone) ---
    public function reset_all_data() {
        try {
            $this->db->beginTransaction();

            // Delete non-admin data
            $tables_to_clear = [
                'appointments', 'diet_plans', 'blood_donors', 'blood_requests',
                'notifications', 'doctor_hospital_affiliations', 'doctors_details'
            ];
            foreach ($tables_to_clear as $table) {
                try {
                    $this->db->exec("DELETE FROM `$table`");
                } catch (Exception $e) { /* table may not exist */ }
            }

            // Delete non-admin hospitals
            $this->db->exec("DELETE FROM hospitals");

            // Delete non-admin users
            $this->db->exec("DELETE FROM users WHERE role != 'admin'");

            $this->db->commit();
            $_SESSION['success'] = "<b>All data has been reset.</b> Only admin accounts have been preserved.";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Reset failed: " . $e->getMessage();
        }

        $this->redirect('?route=admin/settings');
    }
}
