<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);

                $resetLink = "http://localhost/wifi/public/reset.php?token=" . $token;
                $feedback = "<span class='block bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg text-center font-semibold mb-2'>Password reset link: <a href='$resetLink' class='underline text-emerald-700'>$resetLink</a></span>";
        } else {
                $feedback = "<span class='block bg-red-100 text-red-700 px-4 py-2 rounded-lg text-center font-semibold mb-2'>No account found with that email.</span>";
        }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Halo Billing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background: #f8fafc; }</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 to-gray-100">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <h3 class="text-2xl font-extrabold text-center mb-6 text-emerald-700">Forgot Password</h3>
        <?php if (!empty($feedback)) echo $feedback; ?>
        <form method="post" class="space-y-5">
            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
                <input type="email" id="email" name="email" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg shadow transition">Send Reset Link</button>
            <p class="text-center mt-3 text-gray-600">
                <a href="login.php" class="text-emerald-700 font-semibold hover:underline">Back to Login</a>
            </p>
        </form>
    </div>
</body>
</html>
