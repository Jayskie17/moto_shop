<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

checkRole(['Admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token session.']);
        exit();
    }

    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name  = sanitizeInput($_POST['last_name'] ?? '');
    $username   = sanitizeInput($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role_id    = intval($_POST['role_id'] ?? 0);
    $status     = sanitizeInput($_POST['status'] ?? 'Active');

    if (empty($first_name) || empty($last_name) || empty($username) || empty($password) || $role_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit();
    }

    $conn = getDBConnection();

    // Check duplicate username
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username is already taken.']);
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (role_id, first_name, last_name, username, password, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $role_id, $first_name, $last_name, $username, $hashed_password, $status);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User created successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>