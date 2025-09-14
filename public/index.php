<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HaloBandos - Home</title>
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
    @keyframes spin { 100% { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div id="loader"><div></div></div>
  <!-- Navbar -->
  <nav class="bg-gray-900 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
      <a class="text-emerald-400 text-3xl font-extrabold tracking-wide drop-shadow-lg" href="index.php">Halo Bandos</a>
      <button id="nav-toggle" class="block md:hidden text-emerald-400 focus:outline-none" aria-label="Open Menu">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
      </button>
      <div id="nav-menu" class="hidden md:flex flex-col md:flex-row md:items-center absolute md:static top-16 left-0 w-full md:w-auto bg-gray-900 md:bg-transparent z-50">
        <a href="login.php" class="block md:inline text-gray-100 border border-emerald-400 rounded-lg px-5 py-2 md:mr-3 font-semibold hover:bg-emerald-400 hover:text-gray-900 transition-all duration-200 shadow mb-2 md:mb-0">Login</a>
        <a href="register.php" class="block md:inline bg-emerald-400 text-gray-900 rounded-lg px-5 py-2 font-semibold hover:bg-emerald-500 hover:text-white transition-all duration-200 shadow">Register</a>
      </div>
    </div>
    <script>
      const navToggle = document.getElementById('nav-toggle');
      const navMenu = document.getElementById('nav-menu');
      navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('hidden');
      });
    </script>
  </nav>

  <!-- Hero Section -->
  <header class="bg-gradient-to-r from-gray-800 to-gray-600 text-white text-center py-20 shadow-inner">
    <h1 class="text-5xl font-extrabold mb-4 drop-shadow-lg">Welcome to <span class="text-emerald-400">Halo Bando Billing</span></h1>
    <p class="text-xl font-medium drop-shadow">Choose your internet plan and stay connected</p>
  </header>

  <!-- Plans Section -->
  <?php
    require_once __DIR__ . '/../includes/db.php';
    $plans = $pdo->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll();
    function plan_duration($hours) {
      if ($hours % 24 === 0) return ($hours/24) . ' days access';
      return $hours . ' hours access';
    }
  ?>
  <section class="max-w-7xl mx-auto my-16 px-4">
    <h2 class="text-3xl font-extrabold text-center mb-12 text-gray-800 tracking-tight">Available Plans</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10">
      <?php foreach ($plans as $plan): ?>
        <div class="relative group bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center border-2 border-transparent hover:border-emerald-400 hover:scale-105 transition-all duration-300">
          <div class="absolute -top-5 left-1/2 -translate-x-1/2">
            <span class="inline-block bg-emerald-400 text-gray-900 px-4 py-1 rounded-full text-sm font-bold shadow">Popular</span>
          </div>
          <h5 class="text-2xl font-bold text-emerald-600 mb-2 mt-4 group-hover:text-emerald-400 transition"><?= htmlspecialchars($plan['name']) ?></h5>
          <p class="text-gray-500 mb-2 text-lg"><?= plan_duration($plan['duration_hours']) ?></p>
          <h3 class="text-4xl font-extrabold mb-6 text-emerald-500">Tsh. <?= number_format($plan['price']) ?>/=</h3>
          <form method="post" action="purchase.php" class="w-full">
            <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
            <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl shadow-lg text-lg transition-all duration-200">Buy Now</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</body>
<script>
window.addEventListener("load", function(){
  document.getElementById("loader").style.display = "none";
});
</script>
</html>
