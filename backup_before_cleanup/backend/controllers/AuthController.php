<?php
require_once 'models/User.php';

class AuthController extends Controller {
    public function login() {
        $data = [];
        if (isset($_GET['slug'])) {
            $slug = $_GET['slug'];
            $stmt = $this->db->prepare("SELECT name, type, image FROM hospitals WHERE slug = :slug");
            $stmt->execute([':slug' => $slug]);
            $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($hospital) {
                $data['hospital'] = $hospital;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? null;

            $userModel = new User($this->db);
            $user = $userModel->login($email, $password, $role);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                switch ($user['role']) {
                    case 'admin':
                        $this->redirect('?route=admin/dashboard');
                        break;
                    case 'hospital_admin':
                        $this->redirect('?route=hospital_admin/dashboard');
                        break;
                    case 'doctor':
                        $this->redirect('?route=doctor/dashboard');
                        break;
                    case 'receptionist':
                        $this->redirect('?route=receptionist/dashboard');
                        break;
                    case 'patient':
                        $this->redirect('?route=patient/dashboard');
                        break;
                }
            } else {
                error_log("Login failed for email: $email");
                $data['error'] = "Invalid email or password";
                $this->view('auth/login', $data);
            }
        } else {
            $this->view('auth/login', $data);
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('?route=auth/login');
    }
}
