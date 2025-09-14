<?php
// admin/manage_transactions.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_admin();
if (!is_admin()) {
  header('Location: ../public/login.php'); exit;
}

// Handle delete transaction
if (isset($_POST['delete_transaction']) && isset($_POST['transaction_id'])) {
  $txn_id = (int)$_POST['transaction_id'];
  $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
  $stmt->execute([$txn_id]);
}

// Handle update status
if (isset($_POST['update_status']) && isset($_POST['transaction_id']) && isset($_POST['status'])) {
  $txn_id = (int)$_POST['transaction_id'];
  $status = in_array($_POST['status'], ['pending','completed','failed']) ? $_POST['status'] : 'pending';
  $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
  $stmt->execute([$status, $txn_id]);
}

$txnsPerPage = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $txnsPerPage;

// Get total count
$totalTxns = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$totalPages = max(1, ceil($totalTxns / $txnsPerPage));

// fetch transactions for current page
$stmt = $pdo->prepare("SELECT t.*, u.username, p.name as plan_name FROM transactions t JOIN users u ON t.user_id = u.id JOIN plans p ON t.plan_id = p.id ORDER BY t.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $txnsPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Transactions - Halo Billing</title>
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
        <a href="manage_plans.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Manage Plans</a>
        <a href="manage_transactions.php" class="py-2 px-4 rounded-lg bg-emerald-600 transition">Transactions</a>
        <a href="../public/logout.php" class="py-2 px-4 rounded-lg hover:bg-red-600 transition mt-8">Logout</a>
      </nav>
    </aside>
    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
    <!-- Main Content -->
  <div class="flex-1 p-4 md:p-8 md:ml-64" style="min-width:0;" id="mainContent">
      <div class="w-full max-w-full py-10 px-2 md:px-8">
        <h2 class="text-3xl font-extrabold text-emerald-700 mb-6">Manage Transactions</h2>

        <div class="overflow-x-auto bg-white rounded-xl shadow w-full">
          <table class="w-full min-w-[1000px] text-center">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-3 px-6 text-gray-700 font-bold">ID</th>
                <th class="py-3 px-6 text-gray-700 font-bold">User</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Plan</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Amount</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Status</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Date</th>
                <th class="py-3 px-6 text-gray-700 font-bold">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($transactions) === 0): ?>
                <tr>
                  <td colspan="7" class="py-6 text-gray-500 text-lg text-center">No transactions found.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($transactions as $txn): ?>
                  <tr class="border-b hover:bg-gray-50">
                    <form method="post" class="align-middle">
                      <input type="hidden" name="transaction_id" value="<?= $txn['id'] ?>">
                      <td class="py-3 px-6 text-gray-800"><?= $txn['id'] ?></td>
                      <td class="py-3 px-6 text-gray-800"><?= htmlspecialchars($txn['username']) ?></td>
                      <td class="py-3 px-6 text-gray-800"><?= htmlspecialchars($txn['plan_name']) ?></td>
                      <td class="py-3 px-6 text-emerald-600 font-bold">Tsh. <?= number_format($txn['amount'], 2) ?>/=</td>
                      <td class="py-3 px-6">
                        <select name="status" class="rounded-lg border-gray-300 px-2 py-1 text-sm focus:ring-emerald-400">
                          <option value="pending" <?= $txn['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                          <option value="completed" <?= $txn['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                          <option value="failed" <?= $txn['status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                        <button type="submit" name="update_status" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-1 px-3 rounded-lg shadow ml-2 transition">Update</button>
                      </td>
                      <td class="py-3 px-6 text-gray-800"><?= date('Y-m-d H:i', strtotime($txn['created_at'])) ?></td>
                      <td class="py-3 px-6">
                        <button type="submit" name="delete_transaction" class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-3 rounded-lg shadow transition" onclick="return confirm('Delete this transaction?');">Delete</button>
                      </td>
                    </form>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
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
