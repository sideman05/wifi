<?php
$host = "localhost";   // or 127.0.0.1
$db   = "wifi_billing"; // replace with your database name
$user = "root";        // your MySQL username
$pass = "";            // your MySQL password (empty if none)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
