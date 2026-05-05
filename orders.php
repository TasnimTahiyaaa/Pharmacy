<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
requireLogin();
$db = getDB();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id=? GROUP BY o.id ORDER BY o.created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'My Orders';
include 'includes/header.php';
$statusColors = ['pending'=>'badge-warning','processing'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'];
?>
<div class="container" style="padding-top:32px;padding-bottom:48px">
  <div class="page-header"><h1>My Orders</h1><p>Track your order history and status</p></div>
  <?php if (empty($orders)): ?>
  <div class="card" style="text-align:center;padding:60px 20px">
    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--muted);margin-bottom:16px"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    <h3 style="margin-bottom:8px">No orders yet</h3>
    <p style="color:var(--muted);margin-bottom:20px">Start shopping to see your orders here.</p>
    <a href="medicines.php" class="btn btn-primary">Browse Medicines</a>
  </div>
  <?php else: ?>
  <?php foreach ($orders as $order): ?>
  <?php
    $itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id=?");
    $itemStmt->bind_param('i', $order['id']);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
  ?>
  <div class="card order-card">
    <div class="order-header" data-id="<?= $order['id'] ?>">
      <div style="display:flex;align-items:center;gap:12px">
        <div class="order-id">#<?= $order['id'] ?></div>
        <div>
          <p style="font-size:14px;font-weight:700"><?= $order['item_count'] ?> item(s) · <?= ucfirst($order['type']) ?> · <?= ucfirst($order['payment_method']) ?></p>
          <p style="font-size:12px;color:var(--muted)"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <span class="badge <?= $statusColors[$order['status']] ?? 'badge-gray' ?>"><?= $order['status'] ?></span>
        <span style="font-weight:800;color:var(--primary)">৳ <?= number_format($order['total_amount'], 2) ?></span>
        <svg class="expand-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--muted);transition:transform .2s"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
    </div>
    <div class="order-details" id="order-details-<?= $order['id'] ?>" style="display:none">
      <?php foreach ($items as $item): ?>
      <div class="order-item-row">
        <span><?= htmlspecialchars($item['medicine_name']) ?> × <?= $item['quantity'] ?></span>
        <span style="font-weight:700">৳ <?= number_format($item['subtotal'], 2) ?></span>
      </div>
      <?php endforeach; ?>
      <div style="display:flex;gap:16px;margin-top:12px;font-size:13px">
        <span>Payment: <strong><?= ucfirst($order['payment_method']) ?></strong></span>
        <span>Status: <strong><?= ucfirst($order['payment_status']) ?></strong></span>
        <?php if ($order['notes']): ?><span>Note: <?= htmlspecialchars($order['notes']) ?></span><?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
