<?php
// === Database connection details ===
$host = "localhost";   // Change if needed
$user = "root";        // Change to your MySQL username
$pass = "";            // Change to your MySQL password
$db   = "u815229119_shop"; // Change to your database name

try {
    // Connect to MySQL using PDO
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Set timezone to IST (+05:30)
    $pdo->exec("SET time_zone = '+05:30'");

    // Verify timezone
    $stmt = $pdo->query("
        SELECT 
            @@session.time_zone AS sessionTZ, 
            NOW() AS currentTime, 
            UTC_TIMESTAMP() AS utcTime
    ");
    $row = $stmt->fetch();

    echo "<h2>MySQL Timezone Fix</h2>";
    echo "Session Timezone: " . htmlspecialchars($row['sessionTZ']) . "<br>";
    echo "Current DB Time: " . htmlspecialchars($row['currentTime']) . "<br>";
    echo "UTC Time: " . htmlspecialchars($row['utcTime']) . "<br>";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}