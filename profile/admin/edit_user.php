<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../404.html");
    exit;
}

$conn = require "../../database.php";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $user_id = $_GET['id'];
    $sql = "
        SELECT
            u.user_id,
            u.email,
            u.role,
            p.first_name,
            p.last_name,
            p.phone,
            p.gender
        FROM personal_info p
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE p.user_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        echo json_encode(["success" => true, "data" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "User not found."]);
    }
    $stmt->close();
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $allowed_roles = ['USER', 'ADMIN'];
    if (!in_array($_POST['role'], $allowed_roles)) {
        echo json_encode(["success" => false, "message" => " Invalid."]);
        $conn->close();
        exit;
    }

    $target_dir = "../../images/profile/picture/";
    $web_root_dir = "images/profile/picture/";
    $default_photo = "images/profile/default.jpg";
    $photo_path = null;
    $error = null;
    $db_photo_path = null;

    $sql = "SELECT photo_path FROM personal_info WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_POST['user_id']);
    $stmt->execute();
    $stmt->bind_result($db_photo_path);
    $stmt->fetch();
    $stmt->close();

    if (isset($_FILES["profile-picture"]) && !empty($_FILES["profile-picture"]["tmp_name"])) {
        $imageFileType = strtolower(pathinfo($_FILES["profile-picture"]["name"], PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowedTypes)) {

            $target_file = $target_dir . basename($_FILES["profile-picture"]["name"]);
            $web_file_path = $web_root_dir . basename($_FILES["profile-picture"]["name"]);

            if (!move_uploaded_file($_FILES["profile-picture"]["tmp_name"], $target_file)) {
                $error = "Failed to upload file.";
            }
            else {
                $photo_path = $web_file_path;
            }
        }
        else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }
    else {
        $photo_path = ($db_photo_path && file_exists("../../".$db_photo_path)) ? $db_photo_path : $default_photo;
    }

    if ($error) {
        echo json_encode(["success" => false, "message" => $error]);
        $conn->close();
        exit;
    }


    $sql = "
            UPDATE users u 
            LEFT JOIN personal_info p ON p.user_id = u.user_id
            SET 
                u.email = ?,
                u.role = ?, 
                u.updated_at = NOW(), 
                p.first_name = ?, 
                p.last_name = ?, 
                p.phone = ?, 
                p.gender = ?, 
                p.photo_path = ?
            WHERE u.user_id = ?
            ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $_POST['email'], $_POST['role'], $_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['gender'], $photo_path, $_POST['user_id']);

    if ($stmt->execute()) {
        $location = null;

        if ($_SESSION['user_id'] == $_POST['user_id']) {
            $_SESSION['first_name'] = $_POST['first_name'];
            $_SESSION['last_name'] = $_POST['last_name'];

            if ($_SESSION['role'] !== $_POST['role']) {
                session_unset();
                session_destroy();
                $location = "signin.html";
            }
            else {
                $location = "admin.php";
            }
        }
        echo json_encode(["success" => true,
            "message" => "User updated successfully.",
            "user_id" => $_POST["user_id"],
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "email" => $_POST["email"],
            "role" => $_POST["role"],
            "location" => $location
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error updating user: " . $stmt->error]);
    }
    $stmt->close();
}
$conn->close();

