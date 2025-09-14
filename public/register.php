<?php
// public/register.php
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Username and password required.';
    } else {
        // basic validation
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Username or email already exists.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Halo Billing</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #f8fafc; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 to-gray-100">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    <h3 class="text-2xl font-extrabold text-center mb-6 text-emerald-700">Create Account</h3>
    <form method="post" class="space-y-5">
      <?php if (!empty($error)): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg text-center font-semibold mb-2"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <div>
        <label for="username" class="block text-gray-700 font-semibold mb-1">Username</label>
        <input type="text" id="username" name="username" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
      </div>
      <div>
        <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
        <input type="email" id="email" name="email" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
      </div>
      <div>
        <label for="password" class="block text-gray-700 font-semibold mb-1">Password</label>
        <input type="password" id="password" name="password" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
      </div>
      <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg shadow transition">Register</button>
      <p class="text-center mt-3 text-gray-600">
        Already have an account? <a href="login.php" class="text-emerald-700 font-semibold hover:underline">Login</a>
      </p>
    </form>
  </div>
</body>
</html>

