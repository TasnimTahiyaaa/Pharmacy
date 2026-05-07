<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'medicines.php'));
    exit;
}

$error = '';
$isAdminMode = isset($_GET['role']) && $_GET['role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $db = getDB();
        $hash = hashPassword($password);
        $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = ? AND password_hash = ?");
        $stmt->bind_param('ss', $email, $hash);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['flash'] = ['msg' => 'Welcome back, ' . $user['name'] . '!', 'type' => 'success'];
            header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'medicines.php'));
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <div class="logo-icon" style="width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:18px;background:var(--primary);color:#fff;margin:0 auto 12px">NP</div>
      <h1>Welcome back</h1>
      <p>Sign in to your Noor Pharmacy account</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label" for="email">Email address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="password-wrap">
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password" style="padding-right:42px">
          <button type="button" id="togglePass" class="toggle-pass">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 btn-lg" style="margin-top:4px">Sign In</button>
    </form>

    <div class="divider"><span>or</span></div>
    <p style="text-align:center;font-size:14px;color:var(--muted)">Don't have an account? <a href="register.php" style="color:var(--primary);font-weight:700">Create one</a></p>

  </div>
</div>

<?php include 'includes/footer.php'; ?>