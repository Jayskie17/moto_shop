<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

checkRole(['Admin']);

$conn = getDBConnection();
$sql = "SELECT u.user_id, u.first_name, u.last_name, u.username, u.status, u.created_at, r.role_id, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.role_id 
        ORDER BY u.user_id DESC";

$result = $conn->query($sql);
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'data' => $users]);
?>