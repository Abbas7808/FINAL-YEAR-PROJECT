<?php

class Database {
    private $host;
    private $port;
    private $db_name = 'u230645419_fyp_mediqu';
    private $username;
    private $password;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        $isLocal = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $hostHeader = $_SERVER['HTTP_HOST'];
            if (stripos($hostHeader, 'localhost') !== false || stripos($hostHeader, '127.0.0.1') !== false) {
                $isLocal = true;
            }
        } else {
            // CLI or other environments
            $isLocal = true;
        }

        if ($isLocal) {
            // Probe local MySQL candidate ports: 3306 (default MySQL), 3308 (alternative XAMPP), 3307 (alternative MySQL)
            $ports = ['3306', '3308', '3307'];
            $connected = false;
            
            foreach ($ports as $candidatePort) {
                try {
                    $dsn = "mysql:host=127.0.0.1;port=" . $candidatePort . ";dbname=" . $this->db_name;
                    // Short timeout so the connection probe doesn't cause page hangs
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 2,
                    ];
                    $this->conn = new PDO($dsn, 'root', '', $options);
                    $this->conn->exec("set names utf8");
                    
                    $this->host = '127.0.0.1';
                    $this->port = $candidatePort;
                    $this->username = 'root';
                    $this->password = '';
                    $connected = true;
                    break;
                } catch (PDOException $e) {
                    // Code 1049 means the port is active but the specific DB u230645419_fyp_mediqu doesn't exist
                    // This is a successful port detection, but we need to auto-create the DB
                    if ($e->getCode() == 1049) {
                        $this->host = '127.0.0.1';
                        $this->port = $candidatePort;
                        $this->username = 'root';
                        $this->password = '';
                        return $this->createDatabase();
                    }
                }
            }
            
            // If all local ports fail, try connecting using fallback Hostinger credentials locally 
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
                    echo "Connection error: " . $e2->getMessage();
                }
            }
        } else {
            // Live production Hostinger configuration
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
                if ($exception->getCode() == 1049) {
                    return $this->createDatabase();
                } else {
                    echo "Connection error: " . $exception->getMessage();
                }
            }
        }
        
        return $this->conn;
    }

    private function createDatabase() {
        try {
            // Connect to MySQL without DB
            $dsn = "mysql:host=" . $this->host;
            if ($this->port) {
                $dsn .= ";port=" . $this->port;
            }
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create DB
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "`");
            $pdo->exec("USE `" . $this->db_name . "`");

            // Import Schema
            if (file_exists(__DIR__ . '/../schema.sql')) {
                $sql = file_get_contents(__DIR__ . '/../schema.sql');
                $pdo->exec($sql);
            }

            // Re-connect to the new DB
            $dsn .= ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $this->conn;

        } catch (PDOException $e) {
            die("Database Auto-Creation Failed: " . $e->getMessage());
        }
    }
}
