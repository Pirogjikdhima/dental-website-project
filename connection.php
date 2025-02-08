<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';
$conn = require_once "database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: 404.php");
}

if (isset($_POST["action"])) {

    $nameRegex = "/^[a-zA-Z\s]+$/";
    $emailRegex = "/^[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    $phoneRegex = "/^\+?[0-9]{1,14}$/";
    $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*\.])[A-Za-z\d!@#$%^&*\.]{6,}$/";

    if ($_POST["action"] == "register") {
        $required_fields = ['name', 'lastName', 'email', 'phone', 'gender', 'password', 'confirmPassword'];
        $errors = [];

        if (isset($_POST['name']) && !empty(trim($_POST['name'])) && !preg_match($nameRegex, $_POST['name'])) {
            $errors['name'] = "Name should only contain letters and spaces.";
        }
        if (isset($_POST['lastName']) && !empty(trim($_POST['lastName'])) && !preg_match($nameRegex, $_POST['lastName'])) {
            $errors['lastName'] = "Last name should only contain letters and spaces.";
        }
        if (isset($_POST['email']) && !empty(trim($_POST['email'])) && !preg_match($emailRegex, $_POST['email'])) {
            $errors['email'] = "Invalid email address.";
        }
        if (isset($_POST['phone']) && !empty(trim($_POST['phone'])) && !preg_match($phoneRegex, $_POST['phone'])) {
            $errors['phone'] = "Phone number is invalid.";
        }
        if (isset($_POST['password']) && !empty(trim($_POST['password'])) && !preg_match($passwordRegex, $_POST['password'])) {
            $errors['password'] = "Password must be at least 6 characters long, include uppercase, lowercase, a number, and a special character.";
        }
        if (isset($_POST['confirmPassword']) && $_POST['confirmPassword'] !== $_POST['password']) {
            $errors['confirmPassword'] = "Passwords do not match.";
        }
        $email = trim($_POST['email']);
        $emailCheckQuery = "SELECT user_id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $emailCheckQuery);
        if (mysqli_num_rows($result) > 0) {
            $errors['email'] = "Email is already registered.";
        }
        if (!empty($errors)) {
            echo json_encode(["success" => false, "message" => $errors]);
            exit;
        }
        $newsletterConsent = isset($_POST['newsletterConsent']) ? 1 : 0;

        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $lastName = mysqli_real_escape_string($conn, trim($_POST['lastName']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
        $gender = mysqli_real_escape_string($conn, trim($_POST['gender']));
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertUserQuery = "INSERT INTO users (email, password, role, created_at, newsletterConsent) 
                        VALUES ('$email', '$hashedPassword', 'USER', NOW(), '$newsletterConsent')";

        if (!mysqli_query($conn, $insertUserQuery)) {
            echo json_encode(["success" => false, "message" => "Failed to create user: " . mysqli_error($conn)]);
            exit;
        }
        $userId = mysqli_insert_id($conn);

        $insertPersonalQuery = "INSERT INTO personal_info (user_id, first_name, last_name, phone, gender) VALUES ('$userId', '$name', '$lastName', '$phone', '$gender')";
        if (!mysqli_query($conn, $insertPersonalQuery)) {
            mysqli_query($conn, "DELETE FROM users WHERE user_id = '$userId'");
            echo json_encode(["success" => false, "message" => "Failed to save personal information: " . mysqli_error($conn)]);
            exit;
        }
        echo json_encode(["success" => true, "message" => "Registration successful!", "location" => "signin.html"]);
    }
    else if ($_POST["action"] == "check_remember_token") {
        session_start();
        $rememberToken = $_POST["rememberToken"];

        if (isset($rememberToken)) {
            $rememberToken = mysqli_real_escape_string($conn, $rememberToken);
            $query = "SELECT u.user_id, u.role , p.first_name, p.last_name FROM users u INNER JOIN personal_info p on u.user_id = p.user_id WHERE remember_token = '$rememberToken'";
            $result = mysqli_query($conn, $query);

            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["role"] = $user["role"];
                    $_SESSION["first_name"] = $user["first_name"];
                    $_SESSION["last_name"] = $user["last_name"];

                    switch ($user["role"]) {
                        case "USER":
                            $redirectUrl = "http://localhost/DetyreKursi/user.php";
                            break;
                        case "ADMIN":
                            $redirectUrl = "http://localhost/DetyreKursi/admin.php";
                            break;
                        default:
                            $redirectUrl = "signin.html";
                            break;
                    }

                    echo json_encode(["success" => true, "location" => $redirectUrl]);
                    exit;
                } else {
                    echo json_encode(["success" => false, "message" => "Invalid remember token."]);
                    exit;
                }
            } else {
                echo json_encode(["success" => false, "message" => "Error executing query."]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "message" => "No remember token provided."]);
            exit;
        }
    }
    else if ($_POST["action"] == "login") {
        $emailOrPhone = mysqli_real_escape_string($conn, trim($_POST['emailOrPhone']));
        $password = trim($_POST['password']);
        $rememberMe = isset($_POST['rememberMe']) && $_POST['rememberMe'] === 'true';

        if (!preg_match($emailRegex, $emailOrPhone) && !preg_match($phoneRegex, $emailOrPhone)) {
            echo json_encode([
                "success" => false,
                "message" => [
                    "emailOrPhone" => "Invalid email or phone number format."
                ]
            ]);
            exit;
        }
        if (!preg_match($passwordRegex, $password)) {
            echo json_encode([
                "success" => false,
                "message" => [
                    "password" => "Password must be at least 6 characters long, contain uppercase and lowercase letters, a number, and a special character."
                ]
            ]);
            exit;
        }
        if (empty($emailOrPhone) || empty($password)) {
            echo json_encode([
                "success" => false,
                "message" => [
                    "emailOrPhone" => "Both email/phone and password are required."
                ]
            ]);
            exit;
        }
        $query = "SELECT u.user_id, u.password, u.role, p.first_name, p.last_name, u.email, p.phone
              FROM users u
              LEFT JOIN personal_info p ON u.user_id = p.user_id
              WHERE u.email = '$emailOrPhone' OR p.phone = '$emailOrPhone'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 0) {
            $identifier = $emailOrPhone;

            $checkQuery = "SELECT * FROM login_attempts WHERE identifier = '$identifier'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($checkResult) > 0) {
                $updateQuery = "UPDATE login_attempts 
                            SET failed_attempts = failed_attempts + 1, 
                                last_failed_attempt = NOW(),
                                blocked_until = IF(failed_attempts >= 6, NOW() + INTERVAL 30 MINUTE, NULL)
                            WHERE identifier = '$identifier'";
                mysqli_query($conn, $updateQuery);
            } else {
                $insertQuery = "INSERT INTO login_attempts (identifier, failed_attempts, last_failed_attempt, blocked_until)
                            VALUES ('$identifier', 1, NOW(), NULL)";
                mysqli_query($conn, $insertQuery);
            }
            echo json_encode([
                "success" => false,
                "message" => [
                    "emailOrPhone" => "Invalid email/phone."
                ]
            ]);
            exit;
        }

        $user = mysqli_fetch_assoc($result);
        $email = $user['email'];
        $phone = $user['phone'];
        $identifiers = [$email];

        if ($phone !== null) {
            $identifiers[] = $phone;
        }

        $placeholders = "'" . implode("','", $identifiers) . "'";
        $attemptQuery = "SELECT SUM(failed_attempts) AS total_failed_attempts 
                     FROM login_attempts 
                     WHERE identifier IN ($placeholders)";
        $attemptResult = mysqli_query($conn, $attemptQuery);
        $attemptData = mysqli_fetch_assoc($attemptResult);

        if ($attemptData && $attemptData['total_failed_attempts'] >= 7) {
            $checkBlockedQuery = "SELECT MAX(blocked_until) AS blocked_until
                              FROM login_attempts
                              WHERE identifier IN ($placeholders)";
            $blockedResult = mysqli_query($conn, $checkBlockedQuery);
            $blockedData = mysqli_fetch_assoc($blockedResult);

            if ($blockedData && strtotime($blockedData['blocked_until']) > time()) {
                $timeRemaining = strtotime($blockedData['blocked_until']) - time();
                $minutesRemaining = ceil($timeRemaining / 60);
                echo json_encode([
                    "success" => false,
                    "message" => [
                        "accountLocked" => "Your account is locked. Try again after " . $minutesRemaining . " minutes."
                    ]
                ]);
                exit;
            }
        }
        if (!password_verify($password, $user['password'])) {
            $identifier = $emailOrPhone;
            $checkQuery = "SELECT * FROM login_attempts WHERE identifier = '$identifier'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($checkResult) > 0) {
                $updateQuery = "UPDATE login_attempts 
                            SET failed_attempts = failed_attempts + 1, 
                                last_failed_attempt = NOW(),
                                blocked_until = IF(failed_attempts >= 6, NOW() + INTERVAL 30 MINUTE, NULL)
                            WHERE identifier = '$identifier'";
                mysqli_query($conn, $updateQuery);
            } else {
                $insertQuery = "INSERT INTO login_attempts (identifier, failed_attempts, last_failed_attempt, blocked_until)
                            VALUES ('$identifier', 1, NOW(), NULL)";
                mysqli_query($conn, $insertQuery);
            }
            echo json_encode([
                "success" => false,
                "message" => [
                    "password" => "Invalid password."
                ]
            ]);
            exit;
        }
        foreach ($identifiers as $identifier) {
            $resetQuery = "UPDATE login_attempts SET failed_attempts = 0, last_failed_attempt = NULL, blocked_until = NULL
                   WHERE identifier = '$identifier'";
            mysqli_query($conn, $resetQuery);
        }

        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        setcookie("logged_out", "true", time() - (86400 * 30), "/");


        if ($rememberMe) {
            $rememberToken = bin2hex(random_bytes(64));
            setcookie("remember_token", $rememberToken, time() + (86400 * 30), "/");

            $updateQuery = "UPDATE users SET remember_token = '$rememberToken' WHERE user_id = '{$user['user_id']}'";
            if (!mysqli_query($conn, $updateQuery)) {
                echo json_encode(["success" => false, "message" => "Failed to update remember token."]);
                exit;
            }
        } else {
            setcookie("remember_token", "", time() - 3600, "/");
            $clearTokenQuery = "UPDATE users SET remember_token = NULL WHERE user_id = '{$user['user_id']}'";
            mysqli_query($conn, $clearTokenQuery);
        }

        $redirectUrl = "";
        if ($user['role'] == 'USER') {
            $redirectUrl = "http://localhost/DetyreKursi/user.php";
        } elseif ($user['role'] == 'ADMIN') {
            $redirectUrl = "http://localhost/DetyreKursi/admin.php";
        }

        echo json_encode([
            "success" => true,
            "message" => "Login successful!",
            "user_id" => $user['user_id'],
            "role" => $user['role'],
            "location" => $redirectUrl
        ]);
    }
    else if ($_POST["action"] == "resetPassword") {
        $password = mysqli_real_escape_string($conn, trim($_POST['password']));
        $confirmPassword = mysqli_real_escape_string($conn, trim($_POST['confirmPassword']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));

        if (!preg_match($passwordRegex, $password)) {
            echo json_encode(['success' => false, 'message' => 'Password must contain at least 6 characters, including one uppercase letter, one lowercase letter, one number, and one special character.']);
            exit;
        }
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = '$hashedPassword' WHERE email = '$email'";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Password reset successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again later.']);
        }
    }
    else if ($_POST["action"] == "logout") {
        session_start();
        session_unset();
        session_destroy();

        setcookie("remember_token", "", time() - 3600, "/DetyreKursi/");
        setcookie("remember_token", "", time() - 3600, "/");
        setcookie("logged_out", "true", time() + (86400 * 30), "/");

        echo json_encode([
            "success" => true,
            "message" => "You have successfully logged out.",
            "location" => "home.php"
        ]);
        exit;
    }
    else if ($_POST["action"] == "newsletter") {
        $emailQuery = "
            SELECT u.email, p.first_name, p.last_name
            FROM users u
            JOIN personal_info p ON u.user_id = p.user_id
            WHERE u.newsletterConsent = 1
         ";

        $result = mysqli_query($conn, $emailQuery);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo json_encode(["success" => false, "message" => "No users with newsletter consent."]);
            exit;
        }

        $emails = [];
        $userDetails = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $emails[] = $row['email'];
            $userDetails[] = ['first_name' => $row['first_name'], 'last_name' => $row['last_name'], 'email' => $row['email']];
        }

        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : null;
        $body = isset($_POST['body']) ? trim($_POST['body']) : null;

        if (empty($subject) || empty($body)) {
            echo json_encode(["success" => false, "message" => "Subject and body cannot be empty."]);
            exit;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ddetkursi@gmail.com';
            $mail->Password = 'nhfw ewab xzts amcf';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('ddetkursi@gmail.com', 'Crystal Dental');

            foreach ($userDetails as $user) {
                $mail->clearAllRecipients();
                $mail->addBCC($user['email']);

                $personalizedBody = "<p style='font-size: 1.3rem; color: #4a3b7c; line-height: 1.6; margin-bottom: 20px;'>Hello <strong>" . htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']) . "</strong>,</p>" .
                    "<p style='font-size: 1.2rem; color: #4a3b7c; line-height: 1.6; margin-bottom: 20px;'>We are excited to share the latest news with you:</p>" .
                    "<div style='font-size: 1.1rem; color: #6e58a8; line-height: 1.6; background-color: #e3daf5; padding: 20px; border-radius: 10px; text-align: left;'>" .
                    nl2br(htmlspecialchars($body)) .
                    "</div>";

                $header = '<div style="background-color: #4a3b7c; color: white; padding: 30px; text-align: center; font-family: Arial, sans-serif; border-radius: 10px;">
                    <h2 style="margin: 0; font-size: 2rem;">Crystal Dental Newsletter</h2>
                    <p style="margin: 0; font-size: 1.2rem; font-weight: bold;">Smile bright, live right!</p>
                </div>';

                $footer = '<div style="background-color: #e3daf5; color: #4a3b7c; padding: 20px; text-align: center; font-family: Arial, sans-serif; border-top: 2px solid #4a3b7c;">
                    <p style="margin: 0; font-size: 1rem;">&copy; ' . date('Y') . ' Crystal Dental. All rights reserved.</p>
                    <p style="margin: 0; font-size: 0.9rem;">If you wish to unsubscribe, please contact us.</p>
                </div>';

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = '<html><body style="font-family: Arial, sans-serif; color: #4a3b7c; padding: 20px; background-color: #f6f0fc;">' .
                    $header .
                    $personalizedBody .
                    '<div style="text-align: center; margin: 20px 0;">
                    <img src="cid:logo" alt="Company Logo" style="width: 225px; height: 175px; margin-bottom: 20px; border-radius: 10px;">
                </div>' .
                    $footer .
                    '</body></html>';

                $mail->AddEmbeddedImage('C:\xampp\htdocs\DetyreKursi\images\crystal-dental-logo.png', 'logo');
                $mail->send();
            }

            echo json_encode(["success" => true, "message" => "Newsletter sent successfully."]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Failed to send emails: " . $mail->ErrorInfo]);
        }
    }
    else if ($_POST["action"] == "submit_review") {
        session_start();

        if (empty($_POST['rating']) || empty($_POST['comment'])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required!']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $rating = (int)$_POST['rating'];
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);

        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            exit;
        }
        $today = date('Y-m-d');
        $sql_check_reviews = "SELECT COUNT(*) FROM reviews WHERE user_id = '$user_id' AND DATE(created_at) = '$today'";
        $result = mysqli_query($conn, $sql_check_reviews);
        $row = mysqli_fetch_row($result);

        if ($row[0] >= 2) {
            echo json_encode(['success' => false, 'message' => 'You can only submit 2 reviews per day.']);
            exit;
        }

        $sql = "INSERT INTO reviews (user_id, rating, comment, created_at) VALUES ('$user_id', $rating, '$comment', NOW())";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting review. Please try again later.']);
        }
    }
    else if ($_POST["action"] == "updateProfile") {
        session_start();

        function handleFileUpload($file, $target_dir, &$error): bool|string
        {
            $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . basename($file["name"]);

            if (!move_uploaded_file($file["tmp_name"], $target_file)) {
                $error = "Failed to upload file.";
                return false;
            }
            return $target_file;
        }

        function getCurrentPhotoPath($conn, $user_id)
        {
            $photo_path = null;
            $sql = "SELECT photo_path FROM personal_info WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($photo_path);
            $stmt->fetch();
            $stmt->close();

            return $photo_path;
        }

        function updateUserDetails($conn, $data, $photo_path, &$error): bool
        {
            $sql = "
                UPDATE users u 
                LEFT JOIN personal_info p ON p.user_id = u.user_id
                SET 
                    u.email = ?, 
                    u.updated_at = ?, 
                    p.first_name = ?, 
                    p.last_name = ?, 
                    p.phone = ?, 
                    p.gender = ?, 
                    p.photo_path = ?
                WHERE u.user_id = ?
                ";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $error = "Error preparing statement.";
                return false;
            }
            $stmt->bind_param("sssssssi", $data['email'], $data['updated_at'], $data['name'], $data['surname'], $data['phone'], $data['gender'], $photo_path, $data['user_id']);
            if (!$stmt->execute()) {
                $error = "Failed to update user information.";
                return false;
            }
            $stmt->close();
            return true;
        }

        $target_dir = "images/profile/picture/";
        $default_photo = "images/profile/default.jpg";
        $error = null;

        $current_photo = getCurrentPhotoPath($conn, $_SESSION['user_id']);

        if (isset($_FILES["profile-picture"]) && !empty($_FILES["profile-picture"]["tmp_name"])) {
            $photo_path = handleFileUpload($_FILES["profile-picture"], $target_dir, $error);
        } else {
            $photo_path = ($current_photo && file_exists($current_photo)) ? $current_photo : $default_photo;
        }

        if (!$photo_path) {
            echo json_encode(["success" => false, "message" => $error]);
            exit;
        }

        $data = [
            'name' => trim($_POST['name']),
            'surname' => trim($_POST['surname']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'gender' => trim($_POST['gender']),
            'updated_at' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id']
        ];

        if (!preg_match($nameRegex,$data['name']) || !preg_match($nameRegex,$data['surname']) || !preg_match($emailRegex, $data['email']) || !preg_match($phoneRegex, $data['phone'])) {
            echo json_encode(["success" => false, "message" => "Invalid input data."]);
            exit;
        }

        if (updateUserDetails($conn, $data, $photo_path, $error)) {
            $_SESSION['first_name'] = $data['name'];
            $_SESSION['last_name'] = $data['surname'];

            echo json_encode([
                "success" => true,
                "message" => "User information updated successfully.",
                "location" => strtolower("{$_SESSION['role']}.php")]);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => $error]);
        }
    }
    else if ($_POST["action"] == "updatePassword") {
        session_start();

        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Password updated successfully.",
                    "location" => strtolower("{$_SESSION['role']}.php")
                ]);
                exit;
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
        }
        exit;
    }
    else if ($_POST["action"] == "deleteProfile") {
        session_start();

        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);

        $user_id = $_SESSION['user_id'];
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                echo json_encode(["success" => true, "message" => "Profile deleted successfully.",
                    "location" => "home.php"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
        }
        exit;

    }
    else if ($_POST["action"] == "addUser") {
        session_start();
        $response = ["success" => false];


        if (empty($_POST["first_name"]) || empty($_POST["last_name"]) || empty($_POST["email"]) || empty($_POST["password"]) || empty($_POST["role"])) {
            $response["message"] = "All required fields must be provided.";
            echo json_encode($response);
            exit();
        }
        $allowed_roles = ['USER', 'ADMIN'];
        if (!in_array($_POST['role'], $allowed_roles)) {
            $response["message"] = "Invalid data.";
            echo json_encode($response);
            exit();
        }

        $first_name = mysqli_real_escape_string($conn, $_POST["first_name"]);
        $last_name = mysqli_real_escape_string($conn, $_POST["last_name"]);
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $password = mysqli_real_escape_string($conn, $_POST["password"]);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $phone = isset($_POST["phone"]) ? mysqli_real_escape_string($conn, $_POST["phone"]) : null;
        $role = mysqli_real_escape_string($conn, $_POST["role"]);
        $gender = mysqli_real_escape_string($conn, $_POST["gender"]);


        $conn->begin_transaction();

        $sql_users = "INSERT INTO users (email, password, role, created_at) VALUES (?, ?, ?, NOW())";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("sss", $email, $hashedPassword, $role);

        if (!$stmt_users->execute()) {
            $response["message"] = "Error inserting into users: " . $stmt_users->error;
            $conn->rollback();
            echo json_encode($response);
            $stmt_users->close();
            exit();
        }

        $user_id = $stmt_users->insert_id;

        $sql_profile = "INSERT INTO personal_info (user_id, first_name, last_name, phone, gender) VALUES (?, ?, ?, ?, ?)";
        $stmt_profile = $conn->prepare($sql_profile);
        $stmt_profile->bind_param("issss", $user_id, $first_name, $last_name, $phone, $gender);

        if (!$stmt_profile->execute()) {
            $response["message"] = "Error inserting into profile_info: " . $stmt_profile->error;
            $conn->rollback();
            echo json_encode($response);
            $stmt_users->close();
            $stmt_profile->close();
            exit();
        }

        if ($conn->commit()) {
            $response["success"] = true;
            $response["message"] = "User added successfully.";
            $response["user_id"] = $user_id;
            $response["first_name"] = $first_name;
            $response["last_name"] = $last_name;
            $response["email"] = $email;
            $response["role"] = $role;
        } else {
            $response["message"] = "Transaction commit failed.";
        }

        $stmt_users->close();
        $stmt_profile->close();

        echo json_encode($response);
    }
    else {
        echo json_encode(["success" => false, "message" => "Invalid action."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Action parameter is missing."]);
}
mysqli_close($conn);