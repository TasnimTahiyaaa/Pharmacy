<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = (int)$_POST['medicine_id'];
    $qty = max(0, (int)$_POST['stock_quantity']);
    $thresh = max(1, (int)$_POST['low_stock_threshold']);
    $stmt = $db->prepare("UPDATE medicines SET stock_quantity=?, low_stock_threshold=? WHERE id=?");
    $stmt->bind_param('iii', $qty, $thresh, $id);
    $stmt->execute();
    checkLowStock($id);
    $_SESSION['flash'] = ['msg'=>'Stock updated successfully', 'type'=>'success'];
    header('Location: inventory.php'); exit;
}

$search = sanitize($_GET['search'] ?? '');
$lowOnly = isset($_GET['low_stock']);
$page = max(1,(int)($_GET['page']??1)); $perPage = 20; $offset = ($page-1)*$perPage;

$where = ["m.is_active=1"];
if ($search) { $where[] = "(m.name LIKE '%".addslashes($search)."%' OR m.generic_name LIKE '%".addslashes($search)."%')"; }
if ($lowOnly) { $where[] = "m.stock_quantity <= m.low_stock_threshold"; }
$whereSQL = 'WHERE '.implode(' AND ',$where);

$total = $db->query("SELECT COUNT(*) as c FROM medicines m $whereSQL")->fetch_assoc()['c'];
$totalPages = ceil($total/$perPage);
$medicines = $db->query("SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON c.id=m.category_id $whereSQL ORDER BY m.stock_quantity ASC LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
$lowCount = $db->query("SELECT COUNT(*) as c FROM medicines WHERE stock_quantity <= low_stock_threshold AND is_active=1")->fetch_assoc()['c'];

$pageTitle = 'Inventory';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Inventory</h1>
    <p>Monitor and manage medicine stock levels.</p>
  </div>
  <?php if ($lowCount > 0): ?>
  <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:10px 16px;display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#b45309">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <?= $lowCount ?> low-stock item(s)
  </div>
  <?php endif; ?>
</div>

<div class="card" style="padding:20px;margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
    <div class="search-wrap" style="flex:1;min-width:200px">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="search" class="form-control search-input" placeholder="Search medicines..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;font-weight:600">
      <input type="checkbox" name="low_stock" <?= $lowOnly?'checked':'' ?> onchange="this.form.submit()">
      Low Stock Only
    </label>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <?php if ($search||$lowOnly): ?><a href="inventory.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
  </form>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>#</th><th>Medicine</th><th>Category</th><th>Stock</th><th>Threshold</th><th>Status</th><th>Price</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($medicines as $m): ?>
    <?php $lowStock = $m['stock_quantity'] <= $m['low_stock_threshold']; ?>
    <tr>
      <td style="color:var(--muted);font-size:12px"><?= $m['id'] ?></td>
      <td><p style="font-weight:700;font-size:14px"><?= htmlspecialchars($m['name']) ?></p><p style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($m['generic_name']??'') ?></p></td>
      <td><span class="badge badge-primary"><?= htmlspecialchars($m['category_name']??'—') ?></span></td>
      <td style="font-weight:700;<?= $lowStock?'color:var(--warning)':'' ?>"><?= $m['stock_quantity'] ?> <?= htmlspecialchars($m['unit']) ?></td>
      <td style="color:var(--muted)"><?= $m['low_stock_threshold'] ?></td>
      <td><?php if ($m['stock_quantity'] <= 0): ?><span class="badge badge-danger">Out</span><?php elseif($lowStock): ?><span class="badge badge-warning">Low Stock</span><?php else: ?><span class="badge badge-success">In Stock</span><?php endif; ?></td>
      <td style="font-weight:700;color:var(--primary)">৳<?= number_format($m['price'],2) ?></td>
      <td>
        <button class="btn btn-outline btn-sm" data-open-modal="edit-<?= $m['id'] ?>">Update Stock</button>
        <!-- Modal -->
        <div class="modal-overlay" id="edit-<?= $m['id'] ?>">
          <div class="modal">
            <div class="modal-header"><h2>Update Stock — <?= htmlspecialchars($m['name']) ?></h2><button class="modal-close">✕</button></div>
            <form method="POST">
              <input type="hidden" name="update_stock" value="1">
              <input type="hidden" name="medicine_id" value="<?= $m['id'] ?>">
              <div class="form-group"><label class="form-label">Stock Quantity</label><input type="number" name="stock_quantity" class="form-control" value="<?= $m['stock_quantity'] ?>" min="0" required></div>
              <div class="form-group"><label class="form-label">Low Stock Threshold</label><input type="number" name="low_stock_threshold" class="form-control" value="<?= $m['low_stock_threshold'] ?>" min="1" required></div>
              <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            </form>
          </div>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($medicines)): ?><tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No medicines found</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination" style="margin-top:20px">
  <?php $base="?search=".urlencode($search).($lowOnly?'&low_stock=1':'');
  for($i=1;$i<=$totalPages;$i++): ?>
  <a href="<?= $base ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '../includes/admin_footer.php'; ?>
