<?php

/**
 * ==========================================
 * Database Configuration & Connection Class
 * ==========================================
 * This class handles connecting to the MySQL database.
 * It features auto-detection of local development environments (XAMPP/WAMP),
 * automatic port probing (3306, 3307, 3308), production Hostinger fallback,
 * and automatic database creation if the database doesn't exist.
 */
class Database {
    // Database credentials and connection properties
    private $host;
    private $port;
    private $db_name = 'u230645419_fyp_mediqu'; // Name of the database for the project
    private $username;
    private $password;
    public $conn;

    /**
     * Establishes a PDO connection to the MySQL database.
     * Detects environment (Local vs Production) and connects accordingly.
     */
    public function getConnection() {
        $this->conn = null;
        
        // Step 1: Detect if we are running locally (localhost / 127.0.0.1) or on a live server
        $isLocal = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $hostHeader = $_SERVER['HTTP_HOST'];
            if (stripos($hostHeader, 'localhost') !== false || stripos($hostHeader, '127.0.0.1') !== false) {
                $isLocal = true; // Local development (e.g. XAMPP)
            }
        } else {
            // CLI environment is assumed local
            $isLocal = true;
        }

        // Step 2: Connection Logic for Local Development
        if ($isLocal) {
            // Probe common local MySQL ports: 3306 (default), 3308 (often alternative XAMPP), 3307 (other MySQL installations)
            $ports = ['3306', '3308', '3307'];
            $connected = false;
            
            foreach ($ports as $candidatePort) {
                try {
                    // Try to connect to the current candidate port
                    $dsn = "mysql:host=127.0.0.1;port=" . $candidatePort . ";dbname=" . $this->db_name;
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
                        PDO::ATTR_TIMEOUT => 2,                      // 2-second timeout to avoid page hangs
                    ];
                    
                    $this->conn = new PDO($dsn, 'root', '', $options);
                    $this->conn->exec("set names utf8"); // Set character encoding to UTF-8
                    
                    // Connection successful, save connection parameters
                    $this->host = '127.0.0.1';
                    $this->port = $candidatePort;
                    $this->username = 'root';
                    $this->password = '';
                    $connected = true;
                    break; // Stop checking other ports
                } catch (PDOException $e) {
                    // Error Code 1049 means MySQL is running on this port, but the database does not exist.
                    // We detect this active port and auto-create the database and tables.
                    if ($e->getCode() == 1049) {
                        $this->host = '127.0.0.1';
                        $this->port = $candidatePort;
                        $this->username = 'root';
                        $this->password = '';
                        return $this->createDatabase(); // Run database auto-creation script
                    }
                }
            }
            
            // Fallback: If port probing fails, attempt connection with standard Hostinger credentials locally
            if (!$connected) {
                try {
                    $this->host = 'localhost';
                    $this->port = null;
                    $this->username = 'u230645419_fyp_mediqu';
                    $this->password = 'MedNova@@123';
                    
                    $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
                    $this->conn = new PDO($dsn, $this->username, $this->password);
                    $this->conn->exec("set names utf8");
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch(PDOException $e2) {
                    // Fail silently or print the connection error
                    echo "Connection error: " . $e2->getMessage();
                }
            }
        } else {
            // Step 3: Connection Logic for Live Production Hostinger Environment
            $this->host = 'localhost';
            $this->port = null;
            $this->username = 'u230645419_fyp_mediqu';
            $this->password = 'MedNova@@123';
            
            try {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->exec("set names utf8");
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $exception) {
                // If database is not found on live server, auto-create it
                if ($exception->getCode() == 1049) {
                    return $this->createDatabase();
                } else {
                    echo "Connection error: " . $exception->getMessage();
                }
            }
        }
        // Auto-migration: Check if username column exists in users table
        try {
            $stmt = $this->conn->query("SHOW COLUMNS FROM users LIKE 'username'");
            if (!$stmt->fetch()) {
                $this->conn->exec("ALTER TABLE users ADD COLUMN username VARCHAR(100) DEFAULT NULL UNIQUE AFTER name");
            }
        } catch (Exception $e) { /* Fail silently */ }

        // Auto-migration: Create health_assessments table
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS `health_assessments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `bmi_score` decimal(5,2) DEFAULT NULL,
              `bmi_category` varchar(50) DEFAULT NULL,
              `calories_target` int(11) DEFAULT NULL,
              `water_target` decimal(5,2) DEFAULT NULL,
              `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (Exception $e) { /* Fail silently */ }

        return $this->conn; // Return active PDO connection object
    }

    /**
     * Automatically creates the database and imports schema.sql.
     * This makes deployment and local setup seamless.
     */
    private function createDatabase() {
        try {
            // Connect to MySQL server without selecting a database first
            $dsn = "mysql:host=" . $this->host;
            if ($this->port) {
                $dsn .= ";port=" . $this->port;
            }
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create the database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "`");
            $pdo->exec("USE `" . $this->db_name . "`");

            // Import initial SQL structure from schema.sql file
            if (file_exists(__DIR__ . '/../schema.sql')) {
                $sql = file_get_contents(__DIR__ . '/../schema.sql');
                $pdo->exec($sql); // Execute the SQL schema script
            }

            // Reconnect to the newly created database
            $dsn .= ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $this->conn;

        } catch (PDOException $e) {
            die("Database Auto-Creation Failed: " . $e->getMessage());
        }
    }
}

