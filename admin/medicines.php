<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = sanitize($_POST['name']); $generic = sanitize($_POST['generic_name']??'');
    $desc = sanitize($_POST['description']??''); $catId = (int)($_POST['category_id']??0);
    $price = (float)$_POST['price']; $stock = (int)$_POST['stock_quantity'];
    $thresh = (int)($_POST['low_stock_threshold']??10); $unit = sanitize($_POST['unit']??'tablet');
    $mfr = sanitize($_POST['manufacturer']??''); $rx = isset($_POST['requires_prescription'])?1:0;

    if (isset($_POST['delete_med'])) {
        $stmt = $db->prepare("UPDATE medicines SET is_active=0 WHERE id=?"); $stmt->bind_param('i',$id); $stmt->execute();
        $_SESSION['flash']=['msg'=>'Medicine deactivated','type'=>'success']; header('Location: medicines.php'); exit;
    }
    if ($id) {
        $stmt = $db->prepare("UPDATE medicines SET name=?,generic_name=?,description=?,category_id=?,price=?,stock_quantity=?,low_stock_threshold=?,unit=?,manufacturer=?,requires_prescription=? WHERE id=?");
        $stmt->bind_param('sssiidissi',$name,$generic,$desc,$catId,$price,$stock,$thresh,$unit,$mfr,$rx,$id);
    } else {
        $stmt = $db->prepare("INSERT INTO medicines (name,generic_name,description,category_id,price,stock_quantity,low_stock_threshold,unit,manufacturer,requires_prescription,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,1)");
        $stmt->bind_param('sssiidissi',$name,$generic,$desc,$catId,$price,$stock,$thresh,$unit,$mfr,$rx);
    }
    $stmt->execute();
    if ($id) checkLowStock($id); else { $newId=$db->insert_id; checkLowStock($newId); }
    $_SESSION['flash']=['msg'=>$id?'Medicine updated':'Medicine added','type'=>'success'];
    header('Location: medicines.php'); exit;
}

$search = sanitize($_GET['search']??'');
$page=max(1,(int)($_GET['page']??1));$perPage=15;$offset=($page-1)*$perPage;
$where = ["m.is_active=1"];
if ($search) $where[]="(m.name LIKE '%".addslashes($search)."%' OR m.generic_name LIKE '%".addslashes($search)."%')";
$whereSQL='WHERE '.implode(' AND ',$where);
$total=$db->query("SELECT COUNT(*) as c FROM medicines m $whereSQL")->fetch_assoc()['c'];
$totalPages=ceil($total/$perPage);
$medicines=$db->query("SELECT m.*,c.name as cat_name FROM medicines m LEFT JOIN categories c ON c.id=m.category_id $whereSQL ORDER BY m.created_at DESC LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);

$pageTitle='Medicines';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
  <div><h1>Medicines</h1><p>Manage your medicine catalogue.</p></div>
  <button class="btn btn-primary" data-open-modal="add-medicine-modal">+ Add Medicine</button>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="add-medicine-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-header"><h2>Add New Medicine</h2><button class="modal-close">✕</button></div>
    <form method="POST">
      <div class="grid-2"><div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div><div class="form-group"><label class="form-label">Generic Name</label><input type="text" name="generic_name" class="form-control"></div></div>
      <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
      <div class="grid-2"><div class="form-group"><label class="form-label">Category</label><select name="category_id" class="form-control form-select"><option value="">— Select —</option><?php foreach($categories as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach;?></select></div><div class="form-group"><label class="form-label">Manufacturer</label><input type="text" name="manufacturer" class="form-control"></div></div>
      <div class="grid-2"><div class="form-group"><label class="form-label">Price (৳) *</label><input type="number" name="price" class="form-control" step="0.01" min="0" required></div><div class="form-group"><label class="form-label">Unit</label><select name="unit" class="form-control form-select"><option>tablet</option><option>capsule</option><option>bottle</option><option>tube</option><option>injection</option><option>syrup</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label class="form-label">Stock Qty *</label><input type="number" name="stock_quantity" class="form-control" min="0" value="0" required></div><div class="form-group"><label class="form-label">Low Stock Threshold</label><input type="number" name="low_stock_threshold" class="form-control" min="1" value="10"></div></div>
      <div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600"><input type="checkbox" name="requires_prescription"> Requires Prescription</label></div>
      <button type="submit" class="btn btn-primary w-100">Add Medicine</button>
    </form>
  </div>
</div>

<div class="card" style="padding:16px;margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px">
    <div class="search-wrap" style="flex:1">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="search" class="form-control search-input" placeholder="Search medicines..." value="<?=htmlspecialchars($search)?>">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    <?php if($search):?><a href="medicines.php" class="btn btn-outline btn-sm">Clear</a><?php endif;?>
  </form>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Rx</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($medicines as $m): ?>
    <?php $low=$m['stock_quantity']<=$m['low_stock_threshold']; ?>
    <tr>
      <td><p style="font-weight:700;font-size:14px"><?=htmlspecialchars($m['name'])?></p><p style="font-size:11px;color:var(--muted)"><?=htmlspecialchars($m['generic_name']??'')?> · <?=htmlspecialchars($m['manufacturer']??'')?></p></td>
      <td><span class="badge badge-primary"><?=htmlspecialchars($m['cat_name']??'—')?></span></td>
      <td style="font-weight:700;color:var(--primary)">৳<?=number_format($m['price'],2)?></td>
      <td style="font-weight:700;<?=$low?'color:var(--warning)':''?>"><?=$m['stock_quantity']?> <?=htmlspecialchars($m['unit'])?></td>
      <td><?=$m['stock_quantity']<=0?'<span class="badge badge-danger">Out</span>':($low?'<span class="badge badge-warning">Low</span>':'<span class="badge badge-success">OK</span>')?></td>
      <td><?=$m['requires_prescription']?'<span class="badge badge-info">Yes</span>':'<span class="badge badge-gray">No</span>'?></td>
      <td style="display:flex;gap:6px;flex-wrap:wrap">
        <button class="btn btn-outline btn-sm" data-open-modal="edit-med-<?=$m['id']?>">Edit</button>
        <form method="POST" style="display:inline"><input type="hidden" name="id" value="<?=$m['id']?>"><button type="submit" name="delete_med" value="1" class="btn btn-danger btn-sm" data-confirm="Deactivate this medicine?">Deactivate</button></form>
      </td>
    </tr>
    <!-- Edit Modal -->
    <div class="modal-overlay" id="edit-med-<?=$m['id']?>">
      <div class="modal" style="max-width:560px">
        <div class="modal-header"><h2>Edit — <?=htmlspecialchars($m['name'])?></h2><button class="modal-close">✕</button></div>
        <form method="POST">
          <input type="hidden" name="id" value="<?=$m['id']?>">
          <div class="grid-2"><div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="<?=htmlspecialchars($m['name'])?>" required></div><div class="form-group"><label class="form-label">Generic Name</label><input type="text" name="generic_name" class="form-control" value="<?=htmlspecialchars($m['generic_name']??'')?>"></div></div>
          <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?=htmlspecialchars($m['description']??'')?></textarea></div>
          <div class="grid-2"><div class="form-group"><label class="form-label">Category</label><select name="category_id" class="form-control form-select"><option value="">— Select —</option><?php foreach($categories as $c):?><option value="<?=$c['id']?>" <?=$c['id']==$m['category_id']?'selected':''?>><?=htmlspecialchars($c['name'])?></option><?php endforeach;?></select></div><div class="form-group"><label class="form-label">Manufacturer</label><input type="text" name="manufacturer" class="form-control" value="<?=htmlspecialchars($m['manufacturer']??'')?>"></div></div>
          <div class="grid-2"><div class="form-group"><label class="form-label">Price (৳)</label><input type="number" name="price" class="form-control" step="0.01" value="<?=$m['price']?>"></div><div class="form-group"><label class="form-label">Unit</label><select name="unit" class="form-control form-select"><?php foreach(['tablet','capsule','bottle','tube','injection','syrup'] as $u):?><option <?=$u==$m['unit']?'selected':''?>><?=$u?></option><?php endforeach;?></select></div></div>
          <div class="grid-2"><div class="form-group"><label class="form-label">Stock Qty</label><input type="number" name="stock_quantity" class="form-control" value="<?=$m['stock_quantity']?>"></div><div class="form-group"><label class="form-label">Low Stock Threshold</label><input type="number" name="low_stock_threshold" class="form-control" value="<?=$m['low_stock_threshold']?>"></div></div>
          <div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600"><input type="checkbox" name="requires_prescription" <?=$m['requires_prescription']?'checked':''?>> Requires Prescription</label></div>
          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($medicines)):?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No medicines found</td></tr><?php endif;?>
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
