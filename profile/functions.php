<?php
function fetchUserInfo($conn, $userId)
{
    $sql = "
        SELECT
            u.email,
            p.phone,
            p.gender,
            p.photo_path
        FROM personal_info p
        INNER JOIN users u ON p.user_id = u.user_id
        WHERE u.user_id = ?";

    $email = $phone_number = $gender = $photo_path = "";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($email, $phone_number, $gender, $photo_path);
        if ($stmt->fetch()) {
            return compact('email',  'phone_number', 'gender', 'photo_path');
        } else {
            return ["error" => "Error fetching user information: " . $stmt->error];
        }
        $stmt->close();
    } else {
        return ["error" => "Error preparing statement: " . $conn->error];
    }
}
