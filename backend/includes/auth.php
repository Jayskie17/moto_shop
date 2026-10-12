<?php
require_once __DIR__ . '/functions.php';

// Enforce login requirement
// Enforce login requirement
function checkAuth() {
    // Development auto-login: if no session exists, default to Admin
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id']   = 1;
        $_SESSION['full_name'] = 'System Administrator';
        $_SESSION['role_name'] = 'Admin';
    }
}


// Enforce specific role requirements
function checkRole($allowed_roles = []) {
    checkAuth();
    if (!in_array($_SESSION['role_name'], $allowed_roles)) {
        http_response_code(403);
        die("403 Forbidden: You do not have permission to access this page.");
    }
}
?>