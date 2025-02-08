<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$db_server = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "detyrekursi";

$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $result = mysqli_query($conn, "SELECT 
            r.rating, 
            r.comment, 
            r.created_at, 
            u.first_name, 
            u.last_name 
        FROM 
            reviews r
        INNER JOIN 
            personal_info u 
        ON 
            r.user_id = u.user_id 
        ORDER BY rating DESC LIMIT 15");

    if ($result && mysqli_num_rows($result) > 0) {
        $reviews = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No reviews found.']);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method. Only GET requests are allowed."]);
}
?>
