<?php
// public/purchase.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['plan_id'])) {
    header('Location: plans.php'); exit;
}

$plan_id = (int)$_POST['plan_id'];

// fetch plan
$stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();
if (!$plan) {
    die('Plan not found');
}

// create transaction
$stmt = $pdo->prepare("INSERT INTO transactions (user_id, plan_id, amount, status) VALUES (?, ?, ?, 'pending')");
$stmt->execute([$user['id'], $plan_id, $plan['price']]);
$txn_id = $pdo->lastInsertId();

// === In production: redirect to payment gateway here ===
// For demo: mark as completed and provision user immediately
$pdo->prepare("UPDATE transactions SET status='completed' WHERE id = ?")->execute([$txn_id]);

// Provision user into RADIUS (API call to internal endpoint)
// We can call internal script to add username/password to radcheck
$username = $user['username'];
$password = bin2hex(random_bytes(4)); // temporary password
// add to radcheck
$stmt = $pdo->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, 'Cleartext-Password', ':=', ?)");
$stmt->execute([$username, $password]);

// Optionally store provision info somewhere (here we could email or show password)
header('Location: dashboard.php?provisioned=1');
exit;
