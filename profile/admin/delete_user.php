<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../404.html");
    exit;
}

$conn = require "../../database.php";

if ($_POST["action"] == "deleteUser") {

    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    $user_id = $_POST['user_id'];

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $location = null;
                if ($_SESSION['user_id'] == $user_id) {
                    session_unset();
                    session_destroy();
                    $location = "signin.html";
                }
                echo json_encode(["success" => true, "message" => "User deleted successfully.", "location" => $location]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to delete User."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
    }
    $conn->close();
    exit;
}