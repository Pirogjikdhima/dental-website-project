<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../404.html");
    exit;
}

$conn = require_once "../../database.php";
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "SELECT  b.booking_id,
                    b.service_id,
                    b.date,
                    b.time,
                    s.service_name,
                    CONCAT(p.first_name, ' ', p.last_name) AS user_name,
                    u.email AS user_email
            FROM bookings b
            INNER JOIN services s ON s.service_id = b.service_id
            INNER JOIN personal_info p ON p.user_id = b.user_id
            INNER JOIN users u ON u.user_id = b.user_id";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement.']);
        $conn->close();
        exit;
    }

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to execute the SQL statement.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $result = $stmt->get_result();

    $bookings = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = [
                'booking_id' => $row['booking_id'],
                'user_email' => $row['user_email'],
                'date' => $row['date'],
                'time' => $row['time'],
                'service' => $row['service_name'],
                'name' => $row['user_name']
            ];
        }
    }

    echo json_encode(['success'=> true, 'data'=>$bookings]);

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../../404.html");
    $conn->close();
    exit;
}