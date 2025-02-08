<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

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
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        echo json_encode(["success" => false, "message" => "Invalid email format!"], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $emailCheckQuery = "SELECT user_id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $emailCheckQuery);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo json_encode(["success" => false, "message" => "Email is already registered."], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => true, "message" => "Email is available."], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
