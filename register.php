<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';

if (isLoggedIn()) { header('Location: medicines.php'); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    // ✅ UPDATED VALIDATION (added phone & address)
    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = hashPassword($password);

            $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, phone, address) VALUES (?, ?, ?, 'customer', ?, ?)");
            $stmt->bind_param('sssss', $name, $email, $hash, $phone, $address);

            if ($stmt->execute()) {
                $userId = $db->insert_id;
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = 'customer';
                $_SESSION['name'] = $name;
                $_SESSION['flash'] = ['msg' => 'Account created! Welcome, ' . $name . '!', 'type' => 'success'];
                header('Location: medicines.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
include 'includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box" style="max-width:480px">
    <div class="auth-logo">
      <div class="logo-icon" style="width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:18px;background:var(--primary);color:#fff;margin:0 auto 12px">Rx</div>
      <h1>Create Account</h1>
      <p>Join Noor Pharmacy for easy medicine ordering</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input type="text" name="name" class="form-control" placeholder="Your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password *</label>
        <div class="password-wrap">
          <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required style="padding-right:42px">
          <button type="button" id="togglePass" class="toggle-pass">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">Phone *</label>
          <input type="tel" name="phone" class="form-control" placeholder="+880 1700-000000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Address *</label>
          <input type="text" name="address" class="form-control" placeholder="Your address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg">Create Account</button>
    </form>

    <div class="divider"><span>or</span></div>
    <p style="text-align:center;font-size:14px;color:var(--muted)">Already have an account? <a href="login.php" style="color:var(--primary);font-weight:700">Sign in</a></p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>