<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../404.html");
    exit;
}

$conn = require "../../database.php";

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {

    $sql = "SELECT rating,comment FROM reviews WHERE review_id = ?";
    $stmt = $conn->prepare($sql);

    $review_id = $_GET['id'];

    if ($stmt) {
        $stmt->bind_param("i", $review_id);
        $stmt->execute();

        $rating = $comment = '';
        $stmt->bind_result($rating, $comment);

        if ($stmt->fetch()) {
            echo json_encode(["success" => true, "data" => ["rating" => $rating, "comment" => $comment]]);
        } else {
            echo json_encode(["success" => false, "message" => "Review not found."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
    }
    $conn->close();
}
else if($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['id'])) {

    $sql = "
    SELECT 
        r.review_id,
        r.rating,
        r.comment,
        r.created_at,
        u.email,
        CONCAT(p.first_name, ' ', p.last_name) as full_name
    FROM reviews r
    LEFT JOIN users u ON u.user_id = r.user_id
    LEFT JOIN personal_info p ON p.user_id = r.user_id
    ORDER BY r.created_at DESC
";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $review_id = $rating = $comment = $created_at = $email = $full_name = '';

        $stmt->execute();
        $stmt->bind_result($review_id, $rating, $comment, $created_at, $email, $full_name);

        $reviews = [];
        while ($stmt->fetch()) {
            $reviews[] = [
                "review_id" => $review_id,
                "rating" => $rating,
                "comment" => $comment,
                "created_at" => $created_at,
                "email" => $email,
                "full_name" => $full_name
            ];
        }

        echo json_encode(["success" => true, "data" => $reviews]);

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
    }
    $conn->close();

}
else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deleteReview') {

    $sql = "DELETE FROM reviews WHERE review_id = ?";
    $stmt = $conn->prepare($sql);

    $review_id = $_POST['review_id'];

    if ($stmt) {
        $stmt->bind_param("i", $review_id);
        $stmt->execute();

        if ($stmt->affected_rows) {
            echo json_encode(["success" => true, "message" => "Review deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete review."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
    }
    $conn->close();
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'editReview'){

    $sql = "UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ?";
    $stmt = $conn->prepare($sql);

    $review_id = $_POST['review_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    if ($stmt) {
        $stmt->bind_param("isi", $rating, $comment, $review_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Review updated successfully." , "data"=>["rating"=>$rating,"comment"=>$comment]]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update review."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
    }
    $conn->close();
}
else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>