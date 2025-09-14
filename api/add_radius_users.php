<?php
// api/add_radius_user.php
require_once __DIR__ . '/../includes/db.php';
$config = require __DIR__ . '/../includes/config.php';

// Very important: use a secret key and HTTPS in production
$secret = $config['app_key'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['secret'] ?? '') !== $secret) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'missing']);
    exit;
}

// insert into radcheck
$stmt = $pdo->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, 'Cleartext-Password', ':=', ?)");
try {
    $stmt->execute([$username, $password]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db']);
}
