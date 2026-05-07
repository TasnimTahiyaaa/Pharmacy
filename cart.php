<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$user = getCurrentUser();

$deliveryCharge = 100;

// AJAX handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'add') {
        $medId = (int)$_POST['medicine_id'];
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        // Check stock
        $stmt = $db->prepare("SELECT stock_quantity, is_active FROM medicines WHERE id = ?");
        $stmt->bind_param('i', $medId);
        $stmt->execute();
        $med = $stmt->get_result()->fetch_assoc();

        if (!$med || !$med['is_active'] || $med['stock_quantity'] < $qty) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO cart_items (user_id, medicine_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->bind_param('iiii', $userId, $medId, $qty, $qty);
        $stmt->execute();

        $count = getCartCount();

        echo json_encode(['success' => true, 'cart_count' => $count]);
        exit;
    }

    if ($action === 'update') {
        $itemId = (int)$_POST['item_id'];
        $qty = max(1, (int)$_POST['quantity']);

        $stmt = $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param('iii', $qty, $itemId, $userId);
        $stmt->execute();

        echo json_encode(['success' => true, 'cart' => getCartSummary($db, $userId)]);
        exit;
    }

    if ($action === 'remove') {
        $itemId = (int)$_POST['item_id'];

        $stmt = $db->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $itemId, $userId);
        $stmt->execute();

        echo json_encode(['success' => true, 'cart' => getCartSummary($db, $userId)]);
        exit;
    }

    if ($action === 'clear') {
        $stmt = $db->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}

// POST checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {

    $paymentMethod = in_array($_POST['payment_method'], ['bkash', 'cash']) ? $_POST['payment_method'] : 'cash';
    $txId = sanitize($_POST['transaction_id'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');

    // Get cart items
    $stmt = $db->prepare("SELECT ci.*, m.name as medicine_name, m.price, m.stock_quantity 
                          FROM cart_items ci 
                          JOIN medicines m ON m.id = ci.medicine_id 
                          WHERE ci.user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($items)) {
        $_SESSION['flash'] = ['msg' => 'Your cart is empty', 'type' => 'error'];
        header('Location: cart.php');
        exit;
    }

    $total = 0;

    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Add delivery charge
    $total += $deliveryCharge;

    $db->begin_transaction();

    try {

        $stmt = $db->prepare("INSERT INTO orders 
            (user_id, customer_name, customer_phone, type, status, payment_method, payment_status, total_amount, notes) 
            VALUES (?, ?, ?, 'online', 'pending', ?, 'pending', ?, ?)");

        $stmt->bind_param(
            'isssds',
            $userId,
            $user['name'],
            $user['phone'],
            $paymentMethod,
            $total,
            $notes
        );

        $stmt->execute();

        $orderId = $db->insert_id;

        foreach ($items as $item) {

            $subtotal = $item['price'] * $item['quantity'];

            $stmt2 = $db->prepare("INSERT INTO order_items 
                (order_id, medicine_id, medicine_name, quantity, unit_price, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)");

            $stmt2->bind_param(
                'iisidd',
                $orderId,
                $item['medicine_id'],
                $item['medicine_name'],
                $item['quantity'],
                $item['price'],
                $subtotal
            );

            $stmt2->execute();

            // Deduct stock
            $db->query("UPDATE medicines 
                        SET stock_quantity = stock_quantity - {$item['quantity']} 
                        WHERE id = {$item['medicine_id']}");

            checkLowStock($item['medicine_id']);
        }

        // Payment record
        $stmt3 = $db->prepare("INSERT INTO payments 
            (order_id, amount, method, status, transaction_id) 
            VALUES (?, ?, ?, 'pending', ?)");

        $stmt3->bind_param('idss', $orderId, $total, $paymentMethod, $txId);
        $stmt3->execute();

        // Clear cart
        $db->query("DELETE FROM cart_items WHERE user_id = $userId");

        $db->commit();

        $_SESSION['flash'] = [
            'msg' => 'Order placed successfully! Order #' . $orderId,
            'type' => 'success'
        ];

        header('Location: orders.php');
        exit;

    } catch (Exception $e) {

        $db->rollback();

        $_SESSION['flash'] = [
            'msg' => 'Order failed. Please try again.',
            'type' => 'error'
        ];

        header('Location: cart.php');
        exit;
    }
}

function getCartSummary($db, $userId) {

    global $deliveryCharge;

    $stmt = $db->prepare("SELECT ci.id, ci.quantity, m.price 
                          FROM cart_items ci 
                          JOIN medicines m ON m.id = ci.medicine_id 
                          WHERE ci.user_id = ?");

    $stmt->bind_param('i', $userId);
    $stmt->execute();

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total = 0;
    $count = 0;

    foreach ($rows as $r) {
        $total += $r['price'] * $r['quantity'];
        $count += $r['quantity'];
    }

    $total += $deliveryCharge;

    return [
        'total' => $total,
        'item_count' => $count
    ];
}

// Load cart
$stmt = $db->prepare("SELECT ci.id, ci.medicine_id, ci.quantity, 
                             m.name as medicine_name, 
                             m.price, 
                             m.unit, 
                             (m.price * ci.quantity) as subtotal 
                      FROM cart_items ci 
                      JOIN medicines m ON m.id = ci.medicine_id 
                      WHERE ci.user_id = ? 
                      ORDER BY ci.created_at");

$stmt->bind_param('i', $userId);
$stmt->execute();

$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$cartTotal = array_sum(array_column($cartItems, 'subtotal'));
$cartCount = array_sum(array_column($cartItems, 'quantity'));

$grandTotal = $cartTotal + $deliveryCharge;

$pageTitle = 'Your Cart';
include 'includes/header.php';
?>

<div class="container" style="padding-top:32px;padding-bottom:0">
  
  <div class="page-header">
    <h1>
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--primary);vertical-align:middle;margin-right:8px">
        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 01-8 0"/>
      </svg>
      Your Cart
    </h1>
  </div>

  <?php if (empty($cartItems)): ?>

  <div class="card" style="text-align:center;padding:60px 20px">
    
    <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--muted);margin-bottom:16px">
      <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
      <path d="M16 10a4 4 0 01-8 0"/>
    </svg>

    <h3 style="font-size:18px;margin-bottom:8px">Your cart is empty</h3>

    <p style="color:var(--muted);margin-bottom:20px">
      Browse medicines and add items to get started.
    </p>

    <a href="medicines.php" class="btn btn-primary">Browse Medicines</a>

  </div>

  <?php else: ?>

  <div class="cart-layout">

    <div>

      <?php foreach ($cartItems as $item): ?>

      <div class="cart-item" id="cart-row-<?= $item['id'] ?>">

        <div class="cart-item-icon">Rx</div>

        <div class="cart-item-info">
          <h3><?= htmlspecialchars($item['medicine_name']) ?></h3>
          <p>৳ <?= number_format($item['price'], 2) ?> per <?= htmlspecialchars($item['unit']) ?></p>
        </div>

        <div class="cart-qty">
          <button onclick="changeQty(<?= $item['id'] ?>, -1)">−</button>
          <span id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
          <button onclick="changeQty(<?= $item['id'] ?>, 1)">+</button>
        </div>

        <div class="cart-subtotal">
          ৳ <?= number_format($item['subtotal'], 2) ?>
        </div>

        <button class="cart-remove" onclick="removeCartItem(<?= $item['id'] ?>)" title="Remove">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2"/>
          </svg>
        </button>

      </div>

      <?php endforeach; ?>

    </div>

    <div class="cart-summary">

      <div class="card p-6">

        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px">
          Order Summary
        </h3>

        <div class="summary-row">
          <span id="cart-count"><?= $cartCount ?> item(s)</span>
          <span id="cart-total">৳ <?= number_format($cartTotal, 2) ?></span>
        </div>

        <div class="summary-row">
          <span>Delivery</span>
          <span style="font-weight:600">৳ 100.00</span>
        </div>

        <div class="summary-total">
          <span>Total</span>
          <span>৳ <?= number_format($grandTotal, 2) ?></span>
        </div>

        <button class="btn btn-primary w-100 mt-4" data-modal="checkout-modal" style="margin-top:16px">
          Checkout →
        </button>

        <form method="POST" style="margin-top:8px">
          <input type="hidden" name="action" value="clear">

          <button type="submit"
                  class="btn btn-outline w-100 btn-sm"
                  onclick="return confirm('Clear entire cart?')"
                  formaction="cart.php">

            Clear Cart

          </button>
        </form>

      </div>

    </div>

  </div>

  <!-- Checkout Modal -->
  <div class="modal-overlay" id="checkout-modal">

    <div class="modal">

      <div class="modal-header">
        <h2>Complete Your Order</h2>
        <button class="modal-close">✕</button>
      </div>

      <form method="POST">

        <input type="hidden" name="checkout" value="1">

        <div class="form-group">
          <label class="form-label">Payment Method</label>

          <select name="payment_method" id="payment_method" class="form-control form-select">
            <option value="bkash">bKash (Online)</option>
            <option value="cash">Cash on Delivery</option>
          </select>
        </div>

        <div class="form-group" id="bkash_field">
          <label class="form-label">bKash Transaction ID</label>

          <input type="text"
                 name="transaction_id"
                 class="form-control"
                 placeholder="e.g. 8N7TXY2ABC">
        </div>

        <div class="form-group">
          <label class="form-label">Notes (optional)</label>

          <input type="text"
                 name="notes"
                 class="form-control"
                 placeholder="Any special instructions...">
        </div>

        <div style="background:var(--primary-light);
                    border-radius:10px;
                    padding:12px 16px;
                    display:flex;
                    justify-content:space-between;
                    font-weight:700;
                    margin-bottom:16px">

          <span>Total Amount</span>

          <span style="color:var(--primary)">
            ৳ <?= number_format($grandTotal, 2) ?>
          </span>

        </div>

        <button type="submit" class="btn btn-primary w-100">
          Place Order
        </button>

      </form>

    </div>

  </div>

  <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>