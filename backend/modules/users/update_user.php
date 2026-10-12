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

    $user_id    = intval($_POST['user_id'] ?? 0);
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name  = sanitizeInput($_POST['last_name'] ?? '');
    $username   = sanitizeInput($_POST['username'] ?? '');
    $role_id    = intval($_POST['role_id'] ?? 0);
    $status     = sanitizeInput($_POST['status'] ?? 'Active');
    $password   = $_POST['password'] ?? '';

    if ($user_id <= 0 || empty($first_name) || empty($last_name) || empty($username) || $role_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit();
    }

    $conn = getDBConnection();

    // Check duplicate username for other users
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $check_stmt->bind_param("si", $username, $user_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username is already in use by another account.']);
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();

    // Update with or without password reset
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET role_id = ?, first_name = ?, last_name = ?, username = ?, password = ?, status = ? WHERE user_id = ?");
        $stmt->bind_param("isssssi", $role_id, $first_name, $last_name, $username, $hashed_password, $status, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET role_id = ?, first_name = ?, last_name = ?, username = ?, status = ? WHERE user_id = ?");
        $stmt->bind_param("issssi", $role_id, $first_name, $last_name, $username, $status, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>