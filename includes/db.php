<?php
$config = require __DIR__ . '/config.php'; // this loads the array from config.php
$db = $config['db'];
date_default_timezone_set("Africa/Nairobi");

$dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
    // echo "✅ Connected from db.php";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection error: " . $e->getMessage();
    exit;
}
