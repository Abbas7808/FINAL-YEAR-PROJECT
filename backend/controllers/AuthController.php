<?php
require_once 'models/User.php';

/**
 * ==========================================
 * Authentication Controller (AuthController)
 * ==========================================
 * This controller handles user login, session management, 
 * role-based dashboard redirection, and user logout.
 */
class AuthController extends Controller {

    /**
     * Handles the login page rendering and authentication form submission.
     */
    public function login() {
        $data = [];
        
        // Check if hospital slug is provided in GET parameters to customize login context
        if (isset($_GET['slug'])) {
            $slug = $_GET['slug'];
            $stmt = $this->db->prepare("SELECT name, type, image FROM hospitals WHERE slug = :slug");
            $stmt->execute([':slug' => $slug]);
            $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($hospital) {
                $data['hospital'] = $hospital; // Bind hospital details to the login view
            }
        }

        // Process POST requests (Login form submissions)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? null; // Optional role filtering

            // Query database via User model to verify credentials (ignoring user-selected role dropdown for seamless auto-redirection)
            $userModel = new User($this->db);
            $user = $userModel->login($email, $password, null);

            if ($user) {
                // Save user details into PHP session upon successful authentication
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                // Redirect user to their respective dashboard depending on their role
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
                // If login fails, log error, set alert message, and reload the login page
                error_log("Login failed for email: $email");
                $data['error'] = "Invalid email or password";
                $this->view('auth/login', $data);
            }
        } else {
            // Render the login view for normal GET requests
            $this->view('auth/login', $data);
        }
    }

    /**
     * Destroys current session and redirects user to the login page.
     */
    public function logout() {
        session_destroy(); // Clear all session data
        $this->redirect('?route=auth/login'); // Redirect to login
    }
}

