<?php
session_start();
$conn = require "database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./images/crystal-dental-logo.png" type="image/x-icon">
    <title>Crystal Dental Shop</title>
    <link rel="stylesheet" href="css/header&footer.css">
    <link rel="stylesheet" href="./css/shop.css">
    <link rel="stylesheet" href="./css/bot.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include("./partials/navigation.php") ?>

<header class="header">
    <div class="welcome-section">
        <h1>Welcome to our shop</h1>
        <p>We are delighted to have you here! At Crystal Dental Shop, we're dedicated to providing exceptional dental
            care in a warm and friendly environment. From sparkling smiles to expert care, we specialize in making your
            dental visits comfortable and stress-free.</p>
    </div>
    <div class="logo-section">
        <img src="./images/shop.png" alt="Crystal Dental Shop Logo">
    </div>
</header>
<section class="products-header">
    <h1>Our Products</h1>
    <div class="sort-section">
        <select name="sort" class="sort-dropdown">
            <option value="rating">Sort by average rating</option>
            <option value="price-low-high">Sort by price: low to high</option>
            <option value="price-high-low">Sort by price: high to low</option>
        </select>
        <div class="view-toggle">
            <button class="grid-view">Grid</button>
            <button class="list-view">List</button>
        </div>
    </div>
</section>
<section class="secondSection">
    <section class="products-section product-grid" id="products-section">
        <?php
        try {

            $query = "SELECT * FROM shop_items";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                throw new Exception("Error executing query: " . mysqli_error($conn));
            }

            if (mysqli_num_rows($result) > 0) {

                while ($row = mysqli_fetch_assoc($result)) {
                    $discount = $row['discount_percentage'] ? "-" . $row['discount_percentage'] . "%" : "";
                    $price = "US$" . number_format($row['price'], 2);
                    $oldPrice = $row['old_price'] ? "<span class='old-price'>US$" . number_format($row['old_price'], 2) . "</span>" : "";
                    $ratingCount = $row['review_count'] ?: 0;
                    $imageUrl = $row['product_image_url'];
                    $productName = $row['product_name'];

                    echo "
                <div class='product-container' data-product-id='" . $row['id'] . "'>

                    <span class='discount'>$discount</span>
                    <img src='$imageUrl' alt='$productName'>
                    <h3>$productName</h3>
                    <div class='rating'>★★★★★ <span>$ratingCount</span></div>
                    <button class='add-to-cart-button'" . (!isset($_SESSION['role']) || $_SESSION['role'] !== 'USER' ? " style='display: none;'" : "") . ">Add to Cart</button>
                    <div class='price'>$price $oldPrice</div>
                </div>";
                }
            } else {
                echo "<p>No products found in the database.</p>";
            }
        } catch (Exception $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        } finally {
            if (isset($conn) && $conn) {
                mysqli_close($conn);
            }
        }
        ?>
    </section>
</section>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'ADMIN'): ?>
    <div class="admin-section">
        <button class="admin-button" onclick="window.location.href='edit_shopitems.php';">Edit Items</button>
    </div>
<?php endif; ?>
<section class="actions">
    <div class="action-card">
        <h3>Have Questions?</h3>
        <p>If you have questions about our products, pricing, or process,you can see some questions and answers here
            !</p>
        <a href="./faq.php" class="action-button">FAQ</a>
    </div>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'USER'): ?>
        <div class="action-card">
            <h3>Start Buying</h3>
            <p>Start the purchase process by understanding how to place orders for our products.</p>
            <a href="shopping-cart.php" class="action-button">Buy Now</a>
        </div>
    <?php endif; ?>
</section>

<?php include("./partials/bot.html") ?>
<?php include("./partials/footer.html") ?>

<script src="js/bot.js"></script>
<script src="./js/shop.js"></script>
<script src="js/activityTracker.js"></script>
<script src="./js/navigation.js"></script>
</body>
</html>