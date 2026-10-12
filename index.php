<?php
require_once __DIR__ . '/backend/includes/functions.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role_name'])) {
    if ($_SESSION['role_name'] === 'Admin') {
        redirect('/motor_shop/frontend/views/admin/dashboard/dashboard.php');
    } elseif ($_SESSION['role_name'] === 'Inventory Officer') {
        redirect('/motor_shop/frontend/views/inventory/dashboard/dashboard.php');
    } elseif ($_SESSION['role_name'] === 'Staff/Cashier') {
        redirect('/motor_shop/frontend/views/cashier/dashboard/dashboard.php');
    }
}

redirect('/motor_shop/frontend/login.php');
?>