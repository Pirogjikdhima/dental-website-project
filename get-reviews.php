<?php
$conn = require 'database.php';

try {
    $query = "
        SELECT 
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
        WHERE 
            r.rating >= 4 
        ORDER BY 
            RAND() 
        LIMIT 3";

    $result = $conn->query($query);

    $reviews = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = [
                'rating' => $row['rating'],
                'comment' => $row['comment'],
                'created_at' => $row['created_at'],
                'user' => $row['first_name'] . ' ' . $row['last_name']
            ];
        }
    }

    echo json_encode($reviews);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
