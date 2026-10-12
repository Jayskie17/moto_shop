<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'motorshop_db');
define('DB_PORT', 3307);

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        error_log("Database Connection Error: " . $conn->connect_error);
        die('Connection failed: ' . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
?>