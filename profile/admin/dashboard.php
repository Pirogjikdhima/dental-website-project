<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../404.html");
    exit;
}

$conn = require "../../database.php";

$sql = "
    SELECT
        u.user_id,
        u.email,
        u.role,
        p.first_name,
        p.last_name,
        p.phone,
        p.gender
    FROM users u 
    LEFT JOIN personal_info p ON p.user_id = u.user_id
";
$user_id = $email = $role = $first_name = $last_name = $phone = $gender = "";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $stmt->bind_result($user_id, $email, $role, $first_name, $last_name, $phone, $gender);

    $users = [];
    while ($stmt->fetch()) {
        $users[] = [
            "user_id" => $user_id,
            "email" => $email,
            "role" => $role,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "phone" => $phone,
            "gender" => $gender,
        ];
    }

    if (!empty($users)) {
        echo json_encode(["success" => true, "data" => $users]);
    } else {
        echo json_encode(["success" => false, "message" => "No user information found."]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
}
$conn->close();

