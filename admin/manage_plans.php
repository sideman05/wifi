<?php
// admin/manage_plans.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_admin();
if (!is_admin()) {
  header('Location: ../public/login.php'); exit;
}

// Handle add plan
if (isset($_POST['add_plan'])) {
  $name = trim($_POST['name'] ?? '');
  $price = floatval($_POST['price'] ?? 0);
  $duration = intval($_POST['duration_hours'] ?? 0);
  $data_limit = $_POST['data_limit_mb'] !== '' ? intval($_POST['data_limit_mb']) : null;
  if ($name && $price > 0 && $duration > 0) {
    $stmt = $pdo->prepare("INSERT INTO plans (name, price, duration_hours, data_limit_mb) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $duration, $data_limit]);
  }
}

// Handle delete plan
if (isset($_POST['delete_plan']) && isset($_POST['plan_id'])) {
  $plan_id = (int)$_POST['plan_id'];
  $stmt = $pdo->prepare("DELETE FROM plans WHERE id = ?");
  $stmt->execute([$plan_id]);
}

// Handle edit plan (update)
if (isset($_POST['edit_plan']) && isset($_POST['plan_id'])) {
  $plan_id = (int)$_POST['plan_id'];
  $name = trim($_POST['name'] ?? '');
  $price = floatval($_POST['price'] ?? 0);
  $duration = intval($_POST['duration_hours'] ?? 0);
  $data_limit = $_POST['data_limit_mb'] !== '' ? intval($_POST['data_limit_mb']) : null;
  if ($name && $price > 0 && $duration > 0) {
    $stmt = $pdo->prepare("UPDATE plans SET name=?, price=?, duration_hours=?, data_limit_mb=? WHERE id=?");
    $stmt->execute([$name, $price, $duration, $data_limit, $plan_id]);
  }
}

$plansPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $plansPerPage;

// Get total count
$totalPlans = $pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn();
$totalPages = ceil($totalPlans / $plansPerPage);

// fetch plans for current page
$stmt = $pdo->prepare("SELECT * FROM plans ORDER BY price ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $plansPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$plans = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Plans - Halo Billing</title>
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
  <!-- Mobile Navbar -->
  <div id="loader"><div></div></div>
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
        <a href="index.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 font-semibold">Dashboard</a>
        <a href="manage_plans.php" class="py-2 px-4 rounded-lg bg-emerald-600 transition">Manage Plans</a>
        <a href="manage_transactions.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Transactions</a>
        <a href="../public/logout.php" class="py-2 px-4 rounded-lg hover:bg-red-600 transition mt-8">Logout</a>
      </nav>
    </aside>
    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
    <!-- Main Content -->
  <div class="flex-1 p-4 md:p-8" style="min-width:0; margin-left:0;" id="mainContent">
    <div class="flex-1 p-4 md:p-8 md:ml-64" style="min-width:0;" id="mainContent">
      <div class="w-full max-w-full py-10 px-2 md:px-8">
        <h2 class="text-3xl font-extrabold text-emerald-700 mb-6">Manage Plans</h2>
        <!-- Add Plan Form -->
        <form method="post" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 bg-white p-6 rounded-xl shadow w-full">
          <input type="hidden" name="add_plan" value="1">
          <div>
            <label class="block text-gray-700 font-semibold mb-1">Name</label>
            <input type="text" name="name" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
          </div>    
          <div>
            <label class="block text-gray-700 font-semibold mb-1">Price (Tsh)</label>
            <input type="number" name="price" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" step="0.01" min="0" required>
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-1">Duration (hours)</label>
            <input type="number" name="duration_hours" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" min="1" required>
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-1">Data Limit (MB)</label>
            <input type="number" name="data_limit_mb" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none" min="0">
          </div>
          <div class="flex items-end">
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg shadow transition">Add Plan</button>
          </div>
        </form>
        <!-- Plans Table -->
        <div class="overflow-x-auto bg-white rounded-xl shadow w-full">
          <table class="w-full min-w-[900px] text-center">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-3 px-6 text-gray-700 font-bold">ID</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Name</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Price</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Duration (hours)</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Data Limit (MB)</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($plans as $plan): ?>
                <tr class="border-b hover:bg-gray-50">
                  <form method="post" class="align-middle">
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <td class="py-3 px-6 text-gray-800"><?= $plan['id'] ?></td>
                    <td class="py-3 px-6">
                      <input type="text" name="name" value="<?= htmlspecialchars($plan['name']) ?>" class="rounded-lg border border-gray-300 px-2 py-1 w-full text-sm focus:ring-emerald-400" required>
                    </td>
                    <td class="py-3 px-6">
                      <input type="number" name="price" value="<?= $plan['price'] ?>" class="rounded-lg border border-gray-300 px-2 py-1 w-full text-sm focus:ring-emerald-400" step="0.01" min="0" required>
                    </td>
                    <td class="py-3 px-6">
                      <input type="number" name="duration_hours" value="<?= $plan['duration_hours'] ?>" class="rounded-lg border border-gray-300 px-2 py-1 w-full text-sm focus:ring-emerald-400" min="1" required>
                    </td>
                    <td class="py-3 px-6">
                      <input type="number" name="data_limit_mb" value="<?= $plan['data_limit_mb'] ?>" class="rounded-lg border border-gray-300 px-2 py-1 w-full text-sm focus:ring-emerald-400" min="0">
                    </td>
                    <td class="py-3 px-6 flex gap-2 justify-center">
                      <button type="submit" name="edit_plan" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-1 px-3 rounded-lg shadow transition">Update</button>
                      <button type="submit" name="delete_plan" class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-3 rounded-lg shadow transition" onclick="return confirm('Delete this plan?');">Delete</button>
                    </td>
                  </form>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Pagination Navigation -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-4 mt-10">
          <a href="?page=<?= max(1, $page-1) ?>" class="px-5 py-2 rounded-lg font-semibold shadow transition border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50 <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">Previous</a>
          <span class="text-gray-700 font-semibold">Page <?= $page ?> of <?= $totalPages ?></span>
          <a href="?page=<?= min($totalPages, $page+1) ?>" class="px-5 py-2 rounded-lg font-semibold shadow transition border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50 <?= $page >= $totalPages ? 'opacity-50 pointer-events-none' : '' ?>">Next</a>
        </div>
        <?php endif; ?>
      </div>
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
