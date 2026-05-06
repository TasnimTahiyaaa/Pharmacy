<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
requireAdmin();
$currentUser = getCurrentUser();
$unreadNotifs = getUnreadNotifCount();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$navItems = [
  ['dashboard', 'Dashboard', 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z', 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z'],
  ['inventory', 'Inventory', '', ''],
  ['medicines', 'Medicines', '', ''],
  ['orders', 'Orders', '', ''],
  ['customers', 'Customers', '', ''],
  ['payments', 'Payments', '', ''],
  ['reports', 'Reports', '', ''],
  ['notifications', 'Notifications', '', ''],
];

$icons = [
  'dashboard'     => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
  'inventory'     => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
  'medicines'     => '<path d="M10.5 20H4a2 2 0 01-2-2V6a2 2 0 012-2h3.9a2 2 0 011.69.9l.81 1.2a2 2 0 001.67.9H20a2 2 0 012 2v2"/><circle cx="18" cy="18" r="3"/><path d="M18 15v3M18 21v-1.5M16.5 18H15M21 18h-1.5"/>',
  'orders'        => '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>',
  'customers'     => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/><circle cx="9" cy="7" r="4"/>',
  'payments'      => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
  'reports'       => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
  'notifications' => '<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Admin' : 'Admin — Noor Pharmacy' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php if (isset($_SESSION['flash'])): ?>
<div id="flash-data" data-msg="<?= htmlspecialchars($_SESSION['flash']['msg']) ?>" data-type="<?= htmlspecialchars($_SESSION['flash']['type']) ?>"></div>
<?php unset($_SESSION['flash']); endif; ?>
<div class="admin-wrap">
  <aside class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">NP</div>
      <span class="sidebar-title">Noor Pharmacy</span>
      <button class="sidebar-toggle" id="sidebarToggle" title="Collapse">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
      </button>
    </div>
    <nav class="sidebar-nav">
      <?php foreach (['dashboard'=>'Dashboard','inventory'=>'Inventory','medicines'=>'Medicines','orders'=>'Orders','customers'=>'Customers','payments'=>'Payments','reports'=>'Reports','notifications'=>'Notifications'] as $page => $label): ?>
      <a href="<?= $page ?>.php" class="sidebar-item <?= $currentPage === $page ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><?= $icons[$page] ?></svg>
        <span><?= $label ?></span>
        <?php if ($page === 'notifications' && $unreadNotifs > 0): ?>
        <span class="notif-badge"><?= $unreadNotifs ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></div>
        <div class="sidebar-user-info">
          <p><?= htmlspecialchars($currentUser['name']) ?></p>
          <small><?= htmlspecialchars($currentUser['email']) ?></small>
        </div>
      </div>
      <a href="../logout.php" class="btn btn-outline btn-sm w-100 mt-3" style="margin-top:12px">Logout</a>
    </div>
  </aside>
  <main class="admin-main" id="adminMain">
