<?php
// reset.php
require_once __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set("Africa/Nairobi");

$token = $_GET['token'] ?? null;
$feedback = '';
$error = '';

if (!$token) {
        $error = "Invalid reset link.";
} else {
        // Step 1: Lookup token
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? LIMIT 1");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
                $error = "Invalid reset link (token not found).";
        } elseif (strtotime($reset['expires_at']) < time()) {
                $error = "This reset link has expired. Please request a new one.";
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = $_POST['password'] ?? '';
        if (strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters.";
            // Do not set feedback, so form will still show
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $reset['user_id']]);
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id = ?");
            $stmt->execute([$reset['id']]);
            $feedback = "<span class='block bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg text-center font-semibold mb-2'>Password has been changed. <a href='login.php' class='underline text-emerald-700'>Login here</a></span>";
        
            $error = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Halo Billing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background: #f8fafc; }</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 to-gray-100">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <h3 class="text-2xl font-extrabold text-center mb-6 text-emerald-700">Reset Password</h3>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg text-center font-semibold mb-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($feedback)) echo $feedback; ?>
        <?php if (empty($feedback) && (empty($error) || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST'))): ?>
        <form method="post" class="space-y-5">
            <div>
                <label for="password" class="block text-gray-700 font-semibold mb-1">New Password</label>
                <input type="password" id="password" name="password" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="New Password" required>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg shadow transition">Reset Password</button>
            <p class="text-center mt-3 text-gray-600">
                <a href="login.php" class="text-emerald-700 font-semibold hover:underline">Back to Login</a>
            </p>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
