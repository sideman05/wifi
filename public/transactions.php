<?php
// public/transactions.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_login();
$user = current_user();

// Pagination setup
$txnsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $txnsPerPage;
// Get total count
$totalTxns = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
$totalTxns->execute([$user['id']]);
$totalCount = $totalTxns->fetchColumn();
$totalPages = max(1, ceil($totalCount / $txnsPerPage));
// fetch transactions for current page
$txn_stmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM transactions t JOIN plans p ON t.plan_id = p.id WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT ? OFFSET ?");
$txn_stmt->bindValue(1, $user['id'], PDO::PARAM_INT);
$txn_stmt->bindValue(2, $txnsPerPage, PDO::PARAM_INT);
$txn_stmt->bindValue(3, $offset, PDO::PARAM_INT);
$txn_stmt->execute();
$transactions = $txn_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Transactions - Halo Billing</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
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
    @keyframes spin { 
        100% { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <!-- Mobile Navbar -->
  <div id="loader"><div></div></div>
  <div class="md:hidden flex items-center justify-between bg-gray-900 text-white px-4 py-3">
    <span class="text-xl font-bold tracking-wide">Halo Billing</span>
    <button id="menuBtn" class="focus:outline-none">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>
  </div>
  <div class="flex">
    <!-- Sidebar -->
  <aside id="sidebar" class="z-30 top-0 left-0 h-screen w-64 bg-gray-900 text-white flex flex-col p-6 fixed md:fixed transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out md:flex md:translate-x-0 hidden">
      <h3 class="text-2xl font-bold mb-8 tracking-wide">Halo Billing</h3>
      <nav class="flex flex-col gap-2">
        <a href="dashboard.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 font-semibold">Dashboard</a>
        <a href="plans.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Buy Plan</a>
        <a href="transactions.php" class="py-2 px-4 rounded-lg bg-emerald-600 transition">Transactions</a>
        <a href="logout.php" class="py-2 px-4 rounded-lg hover:bg-red-600 transition mt-8">Logout</a>
      </nav>
    </aside>
    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
    <!-- Main Content -->
  <div class="flex-1 p-4 md:p-8 md:ml-64" style="min-width:0;" id="mainContent">
      <div class="w-full max-w-full py-10 px-2 md:px-8">
        <h2 class="text-3xl font-extrabold text-center mb-8 text-emerald-600">My Transactions</h2>
        <div class="flex justify-center mb-6"></div>
        <div class="overflow-x-auto rounded-lg shadow-lg bg-white w-full">
          <table class="w-full min-w-[700px] text-center">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-3 px-6 text-gray-700 font-bold">Date</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Plan</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Amount</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $txn): ?>
                <tr class="border-b hover:bg-gray-50">
                  <td class="py-3 px-6 text-gray-800"><?= date('d M Y', strtotime($txn['created_at'])) ?></td>
                  <td class="py-3 px-6 text-gray-800"><?= htmlspecialchars($txn['plan_name']) ?></td>
                  <td class="py-3 px-6 text-emerald-600 font-bold">Tsh. <?= number_format($txn['amount']) ?>/=</td>
                  <td class="py-3 px-6">
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
