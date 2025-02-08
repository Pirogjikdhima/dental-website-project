<?php
session_start();

$conn = require "database.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;

    if (!$doctor_id) {
        echo json_encode(array(
            "success" => false,
            "message" => "Doctor ID is required."
        ));
        mysqli_close($conn);
        exit;
    }

    $query = "SELECT date, time FROM bookings WHERE doctor_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $slots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $formattedTime = date("H:i", strtotime($row['time']));
        $row['time'] = $formattedTime;

        $slots[] = $row;
    }

    echo json_encode($slots);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: booking-response.php?status=error&message=" . urlencode("You must be logged in to make a booking."));
        exit;
    }

    $user_id = intval($_SESSION['user_id']);
    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN');
    $service_id = isset($_POST['service']) ? intval($_POST['service']) : null;
    $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
    $date = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : null;
    $time = isset($_POST['time']) ? mysqli_real_escape_string($conn, $_POST['time']) : null;
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : null;

    $errors = array();
    if (!$service_id) $errors['service'] = "Service is required.";
    if (!$doctor_id) $errors['doctor'] = "Doctor is required.";
    if (!$date || !strtotime($date)) $errors['date'] = "Valid date is required.";
    if (!$time || !preg_match("/^\d{2}:\d{2}$/", $time)) $errors['time'] = "Valid time is required.";

    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $tomorrow = (clone $today)->modify('+1 day');
    $maxDate = (clone $today)->modify('+8 days');

    $bookingDate = DateTime::createFromFormat('Y-m-d', $date);

    if (!$bookingDate || $bookingDate < $tomorrow || $bookingDate > $maxDate) {
        $errors['date'] = "Booking date must be between tomorrow and 8 days in advance.";
    }

    if (!empty($errors)) {
        header("Location: booking-response.php?status=error&message=" . urlencode("Validation errors: " . implode(", ", $errors)));
        exit;
    }

    if (!$isAdmin) {
        $tomorrowStr = $tomorrow->format('Y-m-d');
        $maxDateStr = $maxDate->format('Y-m-d');

        $countQuery = "
        SELECT COUNT(*) AS count 
        FROM bookings 
        WHERE user_id = ? 
        AND date BETWEEN ? AND ?
    ";
        $stmtCount = mysqli_prepare($conn, $countQuery);
        mysqli_stmt_bind_param($stmtCount, 'iss', $user_id, $tomorrowStr, $maxDateStr);
        mysqli_stmt_execute($stmtCount);
        $result = mysqli_stmt_get_result($stmtCount);
        $row = mysqli_fetch_assoc($result);
        $totalBookings = intval($row['count']);
        mysqli_stmt_close($stmtCount);

        if ($totalBookings >= 5) {
            header("Location: booking-response.php?status=error&message=" . urlencode("You can only have a maximum of 5 bookings within the next 8 days."));
            mysqli_close($conn);
            exit;
        }
    }

    $checkSlotQuery = "
        SELECT booking_id 
        FROM bookings 
        WHERE doctor_id = ? AND date = ? AND time = ?
    ";
    $stmt = mysqli_prepare($conn, $checkSlotQuery);
    mysqli_stmt_bind_param($stmt, 'iss', $doctor_id, $date, $time);
    mysqli_stmt_execute($stmt);
    $checkSlotResult = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($checkSlotResult) > 0) {
        header("Location: booking-response.php?status=error&message=" . urlencode("The selected time slot is already booked."));
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($stmt);

    $insertQuery = "
        INSERT INTO bookings (user_id, doctor_id, date, time, description, service_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'iissss', $user_id, $doctor_id, $date, $time, $description, $service_id);
    $insertResult = mysqli_stmt_execute($stmt);

    if ($insertResult) {
        header("Location: booking-response.php?status=success&message=" . urlencode("Booking successfully created!"));
    } else {
        header("Location: booking-response.php?status=error&message=" . urlencode("Failed to create booking."));
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}