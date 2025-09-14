<?php
// admin/index.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_admin();
if (!is_admin()) {
  header('Location: ../public/login.php'); exit;
}

// Handle delete user
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
  $del_id = (int)$_POST['user_id'];
  if ($del_id !== $_SESSION['user_id']) { // Prevent self-delete
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$del_id]);
  }
}

// Handle status change
if (isset($_POST['change_status']) && isset($_POST['user_id']) && isset($_POST['status'])) {
  $user_id = (int)$_POST['user_id'];
  $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
  $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
  $stmt->execute([$status, $user_id]);
}


// Pagination for users
$usersPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $usersPerPage;
// Get total user count
$totalUsersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPages = max(1, ceil($totalUsersCount / $usersPerPage));
// Fetch users for current page
$stmt = $pdo->prepare("SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $usersPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
$plans = $pdo->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll();

// Dashboard stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active_plans = $pdo->query("SELECT COUNT(*) FROM plans WHERE id IN (SELECT plan_id FROM transactions WHERE status = 'completed')")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'completed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - WiFi Billing</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #f8fafc; }
    #loader {
      position: fixed;
      left: 0; top: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.3);
      display: flex; justify-content: center; align-items: center;
      z-index: 9999;
    }
    #loader div {
      border: 6px solid #f3f3f3;
      border-top: 6px solid #0c9e13ff;
      border-radius: 50%;
      width: 70px; height: 70px;
      animation: spin 1s linear infinite;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div id="loader"><div></div></div>
  <div class="min-h-screen bg-gray-100">
    <!-- Mobile Navbar -->
    <div class="md:hidden flex items-center justify-between bg-gray-900 text-white px-4 py-3">
      <span class="text-xl font-bold tracking-wide">Admin Panel</span>
      <button id="menuBtn" class="focus:outline-none">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
    <div class="flex">
      <!-- Sidebar -->
  <aside id="sidebar" class="z-30 top-0 left-0 h-screen w-64 bg-gray-900 text-white flex flex-col p-6 fixed md:fixed transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out md:flex md:translate-x-0 hidden">
        <h3 class="text-2xl font-bold mb-8 tracking-wide">Admin Panel</h3>
        <nav class="flex flex-col gap-2">
          <a href="admin.html" class="py-2 px-4 rounded-lg bg-emerald-600 font-semibold">Dashboard</a>
          <a href="manage_plans.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Manage Plans</a>
          <a href="manage_transactions.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Transactions</a>
          <a href="../public/logout.php" class="py-2 px-4 rounded-lg hover:bg-red-600 transition mt-8">Logout</a>
        </nav>
      </aside>
      <!-- Overlay for mobile -->
      <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
      <!-- Main Content -->
  <main class="flex-1 p-4 md:p-8 md:ml-64" style="min-width:0;" id="mainContent">
        <h2 class="text-3xl font-extrabold mb-2 text-gray-900">Admin Dashboard</h2>
        <p class="text-gray-500 mb-8">Manage users, plans, and transactions</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <h5 class="text-lg font-semibold text-gray-500 mb-2">Total Users</h5>
            <h3 class="text-3xl font-extrabold text-emerald-600"><?= $total_users ?></h3>
          </div>
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <h5 class="text-lg font-semibold text-gray-500 mb-2">Active Plans</h5>
            <h3 class="text-3xl font-extrabold text-emerald-600"><?= $active_plans ?></h3>
          </div>
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <h5 class="text-lg font-semibold text-gray-500 mb-2">Total Revenue</h5>
            <h3 class="text-3xl font-extrabold text-emerald-600">Tsh. <?= number_format($total_revenue) ?>/=</h3>
          </div>
        </div>
        <h4 class="text-2xl font-bold mb-4 text-gray-800">Recent Users</h4>
        <div class="overflow-x-auto bg-white rounded-xl shadow">
          <table class="min-w-full text-center">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-3 px-4 text-gray-700 font-bold">Id.</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Username</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Email</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Plan</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Role</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Status</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($users as $u): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4 text-gray-800"><?= $u['id'] ?></td>
                <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($u['username']) ?></td>
                <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($u['email']) ?></td>
                <td class="py-3 px-4 text-gray-800">Weekly Plan</td>
                <td class="py-3 px-4 text-gray-800"><?= $u['role'] ?></td>
                <td class="py-3 px-4">
                  <form method="post" class="inline">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="status" class="rounded-lg border-gray-300 px-2 py-1 text-sm focus:ring-emerald-400" onchange="this.form.submit()">
                      <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                      <option value="inactive" <?= $u['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <input type="hidden" name="change_status" value="1">
                  </form>
                </td>
                <td class="py-3 px-4">
                  <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-3 rounded-lg shadow disabled:opacity-50" <?= $u['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Pagination Navigation -->
        <?php if ($totalPages > 1): ?>
        <div class="flex flex-wrap justify-center items-center gap-2 mt-10">
          <a href="?page=<?= max(1, $page-1) ?>" class="px-4 py-2 rounded-lg font-semibold shadow transition border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50 <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">Previous</a>
          <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            if ($start > 1) echo '<span class="px-2">...</span>';
            for ($i = $start; $i <= $end; $i++) {
              if ($i == $page) {
                echo '<span class="px-4 py-2 rounded-lg font-bold bg-emerald-600 text-white">' . $i . '</span>';
              } else {
                echo '<a href="?page=' . $i . '" class="px-4 py-2 rounded-lg font-semibold shadow transition border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50">' . $i . '</a>';
              }
            }
            if ($end < $totalPages) echo '<span class="px-2">...</span>';
          ?>
          <a href="?page=<?= min($totalPages, $page+1) ?>" class="px-4 py-2 rounded-lg font-semibold shadow transition border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50 <?= $page >= $totalPages ? 'opacity-50 pointer-events-none' : '' ?>">Next</a>
        </div>
        <?php endif; ?>
      </main>
    </div>
  </div>
  <script>
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    function openSidebar() {
      sidebar.classList.remove('hidden');
      sidebar.classList.add('flex');
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('hidden');
    }
    function closeSidebar() {
      sidebar.classList.add('hidden');
      sidebar.classList.remove('flex');
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    }
    if(menuBtn && sidebar && overlay) {
      menuBtn.addEventListener('click', openSidebar);
      overlay.addEventListener('click', closeSidebar);
      // On desktop, always show sidebar and remove overlay
      function handleResize() {
        if(window.innerWidth >= 768) {
          sidebar.classList.remove('hidden', '-translate-x-full');
          sidebar.classList.add('flex');
          overlay.classList.add('hidden');
        } else {
          sidebar.classList.add('hidden', '-translate-x-full');
          sidebar.classList.remove('flex');
        }
      }
      window.addEventListener('resize', handleResize);
      handleResize();
    }
  </script>
  <script>
  window.addEventListener("load", function(){
      document.getElementById("loader").style.display = "none";
  });
  </script>
</body>
</html>
