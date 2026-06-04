<?php

class Controller {
    protected $db;
    protected $model;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function view($view, $data = []) {
        extract($data);
        if (file_exists("views/" . $view . ".php")) {
            require_once "views/" . $view . ".php";
        } else {
            die("View does not exist: " . $view);
        }
    }
    
    public function redirect($url) {
        header("Location: " . $url);
        exit();
    }

    // Send email notification via configured SMTP (Gmail example)
    public function sendEmailNotification($to, $subject, $htmlBody) {
        // Load mail config
        $confFile = __DIR__ . '/../config/mail.php';
        if (!file_exists($confFile)) return false;
        $conf = include $confFile;
        // Load SimpleMailer
        $mailerFile = __DIR__ . '/SimpleMailer.php';
        if (!file_exists($mailerFile)) return false;
        require_once $mailerFile;
        try {
            return SimpleMailer::sendSmtp($conf, $to, $subject, $htmlBody);
        } catch (Exception $e) {
            if (!empty($conf['debug_log'])) file_put_contents($conf['debug_log'], date('[Y-m-d H:i:s] ') . "Mailer exception: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }

    // Check hospital-level setting for email notifications. Creates table with default=1 if missing.
    public function isEmailEnabledForHospital($hospitalId) {
        try {
            // Ensure settings table exists (idempotent)
            $this->db->exec("CREATE TABLE IF NOT EXISTS hospital_settings (hospital_id INT PRIMARY KEY, email_notifications TINYINT(1) DEFAULT 1)");
            $s = $this->db->prepare("SELECT email_notifications FROM hospital_settings WHERE hospital_id = :hid LIMIT 1");
            $s->execute([':hid' => $hospitalId]);
            $val = $s->fetchColumn();
            if ($val === false) {
                // insert default enabled
                $i = $this->db->prepare("INSERT INTO hospital_settings (hospital_id, email_notifications) VALUES (:hid, 1)");
                $i->execute([':hid' => $hospitalId]);
                return true;
            }
            return (bool)$val;
        } catch (Exception $e) {
            // On any DB error, default to enabled to avoid silent misses
            return true;
        }
    }
}
