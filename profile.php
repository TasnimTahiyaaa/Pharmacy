<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
requireLogin();
$db = getDB();
$user = getCurrentUser();
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    if (empty($name)) { $error = 'Name is required.'; }
    else {
        $stmt = $db->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param('sssi', $name, $phone, $address, $user['id']);
        $stmt->execute();
        $_SESSION['name'] = $name;
        $success = 'Profile updated successfully!';
        $user = getCurrentUser();
    }
}

$stmt = $db->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id=? GROUP BY o.id ORDER BY o.created_at DESC LIMIT 5");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$recentOrders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'My Profile';
include 'includes/header.php';
?>
<div class="container" style="padding-top:32px;padding-bottom:48px">
  <div class="page-header"><h1>My Profile</h1><p>Manage your account information</p></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:900px">
    <div class="card p-6">
      <h2 style="font-size:17px;font-weight:700;margin-bottom:20px">Personal Information</h2>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
        <div class="form-group"><label class="form-label">Email Address</label><input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.6"></div>
        <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+880 1700-000000"></div>
        <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2" placeholder="Your delivery address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea></div>
        <div class="form-group"><label class="form-label">Member Since</label><input type="text" class="form-control" value="<?= date('F j, Y', strtotime($user['created_at'])) ?>" disabled style="opacity:.6"></div>
        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
      </form>
    </div>
    <div class="card p-6">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <h2 style="font-size:17px;font-weight:700">Recent Orders</h2>
        <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
      </div>
      <?php if (empty($recentOrders)): ?>
      <div style="text-align:center;padding:40px 0;color:var(--muted)">No orders yet</div>
      <?php else: ?>
      <?php foreach ($recentOrders as $order): ?>
      <?php $sc = ['pending'=>'badge-warning','processing'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger']; ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
        <div>
          <p style="font-size:13px;font-weight:700">#<?= $order['id'] ?> · <?= $order['item_count'] ?> item(s)</p>
          <p style="font-size:12px;color:var(--muted)"><?= date('M j, Y', strtotime($order['created_at'])) ?></p>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <span class="badge <?= $sc[$order['status']] ?? 'badge-gray' ?>"><?= $order['status'] ?></span>
          <span style="font-weight:800;color:var(--primary);font-size:13px">৳ <?= number_format($order['total_amount'], 0) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
