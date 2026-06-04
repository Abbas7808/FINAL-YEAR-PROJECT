<?php

/**
 * ==========================================
 * Base Controller Class (Controller)
 * ==========================================
 * This is the parent controller class that all other controllers inherit from.
 * It initializes the shared PDO database connection, manages view rendering,
 * performs HTTP redirections, and provides helper functions for SMTP email sending
 * and checking hospital setting toggles.
 */
class Controller {
    protected $db; // Shareable PDO database connection object
    protected $model;

    /**
     * Constructor: Instantiates the Database class and establishes the connection.
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Renders a frontend view template and binds an associative array of data to it.
     */
    public function view($view, $data = []) {
        extract($data); // Converts keys in the data array into local PHP variables
        if (file_exists("views/" . $view . ".php")) {
            require_once "views/" . $view . ".php";
        } else {
            die("View does not exist: " . $view);
        }
    }
    
    /**
     * Redirects the user to a target URL.
     */
    public function redirect($url) {
        header("Location: " . $url);
        exit();
    }

    /**
     * Sends an email notification using SMTP settings configured in mail.php.
     */
    public function sendEmailNotification($to, $subject, $htmlBody) {
        // Step 1: Load mail credentials and parameters from backend/config/mail.php
        $confFile = __DIR__ . '/../config/mail.php';
        if (!file_exists($confFile)) return false;
        $conf = include $confFile;

        // Step 2: Load the SimpleMailer script
        $mailerFile = __DIR__ . '/SimpleMailer.php';
        if (!file_exists($mailerFile)) return false;
        require_once $mailerFile;

        // Step 3: Attempt SMTP delivery using the configuration
        try {
            return SimpleMailer::sendSmtp($conf, $to, $subject, $htmlBody);
        } catch (Exception $e) {
            // Append debug logs if mailing encounters issues
            if (!empty($conf['debug_log'])) {
                file_put_contents($conf['debug_log'], date('[Y-m-d H:i:s] ') . "Mailer exception: " . $e->getMessage() . "\n", FILE_APPEND);
            }
            return false;
        }
    }

    /**
     * Checks if email notifications are enabled for a specific hospital.
     * Auto-creates the hospital_settings table if it is missing.
     */
    public function isEmailEnabledForHospital($hospitalId) {
        try {
            // Idempotent: Ensure the hospital settings table exists in the database
            $this->db->exec("CREATE TABLE IF NOT EXISTS hospital_settings (hospital_id INT PRIMARY KEY, email_notifications TINYINT(1) DEFAULT 1)");
            
            // Check the status of notifications for the given hospital
            $s = $this->db->prepare("SELECT email_notifications FROM hospital_settings WHERE hospital_id = :hid LIMIT 1");
            $s->execute([':hid' => $hospitalId]);
            $val = $s->fetchColumn();
            
            // If setting record is not found, insert default record (Enabled = 1)
            if ($val === false) {
                $i = $this->db->prepare("INSERT INTO hospital_settings (hospital_id, email_notifications) VALUES (:hid, 1)");
                $i->execute([':hid' => $hospitalId]);
                return true;
            }
            return (bool)$val;
        } catch (Exception $e) {
            // Fallback: On any DB error, return true (notifications enabled by default) so notifications aren't missed
            return true;
        }
    }
}

