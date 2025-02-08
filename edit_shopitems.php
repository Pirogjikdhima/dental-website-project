<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: 404.html");
    exit();
}
$conn = require 'database.php';

$message = null;
$redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add']) || isset($_POST['update'])) {
        $product_name = $_POST['product_name'];
        $product_image_url = $_POST['product_image_url'];
        $discount_percentage = $_POST['discount_percentage'];
        $review_count = $_POST['review_count'];
        $price = $_POST['price'];
        $old_price = $_POST['old_price'];

        if (empty($product_name) || empty($product_image_url) || !is_numeric($discount_percentage) ||
            !is_numeric($review_count) || !is_numeric($price) || !is_numeric($old_price)) {
            $_SESSION['message'] = ['title' => 'Error', 'text' => 'All fields are required, and numeric fields must have valid numbers.', 'icon' => 'error'];
        } elseif ($price <= 0 || $old_price <= 0) {
            $_SESSION['message'] = ['title' => 'Error', 'text' => 'Price and old price must be positive values.', 'icon' => 'error'];
        } elseif ($discount_percentage < 0 || $discount_percentage > 100) {
            $_SESSION['message'] = ['title' => 'Error', 'text' => 'Discount percentage must be between 0 and 100.', 'icon' => 'error'];
        } else {
            $expected_price = $old_price * (1 - ($discount_percentage / 100));
            $expected_price = round($expected_price, 2);

            if (abs($price - $expected_price) > 0.01) {
                $_SESSION['message'] = ['title' => 'Price Mismatch',
                    'text' => "For a {$discount_percentage}% discount on {$old_price}, the price should be {$expected_price}. Please adjust the price.",
                    'icon' => 'warning'];
            } else {
                if (isset($_POST['update'])) {
                    $id = $_POST['id'];
                    $existing_item_query = "SELECT * FROM shop_items WHERE id = $id";
                    $result = mysqli_query($conn, $existing_item_query);
                    $existing_item = mysqli_fetch_assoc($result);

                    if ($product_name === $existing_item['product_name'] &&
                        $product_image_url === $existing_item['product_image_url'] &&
                        $discount_percentage == $existing_item['discount_percentage'] &&
                        $review_count == $existing_item['review_count'] &&
                        $price == $existing_item['price'] &&
                        $old_price == $existing_item['old_price']) {
                        $_SESSION['message'] = ['title' => 'Info', 'text' => 'No changes were made to the item.', 'icon' => 'info'];
                    } else {
                        $query = "UPDATE shop_items 
                                  SET 
                                         product_name='$product_name', 
                                         product_image_url='$product_image_url', 
                                         discount_percentage=$discount_percentage, 
                                         review_count=$review_count, 
                                         price=$price, 
                                         old_price=$old_price 
                                  WHERE  id=$id";
                        if (mysqli_query($conn, $query)) {
                            $_SESSION['message'] = ['title' => 'Success', 'text' => 'Item updated successfully.', 'icon' => 'success'];
                            $redirect = true;
                        } else {
                            $_SESSION['message'] = ['title' => 'Error', 'text' => 'Failed to update item.', 'icon' => 'error'];
                        }
                    }
                }

                if (isset($_POST['add'])) {
                    $query = "INSERT INTO shop_items (product_name, product_image_url, discount_percentage, review_count, price, old_price) 
                             VALUES ('$product_name', '$product_image_url', $discount_percentage, $review_count, $price, $old_price)";
                    if (mysqli_query($conn, $query)) {
                        $_SESSION['message'] = ['title' => 'Success', 'text' => 'Item added successfully.', 'icon' => 'success'];
                        $redirect = true;
                    } else {
                        $_SESSION['message'] = ['title' => 'Error', 'text' => 'Failed to add item.', 'icon' => 'error'];
                    }
                }
            }
        }
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];

        if (empty($id)) {
            $_SESSION['message'] = ['title' => 'Error', 'text' => 'ID is required to delete an item.', 'icon' => 'error'];
        } else {
            $query = "DELETE FROM shop_items WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                $_SESSION['message'] = ['title' => 'Success', 'text' => 'Item deleted successfully.', 'icon' => 'success'];
                $redirect = true;
            } else {
                $_SESSION['message'] = ['title' => 'Error', 'text' => 'Failed to delete item.', 'icon' => 'error'];
            }
        }
    }
}

if ($redirect) {
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

$query = "SELECT * FROM shop_items";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Shop Items</title>
    <link rel="stylesheet" href="css/edit_shopitems.css">
    <link rel="stylesheet" href="css/header&footer.css">
    <link rel="stylesheet" href="./css/bot.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include("./partials/navigation.php") ?>

<h1>Edit Shop Items</h1>
<table>
    <thead>
    <tr>
        <th>Product Image</th>
        <th>Product Name</th>
        <th>Discount (%)</th>
        <th>Review Count</th>
        <th>Price</th>
        <th>Old Price</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                <td>
                    <label>
                        <input type="text" name="product_image_url" value="<?= htmlspecialchars($row['product_image_url']) ?>">
                    </label>
                </td>
                <td>
                    <label>
                        <input type="text" name="product_name" value="<?= htmlspecialchars($row['product_name']) ?>">
                    </label>
                </td>
                <td>
                    <label>
                        <input type="number" name="discount_percentage" value="<?= htmlspecialchars($row['discount_percentage']) ?>">
                    </label>
                </td>
                <td>
                    <label>
                        <input type="number" name="review_count" value="<?= htmlspecialchars($row['review_count']) ?>">
                    </label>
                </td>
                <td>
                    <label>
                        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($row['price']) ?>">
                    </label>
                </td>
                <td>
                    <label>
                        <input type="number" step="0.01" name="old_price" value="<?= htmlspecialchars($row['old_price']) ?>">
                    </label>
                </td>
                <td>
                    <div class="actions">
                        <button type="submit" name="update">Update</button>
                        <button type="submit" name="delete">Delete</button>
                    </div>
                </td>
            </form>
        </tr>
    <?php endwhile; ?>
    <tr>
        <form method="POST">
            <td><label><input type="text" name="product_image_url" placeholder="Image URL"></label></td>
            <td><label><input type="text" name="product_name" placeholder="Product Name"></label></td>
            <td><label><input type="number" name="discount_percentage" placeholder="Discount (%)"></label></td>
            <td><label><input type="number" name="review_count" placeholder="Review Count"></label></td>
            <td><label><input type="number" step="0.01" name="price" placeholder="Price"></label></td>
            <td><label><input type="number" step="0.01" name="old_price" placeholder="Old Price"></label></td>
            <td><button type="submit" name="add">Add</button></td>
        </form>
    </tr>
    </tbody>
</table>
<?php include("./partials/footer.html") ?>
<?php include("./partials/bot.html") ?>
<script src="js/bot.js"></script>
<script src="./js/navigation.js"></script>
<script src="js/activityTracker.js"></script>

<script>
    <?php if (isset($_SESSION['message'])): ?>
    Swal.fire({
        title: '<?= $_SESSION['message']['title'] ?>',
        text: '<?= $_SESSION['message']['text'] ?>',
        icon: '<?= $_SESSION['message']['icon'] ?>',
    });
    <?php unset($_SESSION['message']); endif; ?>
</script>

</body>
</html>
