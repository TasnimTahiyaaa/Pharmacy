<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
$cartCount = isLoggedIn() ? getCartCount() : 0;
$currentUser = isLoggedIn() ? getCurrentUser() : null;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Noor Pharmacy' : 'Noor Pharmacy' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= str_repeat('../', $depth ?? 0) ?>assets/css/style.css">
</head>
<body>

<?php if (isset($_SESSION['flash'])): ?>
<div id="flash-data" data-msg="<?= htmlspecialchars($_SESSION['flash']['msg']) ?>" data-type="<?= htmlspecialchars($_SESSION['flash']['type']) ?>"></div>
<?php unset($_SESSION['flash']); endif; ?>

<nav class="navbar">
  <div class="navbar-inner">
    <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php" class="navbar-logo">
      <div class="logo-icon">NP</div>
      <div class="logo-text">Noor <span>Pharmacy</span></div>
    </a>

    <div class="nav-links">
      <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">Home</a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>medicines.php" class="nav-link <?= $currentPage === 'medicines' ? 'active' : '' ?>">Medicines</a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php#about" class="nav-link">About</a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php#contact" class="nav-link">Contact</a>
    </div>

    <div class="nav-actions">
      <?php if (isLoggedIn() && !isAdmin()): ?>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>cart.php" class="btn btn-outline btn-icon cart-btn" title="Cart">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <?php if ($cartCount > 0): ?><span class="cart-badge" id="nav-cart-badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <?php endif; ?>

      <?php if (isLoggedIn() && isAdmin()): ?>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>admin/dashboard.php" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Admin Panel
      </a>
      <?php endif; ?>

      <?php if (isLoggedIn()): ?>
      <div class="user-menu">
        <button class="user-btn" id="userMenuBtn">
          <div class="user-avatar" style="<?= isAdmin() ? '' : 'background:var(--primary-light);color:var(--primary)' ?>"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></div>
          <span><?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?></span>
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="dropdown" id="userDropdown">
          <div class="dropdown-header">
            <?php if (isAdmin()): ?><p>Admin Account</p><?php endif; ?>
            <small><?= htmlspecialchars($currentUser['email']) ?></small>
          </div>
          <?php if (isAdmin()): ?>
          <a href="<?= str_repeat('../', $depth ?? 0) ?>admin/dashboard.php" class="dropdown-item">Dashboard</a>
          <a href="<?= str_repeat('../', $depth ?? 0) ?>admin/inventory.php" class="dropdown-item">Inventory</a>
          <a href="<?= str_repeat('../', $depth ?? 0) ?>admin/orders.php" class="dropdown-item">Orders</a>
          <a href="<?= str_repeat('../', $depth ?? 0) ?>admin/reports.php" class="dropdown-item">Reports</a>
          <a href="<?= str_repeat('../', $depth ?? 0) ?>admin/notifications.php" class="dropdown-item">Notifications</a>
          <?php else: ?>
          <a href="<?= str_repeat('../', $depth ?? 0) ?>profile.php" class="dropdown-item">My Profile</a>
          <a href="<?= str_repeat('../', $depth ?? 0) ?>orders.php" class="dropdown-item">My Orders</a>
          <?php endif; ?>
          <hr class="dropdown-divider">
          <a href="<?= str_repeat('../', $depth ?? 0) ?>logout.php" class="dropdown-item text-danger">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Logout
          </a>
        </div>
      </div>
      <?php else: ?>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>login.php" class="btn btn-outline btn-sm">Customer Login</a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>login.php?role=admin" class="btn btn-outline-primary btn-sm">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Admin
      </a>
      <a href="<?= str_repeat('../', $depth ?? 0) ?>register.php" class="btn btn-primary btn-sm">Register</a>
      <?php endif; ?>

      <button class="hamburger" id="hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
  <div class="mobile-nav" id="mobileNav">
    <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php">Home</a>
    <a href="<?= str_repeat('../', $depth ?? 0) ?>medicines.php">Medicines</a>
    <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php#about">About</a>
    <a href="<?= str_repeat('../', $depth ?? 0) ?>index.php#contact">Contact</a>
    <?php if (!isLoggedIn()): ?>
    <a href="<?= str_repeat('../', $depth ?? 0) ?>login.php?role=admin" style="color:var(--primary);font-weight:700">Admin Portal</a>
    <?php endif; ?>
  </div>
</nav>
