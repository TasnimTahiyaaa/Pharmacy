<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
$db = getDB();

$search = sanitize($_GET['search'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = ["m.is_active = 1"];
$params = [];
$types = '';
if ($search) { $where[] = "(m.name LIKE ? OR m.generic_name LIKE ? OR m.description LIKE ?)"; $s = "%$search%"; $params[] = $s; $params[] = $s; $params[] = $s; $types .= 'sss'; }
if ($categoryId) { $where[] = "m.category_id = ?"; $params[] = $categoryId; $types .= 'i'; }
$whereSQL = 'WHERE ' . implode(' AND ', $where);

$countSQL = "SELECT COUNT(*) as cnt FROM medicines m $whereSQL";
$countStmt = $db->prepare($countSQL);
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($total / $perPage);

$sql = "SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON c.id=m.category_id $whereSQL ORDER BY m.name LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$allParams = $params; $allParams[] = $perPage; $allParams[] = $offset;
$allTypes = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$medicines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Medicine Catalogue';
include 'includes/header.php';
?>
<div class="container" style="padding-top:32px;padding-bottom:48px">
  <div class="page-header">
    <h1>Medicine Catalogue</h1>
    <p>Browse our full range of authentic medicines and healthcare products.</p>
  </div>

  <!-- Search & Filters -->
<form method="GET" id="filterForm">
  <div class="filters-bar">
    <div class="search-wrap" style="flex:1;min-width:220px">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/>
        <path d="M21 21l-4.35-4.35"/>
      </svg>

      <!-- FIXED SEARCH INPUT -->
      <input 
        type="text" 
        id="searchInput"
        name="search" 
        class="form-control search-input" 
        placeholder="Search medicines..." 
        value="<?= htmlspecialchars($search) ?>"
      >
    </div>

    <?php if ($categoryId): ?>
      <input type="hidden" name="category" value="<?= $categoryId ?>">
    <?php endif; ?>
  </div>
</form>

<script>
document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('filterForm').submit();
    }
});

document.getElementById('searchInput').addEventListener('input', function() {
    if (this.value.trim() === '') {
        window.location.href = 'medicines.php';
    }
});
</script>

  <div class="cat-tabs">
    <button class="cat-tab <?= !$categoryId ? 'active' : '' ?>" data-cat="all">All</button>
    <?php foreach ($categories as $cat): ?>
    <button class="cat-tab <?= $categoryId === $cat['id'] ? 'active' : '' ?>" data-cat="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></button>
    <?php endforeach; ?>
  </div>

  <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Showing <?= count($medicines) ?> of <?= $total ?> medicines<?= $search ? ' for "' . htmlspecialchars($search) . '"' : '' ?></p>

  <?php if (empty($medicines)): ?>
  <div class="no-results card p-6">
    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M10.5 20H4a2 2 0 01-2-2V6a2 2 0 012-2h3.9a2 2 0 011.69.9l.81 1.2a2 2 0 001.67.9H20a2 2 0 012 2v2"/></svg>
    <h3 style="font-size:16px;margin:12px 0 6px">No medicines found</h3>
    <p style="color:var(--muted);font-size:13px">Try adjusting your search or category filter.</p>
  </div>
  <?php else: ?>
  <div class="medicines-grid">
    <?php foreach ($medicines as $med): ?>
    <?php
      $stockBadge = $med['stock_quantity'] <= 0 ? ['class'=>'stock-out','label'=>'Out of Stock']
        : ($med['stock_quantity'] <= $med['low_stock_threshold'] ? ['class'=>'stock-low','label'=>'Low ('.$med['stock_quantity'].')']
        : ['class'=>'stock-in','label'=>'In Stock ('.$med['stock_quantity'].')']);
      $canAdd = isLoggedIn() && !isAdmin() && $med['stock_quantity'] > 0;
    ?>
    <div class="card med-card">
      <div class="med-card-header">
        <div class="med-card-icon">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M10.5 20H4a2 2 0 01-2-2V6a2 2 0 012-2h3.9a2 2 0 011.69.9l.81 1.2a2 2 0 001.67.9H20a2 2 0 012 2v.5"/><circle cx="18" cy="18" r="3"/><path d="M18 15v3M18 21v-.5M16.5 18H15M21 18h-.5"/></svg>
        </div>
        <div class="med-badges">
          <span class="stock-badge <?= $stockBadge['class'] ?>"><?= $stockBadge['label'] ?></span>
          <?php if ($med['requires_prescription']): ?><span class="rx-badge">Rx Required</span><?php endif; ?>
        </div>
      </div>
      <h3><?= htmlspecialchars($med['name']) ?></h3>
      <p class="generic"><?= htmlspecialchars($med['generic_name'] ?? '') ?></p>
      <?php if ($med['category_name']): ?><span class="cat-tag"><?= htmlspecialchars($med['category_name']) ?></span><?php endif; ?>
      <p class="desc"><?= htmlspecialchars($med['description'] ?? '') ?></p>
      <div class="med-card-footer">
        <div>
          <div style="font-size:18px;font-weight:800;color:var(--primary)">৳ <?= number_format($med['price'], 2) ?></div>
          <div class="price-unit">per <?= htmlspecialchars($med['unit']) ?></div>
        </div>
        <div>
          <?php if ($canAdd): ?>
          <div class="qty-control" style="margin-bottom:6px">
            <button class="qty-btn" onclick="changeMedQty(<?= $med['id'] ?>, -1)">−</button>
            <span class="qty-val" id="med-qty-<?= $med['id'] ?>">1</span>
            <button class="qty-btn" onclick="changeMedQty(<?= $med['id'] ?>, 1)">+</button>
          </div>
          <button class="add-cart-btn" onclick="addToCart(<?= $med['id'] ?>, '<?= addslashes($med['name']) ?>')">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            Add to Cart
          </button>
          <?php elseif (!isLoggedIn()): ?>
          <a href="login.php" class="btn btn-outline-primary btn-sm">Login to Buy</a>
          <?php else: ?>
          <span style="font-size:12px;color:var(--muted)">Out of stock</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>