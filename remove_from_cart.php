<?php
$conn = require "database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['cartId'])) {
            throw new Exception('Cart ID is missing');
        }

        $cartId = filter_var($_POST['cartId'], FILTER_VALIDATE_INT);
        if ($cartId === false) {
            throw new Exception('Invalid Cart ID');
        }

        $checkQuery = "SELECT 1 FROM shopping_cart WHERE cart_id = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $cartId);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            throw new Exception('Cart ID does not exist');
        }
        $checkStmt->close();

        $quantity = 0;
        $quantityQuery = "SELECT quantity FROM shopping_cart WHERE cart_id = ?";
        $quantityStmt = $conn->prepare($quantityQuery);
        $quantityStmt->bind_param("i", $cartId);
        $quantityStmt->execute();
        $quantityStmt->bind_result($quantity);
        $quantityStmt->fetch();
        $quantityStmt->close();

        if ($quantity > 1) {
            $updateQuery = "UPDATE shopping_cart SET quantity = quantity - 1 WHERE cart_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $cartId);

            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update quantity');
            }
            $updateStmt->close();
        } else {
            $deleteQuery = "DELETE FROM shopping_cart WHERE cart_id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $cartId);

            if (!$deleteStmt->execute()) {
                throw new Exception('Failed to remove item');
            }
            $deleteStmt->close();
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
