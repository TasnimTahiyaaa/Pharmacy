<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'noor_pharmacy');
define('SALT', 'noor_pharmacy_salt_2024');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function hashPassword($password) {
    return hash('sha256', $password . SALT);
}

function sanitize($value) {
    return htmlspecialchars(strip_tags(trim($value)));
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, role, phone, address, created_at FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCartCount() {
    if (!isLoggedIn()) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['total'] ?? 0);
}

function getUnreadNotifCount() {
    $db = getDB();
    $result = $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 0");
    $row = $result->fetch_assoc();
    return (int)$row['cnt'];
}

function checkLowStock($medicineId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, stock_quantity, low_stock_threshold FROM medicines WHERE id = ?");
    $stmt->bind_param('i', $medicineId);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();
    if ($med && $med['stock_quantity'] <= $med['low_stock_threshold']) {
        $check = $db->prepare("SELECT id FROM notifications WHERE medicine_id = ? AND is_read = 0 AND type = 'low_stock'");
        $check->bind_param('i', $medicineId);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $msg = $med['name'] . ' has only ' . $med['stock_quantity'] . ' units left (threshold: ' . $med['low_stock_threshold'] . ')';
            $ins = $db->prepare("INSERT INTO notifications (type, title, message, medicine_id, medicine_name) VALUES ('low_stock', 'Low Stock Alert', ?, ?, ?)");
            $ins->bind_param('sis', $msg, $medicineId, $med['name']);
            $ins->execute();
        }
    }
}
