<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();

$stats = [];
$r = $db->query("SELECT COUNT(*) as c FROM medicines WHERE is_active=1")->fetch_assoc(); $stats['medicines'] = $r['c'];
$r = $db->query("SELECT COUNT(*) as c FROM medicines WHERE stock_quantity <= low_stock_threshold AND is_active=1")->fetch_assoc(); $stats['low_stock'] = $r['c'];
$r = $db->query("SELECT COUNT(*) as c FROM orders WHERE DATE(created_at)=CURDATE()")->fetch_assoc(); $stats['today_orders'] = $r['c'];
$r = $db->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetch_assoc(); $stats['today_revenue'] = $r['s'];
$r = $db->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc(); $stats['customers'] = $r['c'];
$r = $db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc(); $stats['pending'] = $r['c'];
$r = $db->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) AND status!='cancelled'")->fetch_assoc(); $stats['monthly_revenue'] = $r['s'];
$r = $db->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE type='online' AND status!='cancelled'")->fetch_assoc(); $stats['online_revenue'] = $r['s'];
$r = $db->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE type='offline' AND status!='cancelled'")->fetch_assoc(); $stats['offline_revenue'] = $r['s'];

$recentOrders = $db->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard';
include '../includes/admin_header.php';
$statusColors = ['pending'=>'badge-warning','processing'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'];
?>

<div class="admin-page-header">
  <div><h1>Dashboard</h1><p>Welcome back to Noor Pharmacy admin.</p></div>
</div>

<!-- KPIs -->
<div class="kpi-grid" style="margin-bottom:24px">
  <?php $kpis = [
    ['label'=>'Total Medicines','value'=>$stats['medicines'],'class'=>'kpi-primary','icon'=>'M10.5 20H4a2 2 0 01-2-2V6a2 2 0 012-2h3.9a2 2 0 011.69.9l.81 1.2a2 2 0 001.67.9H20a2 2 0 012 2v2'],
    ['label'=>'Low Stock Items','value'=>$stats['low_stock'],'class'=>'kpi-amber','icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ['label'=>"Today's Orders",'value'=>$stats['today_orders'],'class'=>'kpi-blue','icon'=>'M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z'],
    ['label'=>"Today's Revenue",'value'=>'৳ '.number_format($stats['today_revenue'],0),'class'=>'kpi-green','icon'=>'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
    ['label'=>'Total Customers','value'=>$stats['customers'],'class'=>'kpi-purple','icon'=>'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75'],
    ['label'=>'Pending Orders','value'=>$stats['pending'],'class'=>'kpi-orange','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label'=>'Monthly Revenue','value'=>'৳ '.number_format($stats['monthly_revenue'],0),'class'=>'kpi-teal','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
    ['label'=>'Online Revenue','value'=>'৳ '.number_format($stats['online_revenue'],0),'class'=>'kpi-emerald','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
  ]; foreach ($kpis as $k): ?>
  <div class="card kpi-card <?= $k['class'] ?>">
    <div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="<?= $k['icon'] ?>"/></svg></div>
    <div class="kpi-value"><?= $k['value'] ?></div>
    <div class="kpi-label"><?= $k['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts + Recent Orders -->
<div class="charts-row">
  <!-- Revenue Pie -->
  <div class="chart-box">
    <h3>Revenue Breakdown</h3>
    <?php $total = $stats['online_revenue'] + $stats['offline_revenue']; ?>
    <?php if ($total > 0): ?>
    <canvas id="revPie" class="pie-canvas" width="150" height="150"></canvas>
    <div class="pie-legend">
      <div class="pie-legend-item"><div class="pie-dot" style="background:#1a9e72"></div>Online ৳<?= number_format($stats['online_revenue'],0) ?></div>
      <div class="pie-legend-item"><div class="pie-dot" style="background:#1a8db5"></div>Offline ৳<?= number_format($stats['offline_revenue'],0) ?></div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded',function(){
      drawPie('revPie',[{value:<?= $stats['online_revenue'] ?>},{value:<?= $stats['offline_revenue'] ?>}],['#1a9e72','#1a8db5']);
    });
    </script>
    <?php else: ?>
    <div style="text-align:center;padding:40px 0;color:var(--muted);font-size:14px">No revenue data yet</div>
    <?php endif; ?>
  </div>

  <!-- Recent Orders -->
  <div class="chart-box">
    <h3>Recent Orders</h3>
    <?php if (empty($recentOrders)): ?>
    <div style="text-align:center;padding:40px 0;color:var(--muted);font-size:14px">No orders yet</div>
    <?php else: foreach ($recentOrders as $order): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:center;gap:10px">
        <div class="order-id">#<?= $order['id'] ?></div>
        <div>
          <p style="font-size:13px;font-weight:700"><?= htmlspecialchars($order['customer_name']) ?></p>
          <p style="font-size:11px;color:var(--muted)"><?= $order['type'] ?> · <?= $order['payment_method'] ?></p>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <span class="badge <?= $statusColors[$order['status']] ?? 'badge-gray' ?>"><?= $order['status'] ?></span>
        <span style="font-weight:800;color:var(--primary);font-size:13px">৳<?= number_format($order['total_amount'],0) ?></span>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
