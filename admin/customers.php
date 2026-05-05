<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();

$search = sanitize($_GET['search'] ?? '');
$page = max(1,(int)($_GET['page']??1)); $perPage = 20; $offset = ($page-1)*$perPage;
$where = ["u.role='customer'"];
if ($search) $where[] = "(u.name LIKE '%".addslashes($search)."%' OR u.email LIKE '%".addslashes($search)."%' OR u.phone LIKE '%".addslashes($search)."%')";
$whereSQL = 'WHERE '.implode(' AND ', $where);
$total = $db->query("SELECT COUNT(*) as c FROM users u $whereSQL")->fetch_assoc()['c'];
$totalPages = ceil($total/$perPage);
$customers = $db->query("SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total_spent FROM users u LEFT JOIN orders o ON o.user_id=u.id AND o.status!='cancelled' $whereSQL GROUP BY u.id ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Customers';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
  <div><h1>Customers</h1><p>Registered customer accounts.</p></div>
  <span style="font-size:14px;color:var(--muted)"><strong style="color:var(--foreground)"><?=$total?></strong> customers total</span>
</div>

<div class="card" style="padding:16px;margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px">
    <div class="search-wrap" style="flex:1">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="search" class="form-control search-input" placeholder="Search by name, email or phone..." value="<?=htmlspecialchars($search)?>">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    <?php if($search):?><a href="customers.php" class="btn btn-outline btn-sm">Clear</a><?php endif;?>
  </form>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>#</th><th>Customer</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach($customers as $c): ?>
    <tr>
      <td style="color:var(--muted);font-size:12px"><?=$c['id']?></td>
      <td>
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0"><?=strtoupper(substr($c['name'],0,1))?></div>
          <span style="font-weight:700;font-size:14px"><?=htmlspecialchars($c['name'])?></span>
        </div>
      </td>
      <td style="font-size:13px;color:var(--muted)"><?=htmlspecialchars($c['email'])?></td>
      <td style="font-size:13px"><?=htmlspecialchars($c['phone']??'—')?></td>
      <td><span class="badge badge-primary"><?=$c['order_count']?> orders</span></td>
      <td style="font-weight:700;color:var(--primary)">৳<?=number_format($c['total_spent'],2)?></td>
      <td style="font-size:12px;color:var(--muted)"><?=date('M j, Y',strtotime($c['created_at']))?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($customers)):?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No customers found</td></tr><?php endif;?>
    </tbody>
  </table>
</div>

<?php if($totalPages>1):?>
<div class="pagination" style="margin-top:20px">
  <?php for($i=1;$i<=$totalPages;$i++):?>
  <a href="?search=<?=urlencode($search)?>&page=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
  <?php endfor;?>
</div>
<?php endif;?>
<?php include '../includes/admin_footer.php'; ?>
