<?php
session_start();
require_once __DIR__ . '/db.php';

function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;

    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function is_admin(): bool {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function require_admin() {
    if (!is_admin()) {
        header('HTTP/1.1 403 Forbidden');
        echo " Access denied. Admins only.";
        exit;
    }
}
