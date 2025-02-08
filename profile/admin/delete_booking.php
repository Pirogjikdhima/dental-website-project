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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : null;

    if (empty($booking_id)) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required.']);
        exit;
    }

    $sql = "DELETE FROM bookings WHERE booking_id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement.']);
        $conn->close();
        exit;
    }

    $stmt->bind_param('i', $booking_id);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete booking.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Booking deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete booking data.']);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../../404.html");
    $conn->close();
    exit;
}
?>
