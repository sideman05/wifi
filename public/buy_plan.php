<?php
// public/buy_plan.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_login();
$user = current_user();

// Get all available plans
$plans_stmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC");
$all_plans = $plans_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buy Plan - WiFi Billing</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container py-4">
    <h2>Buy a Plan</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
    <div class="row">
      <?php foreach ($all_plans as $plan): ?>
        <div class="col-md-4 mb-3">
          <div class="card shadow">
            <div class="card-body text-center">
              <h5 class="card-title"><?= htmlspecialchars($plan['name']) ?></h5>
              <p class="card-text"><?= $plan['duration_hours'] % 24 === 0 ? ($plan['duration_hours']/24) . ' days' : $plan['duration_hours'] . ' hours' ?></p>
              <h3>$<?= number_format($plan['price'], 2) ?></h3>
              <form method="post" action="purchase.php">
                <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                <button class="btn btn-primary" type="submit">Buy Now</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
