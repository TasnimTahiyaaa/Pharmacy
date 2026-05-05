<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['order_id'];
    $status = in_array($_POST['status'], ['pending','processing','completed','cancelled']) ? $_POST['status'] : 'pending';
    $payStatus = $_POST['payment_status'] ?? null;
    $stmt = $db->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    if ($payStatus && in_array($payStatus, ['pending','paid','failed'])) {
        $stmt2 = $db->prepare("UPDATE orders SET payment_status=? WHERE id=?");
        $stmt2->bind_param('si', $payStatus, $id);
        $stmt2->execute();
        $db->query("UPDATE payments SET status='".addslashes($payStatus)."' WHERE order_id=$id");
    }
    $_SESSION['flash'] = ['msg'=>'Order #'.$id.' updated', 'type'=>'success'];
    header('Location: orders.php'); exit;
}

// Create offline order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_offline'])) {
    $custName = sanitize($_POST['customer_name']);
    $custPhone = sanitize($_POST['customer_phone']??'');
    $payMethod = in_array($_POST['payment_method'],['bkash','cash'])?$_POST['payment_method']:'cash';
    $notes = sanitize($_POST['notes']??'');
    $itemIds = $_POST['item_medicine_id'] ?? [];
    $itemQtys = $_POST['item_quantity'] ?? [];

    $total = 0; $orderItems = [];
    foreach ($itemIds as $idx => $medId) {
        $medId = (int)$medId; $qty = max(1,(int)($itemQtys[$idx]??1));
        if (!$medId) continue;
        $med = $db->query("SELECT id,name,price,stock_quantity FROM medicines WHERE id=$medId AND is_active=1")->fetch_assoc();
        if ($med) { $sub = $med['price']*$qty; $total += $sub; $orderItems[]=['med'=>$med,'qty'=>$qty,'sub'=>$sub]; }
    }
    if (!empty($orderItems) && $custName) {
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("INSERT INTO orders (customer_name,customer_phone,type,status,payment_method,payment_status,total_amount,notes) VALUES (?,?,'offline','completed',?,'paid',?,?)");
            $stmt->bind_param('sssds', $custName,$custPhone,$payMethod,$total,$notes);
            $stmt->execute(); $orderId = $db->insert_id;
            foreach ($orderItems as $oi) {
                $db->query("INSERT INTO order_items (order_id,medicine_id,medicine_name,quantity,unit_price,subtotal) VALUES ($orderId,{$oi['med']['id']},'{$db->real_escape_string($oi['med']['name'])}',{$oi['qty']},{$oi['med']['price']},{$oi['sub']})");
                $db->query("UPDATE medicines SET stock_quantity=stock_quantity-{$oi['qty']} WHERE id={$oi['med']['id']}");
                checkLowStock($oi['med']['id']);
            }
            $db->query("INSERT INTO payments (order_id,amount,method,status) VALUES ($orderId,$total,'$payMethod','completed')");
            $db->commit();
            $_SESSION['flash'] = ['msg'=>'Offline order #'.$orderId.' created', 'type'=>'success'];
        } catch(Exception $e) { $db->rollback(); $_SESSION['flash']=['msg'=>'Failed to create order','type'=>'error']; }
    } else { $_SESSION['flash']=['msg'=>'Invalid order data','type'=>'error']; }
    header('Location: orders.php'); exit;
}

$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$page=max(1,(int)($_GET['page']??1));$perPage=20;$offset=($page-1)*$perPage;

$where = ["1=1"];
if ($statusFilter && in_array($statusFilter,['pending','processing','completed','cancelled'])) $where[]="o.status='$statusFilter'";
if ($typeFilter && in_array($typeFilter,['online','offline'])) $where[]="o.type='$typeFilter'";
$whereSQL = 'WHERE '.implode(' AND ',$where);

$total=$db->query("SELECT COUNT(*) as c FROM orders o $whereSQL")->fetch_assoc()['c'];
$totalPages=ceil($total/$perPage);
$orders=$db->query("SELECT o.*,u.name as user_name FROM orders o LEFT JOIN users u ON u.id=o.user_id $whereSQL ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
$allMeds=$db->query("SELECT id,name,price,stock_quantity FROM medicines WHERE is_active=1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$pageTitle='Orders';
include '../includes/admin_header.php';
$sc=['pending'=>'badge-warning','processing'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'];
?>

<div class="admin-page-header">
  <div><h1>Orders</h1><p>Manage all customer and offline orders.</p></div>
  <button class="btn btn-primary" data-open-modal="offline-order-modal">+ New Offline Order</button>
</div>

<!-- Filters -->
<div class="card" style="padding:16px;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;flex:1">
    <select name="status" class="form-control form-select" style="width:160px" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <?php foreach(['pending','processing','completed','cancelled'] as $s):?>
      <option value="<?=$s?>" <?=$statusFilter===$s?'selected':''?>><?=ucfirst($s)?></option>
      <?php endforeach;?>
    </select>
    <select name="type" class="form-control form-select" style="width:140px" onchange="this.form.submit()">
      <option value="">All Types</option>
      <option value="online" <?=$typeFilter==='online'?'selected':''?>>Online</option>
      <option value="offline" <?=$typeFilter==='offline'?'selected':''?>>Offline</option>
    </select>
    <?php if($statusFilter||$typeFilter):?><a href="orders.php" class="btn btn-outline btn-sm">Clear</a><?php endif;?>
  </form>
  <span style="font-size:13px;color:var(--muted)"><?=$total?> order(s) found</span>
</div>

<!-- Orders List -->
<?php foreach($orders as $order): ?>
<?php $items=$db->query("SELECT * FROM order_items WHERE order_id={$order['id']}")->fetch_all(MYSQLI_ASSOC); ?>
<div class="card order-card" style="margin-bottom:10px">
  <div class="order-header" data-id="<?=$order['id']?>">
    <div style="display:flex;align-items:center;gap:12px">
      <div class="order-id">#<?=$order['id']?></div>
      <div>
        <p style="font-size:14px;font-weight:700"><?=htmlspecialchars($order['customer_name'])?> <?=$order['customer_phone']?'<span style="font-weight:400;color:var(--muted);font-size:12px">· '.$order['customer_phone'].'</span>':''?></p>
        <p style="font-size:12px;color:var(--muted)"><?=date('M j, Y g:i A',strtotime($order['created_at']))?> · <?=ucfirst($order['type'])?>  · <?=ucfirst($order['payment_method'])?></p>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <span class="badge <?=$sc[$order['status']]??'badge-gray'?>"><?=$order['status']?></span>
      <span style="font-weight:800;color:var(--primary)">৳<?=number_format($order['total_amount'],2)?></span>
      <svg class="expand-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--muted);transition:transform .2s"><polyline points="9 18 15 12 9 6"/></svg>
    </div>
  </div>
  <div class="order-details" id="order-details-<?=$order['id']?>" style="display:none">
    <?php foreach($items as $it):?>
    <div class="order-item-row"><span><?=htmlspecialchars($it['medicine_name'])?> × <?=$it['quantity']?></span><span style="font-weight:700">৳<?=number_format($it['subtotal'],2)?></span></div>
    <?php endforeach;?>
    <?php if($order['notes']):?><p style="font-size:13px;color:var(--muted);margin-top:8px">Note: <?=htmlspecialchars($order['notes'])?></p><?php endif;?>
    <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
      <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <input type="hidden" name="update_status" value="1">
        <input type="hidden" name="order_id" value="<?=$order['id']?>">
        <select name="status" class="form-control form-select" style="width:150px">
          <?php foreach(['pending','processing','completed','cancelled'] as $s):?><option value="<?=$s?>" <?=$s===$order['status']?'selected':''?>><?=ucfirst($s)?></option><?php endforeach;?>
        </select>
        <select name="payment_status" class="form-control form-select" style="width:140px">
          <?php foreach(['pending','paid','failed'] as $ps):?><option value="<?=$ps?>" <?=$ps===$order['payment_status']?'selected':''?>><?=ucfirst($ps)?></option><?php endforeach;?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Update</button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php if(empty($orders)):?><div class="card" style="padding:40px;text-align:center;color:var(--muted)">No orders found</div><?php endif;?>

<!-- Pagination -->
<?php if($totalPages>1):?>
<div class="pagination" style="margin-top:20px">
  <?php $base="?status=".urlencode($statusFilter)."&type=".urlencode($typeFilter);
  for($i=1;$i<=$totalPages;$i++):?>
  <a href="<?=$base?>&page=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
  <?php endfor;?>
</div>
<?php endif;?>

<!-- Offline Order Modal -->
<div class="modal-overlay" id="offline-order-modal">
  <div class="modal" style="max-width:600px">
    <div class="modal-header"><h2>New Offline Order</h2><button class="modal-close">✕</button></div>
    <form method="POST">
      <input type="hidden" name="create_offline" value="1">
      <div class="grid-2">
        <div class="form-group"><label class="form-label">Customer Name *</label><input type="text" name="customer_name" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Phone</label><input type="text" name="customer_phone" class="form-control"></div>
      </div>
      <div class="form-group"><label class="form-label">Payment Method</label><select name="payment_method" class="form-control form-select"><option value="cash">Cash</option><option value="bkash">bKash</option></select></div>
      <div class="form-group"><label class="form-label">Notes</label><input type="text" name="notes" class="form-control" placeholder="Optional notes..."></div>
      <hr style="margin:16px 0;border-color:var(--border)">
      <p style="font-weight:700;font-size:14px;margin-bottom:12px">Order Items</p>
      <div id="offline-items">
        <div class="offline-item" style="display:grid;grid-template-columns:1fr 100px auto;gap:8px;margin-bottom:8px">
          <select name="item_medicine_id[]" class="form-control form-select"><option value="">— Select Medicine —</option><?php foreach($allMeds as $m):?><option value="<?=$m['id']?>"><?=htmlspecialchars($m['name'])?> (৳<?=number_format($m['price'],2)?>)</option><?php endforeach;?></select>
          <input type="number" name="item_quantity[]" class="form-control" placeholder="Qty" min="1" value="1">
          <button type="button" onclick="this.parentElement.remove()" class="btn btn-danger btn-sm btn-icon">✕</button>
        </div>
      </div>
      <button type="button" class="btn btn-outline btn-sm" onclick="addOfflineItem()" style="margin-bottom:16px">+ Add Item</button>
      <button type="submit" class="btn btn-primary w-100">Create Order</button>
    </form>
  </div>
</div>
<script>
function addOfflineItem() {
  const container = document.getElementById('offline-items');
  const div = document.createElement('div');
  div.className = 'offline-item';
  div.style = 'display:grid;grid-template-columns:1fr 100px auto;gap:8px;margin-bottom:8px';
  div.innerHTML = `<select name="item_medicine_id[]" class="form-control form-select"><option value="">— Select Medicine —</option><?php foreach($allMeds as $m):?><option value="<?=$m['id']?>"><?=addslashes(htmlspecialchars($m['name']))?> (৳<?=number_format($m['price'],2)?>)</option><?php endforeach;?></select><input type="number" name="item_quantity[]" class="form-control" placeholder="Qty" min="1" value="1"><button type="button" onclick="this.parentElement.remove()" class="btn btn-danger btn-sm btn-icon">✕</button>`;
  container.appendChild(div);
}
</script>

<?php include '../includes/admin_footer.php'; ?>
