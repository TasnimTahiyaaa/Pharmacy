<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();

$page=max(1,(int)($_GET['page']??1));$perPage=20;$offset=($page-1)*$perPage;
$total=$db->query("SELECT COUNT(*) as c FROM payments")->fetch_assoc()['c'];
$totalPages=ceil($total/$perPage);
$payments=$db->query("SELECT p.*,o.customer_name,o.type as order_type,o.status as order_status FROM payments p JOIN orders o ON o.id=p.order_id ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);

$totals=$db->query("SELECT SUM(amount) as total, SUM(CASE WHEN status='completed' THEN amount ELSE 0 END) as completed, SUM(CASE WHEN method='bkash' THEN amount ELSE 0 END) as bkash, SUM(CASE WHEN method='cash' THEN amount ELSE 0 END) as cash FROM payments")->fetch_assoc();

$pageTitle='Payments';
include '../includes/admin_header.php';
$sc=['completed'=>'badge-success','pending'=>'badge-warning','failed'=>'badge-danger'];
?>

<div class="admin-page-header"><div><h1>Payments</h1><p>All payment transactions.</p></div></div>

<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
  <div class="card kpi-card kpi-primary"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div><div class="kpi-value">৳<?=number_format($totals['total'],0)?></div><div class="kpi-label">Total Transactions</div></div>
  <div class="card kpi-card kpi-green"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="kpi-value">৳<?=number_format($totals['completed'],0)?></div><div class="kpi-label">Completed</div></div>
  <div class="card kpi-card kpi-purple"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v1m0 8v1"/></svg></div><div class="kpi-value">৳<?=number_format($totals['bkash'],0)?></div><div class="kpi-label">bKash Revenue</div></div>
  <div class="card kpi-card kpi-teal"><div class="kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 100 7h5a3.5 3.5 0 110 7H6"/></svg></div><div class="kpi-value">৳<?=number_format($totals['cash'],0)?></div><div class="kpi-label">Cash Revenue</div></div>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>#</th><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th>Status</th><th>Transaction ID</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach($payments as $p): ?>
    <tr>
      <td style="color:var(--muted);font-size:12px"><?=$p['id']?></td>
      <td><a href="orders.php" style="color:var(--primary);font-weight:700">#<?=$p['order_id']?></a></td>
      <td style="font-size:13px;font-weight:600"><?=htmlspecialchars($p['customer_name'])?></td>
      <td><span class="badge <?=$p['method']==='bkash'?'badge-purple':'badge-gray'?>" style="<?=$p['method']==='bkash'?'background:#faf5ff;color:#7c3aed;border-color:#e9d5ff':''?>"><?=ucfirst($p['method'])?></span></td>
      <td style="font-weight:800;color:var(--primary)">৳<?=number_format($p['amount'],2)?></td>
      <td><span class="badge <?=$sc[$p['status']]??'badge-gray'?>"><?=ucfirst($p['status'])?></span></td>
      <td style="font-size:12px;font-family:monospace;color:var(--muted)"><?=$p['transaction_id']?htmlspecialchars($p['transaction_id']):'—'?></td>
      <td style="font-size:12px;color:var(--muted)"><?=date('M j, Y',strtotime($p['created_at']))?></td>
    </tr>
    <?php endforeach;?>
    <?php if(empty($payments)):?><tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No payments yet</td></tr><?php endif;?>
    </tbody>
  </table>
</div>

<?php if($totalPages>1):?>
<div class="pagination" style="margin-top:20px">
  <?php for($i=1;$i<=$totalPages;$i++):?><a href="?page=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a><?php endfor;?>
</div>
<?php endif;?>
<?php include '../includes/admin_footer.php'; ?>
