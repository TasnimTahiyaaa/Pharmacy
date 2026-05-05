<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_all_read'])) {
        $db->query("UPDATE notifications SET is_read=1");
        $_SESSION['flash'] = ['msg'=>'All notifications marked as read','type'=>'success'];
    } elseif (isset($_POST['mark_read'])) {
        $id = (int)$_POST['notif_id'];
        $db->query("UPDATE notifications SET is_read=1 WHERE id=$id");
    } elseif (isset($_POST['delete_notif'])) {
        $id = (int)$_POST['notif_id'];
        $db->query("DELETE FROM notifications WHERE id=$id");
        $_SESSION['flash'] = ['msg'=>'Notification deleted','type'=>'success'];
    }
    header('Location: notifications.php'); exit;
}

$notifications = $db->query("SELECT * FROM notifications ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unread = count(array_filter($notifications, fn($n) => !$n['is_read']));

$pageTitle = 'Notifications';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Notifications</h1>
    <p><?=$unread?> unread notification(s)</p>
  </div>
  <?php if($unread > 0): ?>
  <form method="POST">
    <button type="submit" name="mark_all_read" value="1" class="btn btn-outline btn-sm">Mark All Read</button>
  </form>
  <?php endif; ?>
</div>

<div class="card">
  <?php if(empty($notifications)): ?>
  <div style="text-align:center;padding:60px;color:var(--muted)">
    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:12px"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
    <h3>No notifications</h3>
  </div>
  <?php else: foreach($notifications as $n): ?>
  <div class="notif-item <?=$n['is_read']?'':'unread'?>">
    <div class="notif-dot <?=$n['is_read']?'read':''?>"></div>
    <div class="notif-icon">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <div style="flex:1;min-width:0">
      <div class="notif-title"><?=htmlspecialchars($n['title'])?></div>
      <div class="notif-msg"><?=htmlspecialchars($n['message'])?></div>
      <div class="notif-time"><?=date('M j, Y g:i A',strtotime($n['created_at']))?></div>
    </div>
    <div style="display:flex;gap:6px;flex-shrink:0">
      <?php if(!$n['is_read']): ?>
      <form method="POST">
        <input type="hidden" name="notif_id" value="<?=$n['id']?>">
        <button type="submit" name="mark_read" value="1" class="btn btn-outline btn-sm">Mark Read</button>
      </form>
      <?php endif; ?>
      <form method="POST">
        <input type="hidden" name="notif_id" value="<?=$n['id']?>">
        <button type="submit" name="delete_notif" value="1" class="btn btn-danger btn-sm" data-confirm="Delete this notification?">Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>
