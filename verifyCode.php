<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$db_server = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "detyrekursi";

$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = mysqli_real_escape_string($conn, trim($_POST['code']));
    $query = "SELECT * FROM email_verification WHERE code='$code' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) <= 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        echo json_encode(["success" => true, "message" => "Code verified successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid or expired code."]);
    }
}
?>
