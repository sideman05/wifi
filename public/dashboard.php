<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_login();
$user = current_user();

// Get user's active/completed plan (latest completed transaction)
$plan = null;
$plan_stmt = $pdo->prepare("SELECT p.* FROM transactions t JOIN plans p ON t.plan_id = p.id WHERE t.user_id = ? AND t.status = 'completed' ORDER BY t.created_at DESC LIMIT 1");
$plan_stmt->execute([$user['id']]);
$plan = $plan_stmt->fetch();

// Get user's balance (sum of all completed transactions minus used, for demo just sum completed)
$balance_stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND status = 'completed'");
$balance_stmt->execute([$user['id']]);
$balance = $balance_stmt->fetchColumn() ?: 0;

// Get user's data usage (for demo, random or static, as not tracked in schema)
$data_used = 0; // GB, demo static
$data_limit = $plan['data_limit_mb'] ? ($plan['data_limit_mb']/1024) : 5; // GB, fallback 5GB

// Get recent transactions
$txn_stmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM transactions t JOIN plans p ON t.plan_id = p.id WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT 5");
$txn_stmt->execute([$user['id']]);
$transactions = $txn_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - Halo Billing</title>
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
      <span class="text-xl font-bold tracking-wide">WiFi Billing</span>
      <button id="menuBtn" class="focus:outline-none">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
    <div class="flex">
      <!-- Sidebar -->
  <aside id="sidebar" class="z-30 top-0 left-0 h-screen w-64 bg-gray-900 text-white flex flex-col p-6 fixed md:static transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out md:flex md:translate-x-0 hidden">
        <h3 class="text-2xl font-bold mb-8 tracking-wide">Halo Billing</h3>
        <nav class="flex flex-col gap-2">
          <a href="dashboard.php" class="py-2 px-4 rounded-lg bg-emerald-600 font-semibold">Dashboard</a>
          <a href="plans.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Buy Plan</a>
          <a href="transactions.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Transactions</a>
          <a href="logout.php" class="py-2 px-4 rounded-lg hover:bg-red-600 transition mt-8">Logout</a>
        </nav>
      </aside>
      <!-- Overlay for mobile -->
      <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
      <!-- Main Content -->
      <main class="flex-1 p-4 md:p-8" style="min-width:0; margin-left:0;" id="mainContent">
        <h2 class="text-3xl font-extrabold mb-2 text-gray-900">Welcome, <?= htmlspecialchars($user['username']) ?></h2>
        <p class="text-gray-500 mb-8">Here’s your account summary:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-10">
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <h5 class="text-lg font-semibold text-gray-500 mb-2">Active Plan</h5>
            <p class="text-xl font-bold text-emerald-600">
              <?php if ($plan): ?>
                <?= htmlspecialchars($plan['name']) ?> (<?= $plan['duration_hours'] / 24 ?> days)
              <?php else: ?>
                No active plan
              <?php endif; ?>
            </p>
          </div>
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <h5 class="text-lg font-semibold text-gray-500 mb-2">Balance</h5>
            <p class="text-xl font-bold text-emerald-600">Tsh <?= number_format($balance) ?>/=</p>
          </div>
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <h5 class="text-lg font-semibold text-gray-500 mb-2">Data Used</h5>
            <p class="text-xl font-bold text-emerald-600"><?= $data_used ?> GB / <?= $data_limit ?> GB</p>
          </div>
        </div>
        <h4 class="text-2xl font-bold mb-4 text-gray-800">Recent Transactions</h4>
        <div class="overflow-x-auto bg-white rounded-xl shadow">
          <table class="min-w-full text-center">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-3 px-4 text-gray-700 font-bold">Date</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Plan</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Amount</th>
                <th class="py-3 px-4 text-gray-700 font-bold">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $txn): ?>
                <tr class="border-b hover:bg-gray-50">
                  <td class="py-3 px-4 text-gray-800"><?= date('d M Y', strtotime($txn['created_at'])) ?></td>
                  <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($txn['plan_name']) ?></td>
                  <td class="py-3 px-4 text-emerald-600 font-bold">Tsh. <?= number_format($txn['amount']) ?></td>
                  <td class="py-3 px-4">
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                      <?php if ($txn['status'] === 'completed'): ?>bg-emerald-100 text-emerald-700<?php elseif ($txn['status'] === 'pending'): ?>bg-yellow-100 text-yellow-700<?php else: ?>bg-red-100 text-red-700<?php endif; ?>">
                      <?= ucfirst($txn['status']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
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

