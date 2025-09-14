<?php
// public/login.php
require_once __DIR__ . '/../includes/db.php';
// require_once __DIR__ . '/../includes/auth.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = (int)$row['id'];
        if ($row['role'] === 'admin') {
            header('Location: ../admin/index.php'); 
        } else {
            header('Location: dashboard.php'); 
        }
        exit;
        exit;
    } else {
        $error = 'wrong username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Halo Billing</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #f8fafc; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 to-gray-100">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    <h3 class="text-2xl font-extrabold text-center mb-6 text-emerald-700">Login</h3>
    <form method="post" class="space-y-5">
      <?php if (!empty($error)): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg text-center font-semibold mb-2"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <div>
        <label for="username" class="block text-gray-700 font-semibold mb-1">Username</label>
        <input type="text" id="username" name="username" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
      </div>
      <div>
        <label for="password" class="block text-gray-700 font-semibold mb-1">Password</label>
        <input type="password" id="password" name="password" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
      </div>
      <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg shadow transition">Login</button>
      <div class="flex flex-col items-center mt-2">
        <a href="forgot.php" class="text-sm text-emerald-700 font-semibold hover:underline mb-2">Forgot password?</a>
        <p class="text-center text-gray-600">
          Don’t have an account? <a href="register.php" class="text-emerald-700 font-semibold hover:underline">Register</a>
        </p>
      </div>
    </form>
  </div>
</body>
</html>

