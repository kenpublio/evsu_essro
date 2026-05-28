<?php
$db_host = 'localhost';
$db_user = 'u656591888_eva';
$db_password = 'Kenrepollo_12';
$db_name = 'u656591888_eva_evsu_db';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function getDB() {
    global $conn;
    return $conn;
}

date_default_timezone_set('Asia/Manila');
?>