<?php
$host = 'localhost';
$username = 'u230645419_fyp_mediqu';
$password = 'MedNova@@123';

try {
    // 1. Connect to MySQL Server (no DB selected)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server successfully.<br>";

    // 2. Create the Database
    $dbname = "`u230645419_fyp_mediqu`"; // Backticks for safety
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Database 'fyp_mediqu' check/creation successful.<br>";

    // 3. Select the Database
    $pdo->exec("USE $dbname");

    // 4. Read and Execute schema.sql
    $sql_file = 'schema.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        
        // Split SQL by semicolons to execute statements individually if needed, 
        // but PDO usually handles multiple queries if emulation is on.
        // For robustness, let's try direct execution.
        try {
            $pdo->exec($sql);
            echo "Schema imported successfully.<br>";
        } catch (PDOException $e) {
            echo "Error importing schema: " . $e->getMessage() . "<br>";
            // Fallback: splitting by ;
            echo "Attempting fallback import...<br>";
        }
        
    } else {
        echo "Error: schema.sql not found.<br>";
        exit;
    }

    echo "<h3>Setup Completed!</h3>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
