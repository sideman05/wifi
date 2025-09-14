<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$user = current_user();

// Pagination setup
$plansPerPage = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $plansPerPage;

// Get total count
$totalPlans = $pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn();
$totalPages = ceil($totalPlans / $plansPerPage);

// fetch plans for current page
$stmt = $pdo->prepare("SELECT * FROM plans LIMIT ? OFFSET ?");
$stmt->bindValue(1, $plansPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$plans = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Available Halo Plans</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: #f8fafc;
    }

    #loader {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.3);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    #loader div {
      border: 6px solid #f3f3f3;
      border-top: 6px solid #0c9e13ff;
      border-radius: 50%;
      width: 70px;
      height: 70px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      100% {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body>
  <div id="loader">
    <div></div>
  </div>
  <!-- Mobile Navbar -->
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
        <a href="plans.php" class="py-2 px-4 rounded-lg bg-emerald-600 transition">Buy Plan</a>
        <a href="transactions.php" class="py-2 px-4 rounded-lg hover:bg-emerald-700 transition">Transactions</a>
        <a href="logout.php" class="py-2 px-4 rounded-lg hover:bg-red-600 transition mt-8">Logout</a>
      </nav>
    </aside>
    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden"></div>
    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-8 md:ml-64" style="min-width:0;" id="mainContent">
      <div class="max-w-5xl mx-auto py-10 px-4">
        <h2 class="text-3xl font-extrabold text-center mb-8 text-emerald-600">Welcome <?= htmlspecialchars($user['username']) ?>! Choose a WiFi Plan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php foreach ($plans as $plan): ?>
            <div class="bg-white rounded-2xl shadow-lg flex flex-col justify-between p-6 min-h-[320px] border border-gray-100 hover:shadow-2xl transition">
              <div>
                <h5 class="text-xl font-bold text-emerald-700 mb-2"><?= htmlspecialchars($plan['name']) ?></h5>
                <h3 class="text-3xl font-extrabold text-gray-900 mb-3">Tsh. <?= number_format($plan['price']) ?>/=</h3>
                <p class="text-gray-600 mb-2">
                  Duration: <span class="font-semibold text-gray-800"><?= $plan['duration_hours'] % 24 === 0 ? ($plan['duration_hours'] / 24) . ' days' : $plan['duration_hours'] . ' hours' ?></span><br>
                  <?php if (!empty($plan['data_limit_mb'])): ?>
                    Data: <span class="font-semibold text-gray-800"><?= $plan['data_limit_mb'] / 1024 ?> GB</span>
                  <?php endif; ?>
                </p>
              </div>
              <form method="post" action="purchase.php" class="mt-4">
                <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                <input type="text" name="phone" class="block w-full rounded-lg border border-gray-300 px-4 py-2 mb-3 focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="2557XXXXXXXX" required>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg shadow transition">Buy</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- Pagination Navigation -->
        <?php if ($totalPages > 1): ?>
          <div class="flex justify-center items-center gap-4 mt-10">
            <a href="?page=<?= max(1, $page - 1) ?>" class="px-5 py-2 rounded-lg font-semibold shadow transition border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50 <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">Previous</a>
            <span class="text-gray-700 font-semibold">Page <?= $page ?> of <?= $totalPages ?></span>
            <a href="?page=<?= min($totalPages, $page + 1) ?>" class="px-5 py-2 rounded-lg font-semibold shadow transition border border-emerald-600 text-emerald-700 bg-white hover:bg-emerald-50 <?= $page >= $totalPages ? 'opacity-50 pointer-events-none' : '' ?>">Next</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <!-- Floating Next Button -->
  <button id="nextPlansBtn" class="fixed bottom-6 right-6 z-40 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full shadow-lg p-4 transition flex items-center justify-center" title="See more plans" style="display:none;">
    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
    </svg>
  </button>
  <script>
    window.addEventListener("load", function() {
      document.getElementById("loader").style.display = "none";
    });
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
    if (menuBtn && sidebar && overlay) {
      menuBtn.addEventListener('click', openSidebar);
      overlay.addEventListener('click', closeSidebar);
      // On desktop, always show sidebar and remove overlay
      function handleResize() {
        if (window.innerWidth >= 768) {
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

    // Floating next button logic
    document.addEventListener('DOMContentLoaded', function() {
      const plansGrid = document.querySelector('.grid');
      const nextBtn = document.getElementById('nextPlansBtn');
      if (!plansGrid || !nextBtn) return;

      function checkOverflow() {
        // Show button if grid is taller than viewport
        if (plansGrid.scrollHeight > window.innerHeight * 0.85) {
          nextBtn.style.display = 'flex';
        } else {
          nextBtn.style.display = 'none';
        }
      }
      checkOverflow();
      window.addEventListener('resize', checkOverflow);

      nextBtn.addEventListener('click', function() {
        // Scroll down by one grid row height (estimate)
        const card = plansGrid.querySelector('div');
        if (card) {
          const cardHeight = card.offsetHeight + 32; // 32px = gap-8
          window.scrollBy({
            top: cardHeight * (window.innerWidth >= 1024 ? 1 : 2),
            left: 0,
            behavior: 'smooth'
          });
        }
      });
    });
  </script>
</body>

</html>