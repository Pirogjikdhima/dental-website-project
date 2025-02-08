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

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $query = "SELECT 
                b.quantity,
                b.date AS time,
                b.price,
                s.product_name AS name
              FROM bought_products b 
              INNER JOIN shop_items s ON b.product_id = s.id 
              WHERE b.user_id = ?
              ORDER BY b.date DESC";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement.']);
        $conn->close();
        exit;
    }

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to execute the SQL statement.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $result = $stmt->get_result();
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch the results.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $products]);

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../../404.html");
    $conn->close();
    exit;
}

