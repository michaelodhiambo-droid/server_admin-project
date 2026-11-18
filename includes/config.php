<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_mysql_password'); // change this
define('DB_NAME', 'leaveapp');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /index.php");
        exit();
    }
}

function requireManager() {
    requireLogin();
    if ($_SESSION['role'] !== 'manager') {
        header("Location: /employee/dashboard.php");
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function countLeaveDays($start, $end) {
    $s = new DateTime($start);
    $e = new DateTime($end);
    $diff = $s->diff($e);
    return $diff->days + 1;
}

function getLeaveUsed($conn, $user_id, $type) {
    $stmt = $conn->prepare("SELECT SUM(days) as total FROM leave_requests WHERE user_id=? AND leave_type=? AND status='approved'");
    $stmt->bind_param("is", $user_id, $type);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    return $r['total'] ?? 0;
}
