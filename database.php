<?php

$db_server = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "detyrekursi";
$db_port = "3306";

$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name, $db_port);

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}
return $conn;

