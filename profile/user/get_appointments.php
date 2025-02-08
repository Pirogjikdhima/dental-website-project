<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'USER') {
    header("Location: ../../404.html");
    exit;
}

$conn = require_once "../../database.php";
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "SELECT  b.user_id,
                    b.doctor_id, 
                    b.description, 
                    b.date,
                    b.time,
                    b.service_id,
                    s.service_name,
                    d.name AS doctor_name
            FROM bookings b 
            INNER JOIN users u ON u.user_id = b.user_id
            INNER JOIN services s ON s.service_id = b.service_id
            INNER JOIN doctors d ON d.doctor_id = b.doctor_id
            WHERE b.user_id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement.']);
        $conn->close();
        exit;
    }

    $stmt->bind_param("i", $user_id);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to execute the SQL statement.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $result = $stmt->get_result();

    $appointments = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $appointments[] = [
                'date' => $row['date'],
                'time' => $row['time'],
                'description' => $row['description'],
                'doctor' => $row['doctor_name'],
                'service' => $row['service_name'],
            ];
        }
    }

    echo json_encode(['success'=> true, 'data'=>$appointments]);

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../../404.html");
    $conn->close();
    exit;
}

