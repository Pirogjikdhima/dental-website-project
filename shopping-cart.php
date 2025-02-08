<?php
require_once 'vendor/autoload.php';
$conn = require_once "database.php";

session_start();

if (isset($_SESSION['user_id']) && $_SESSION['role'] == "USER") {
    $user_id = $_SESSION['user_id'];
} else {
    header('Location: signin.html');
    exit;
}
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    try {
        $query = "SELECT sc.quantity, si.product_name, si.price ,si.id as product_id
                  FROM shopping_cart sc
                  JOIN shop_items si ON sc.product_id = si.id
                  WHERE sc.user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $totalAmount = 0;
        $cartItems = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $price = $row['price'] * 100;
                $quantity = $row['quantity'];
                $totalAmount += $price * $quantity;
                $cartItems[] = $row;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $totalAmount,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'metadata' => [
                'integration' => 'shopping_cart',
            ],
        ]);

        $conn->begin_transaction();

        try {
            $paidQuery = "INSERT INTO bought_products (user_id, product_id, quantity, price, date) VALUES (?, ?, ?, ?, NOW())";
            $paidStmt = $conn->prepare($paidQuery);

            foreach ($cartItems as $item) {
                $paidStmt->bind_param("iiii", $user_id, $item['product_id'], $item['quantity'], $item['price']);
                $paidStmt->execute();
            }

            $logQuery = "INSERT INTO payment_logs (user_id, payment_intent_id, amount, currency, status, created_at)
                         VALUES (?, ?, ?, ?, ?, NOW())";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param(
                "isiss",
                $user_id,
                $paymentIntent->id,
                $totalAmount,
                $paymentIntent->currency,
                $paymentIntent->status
            );
            $logStmt->execute();

            $deleteQuery = "DELETE FROM shopping_cart WHERE user_id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $user_id);
            $deleteStmt->execute();

            $conn->commit();

            echo json_encode([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
        ]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productId'])) {
    try {
        $productId = filter_var($_POST['productId'], FILTER_VALIDATE_INT);

        if ($productId === false) {
            throw new Exception('Invalid product ID');
        }
        $conn->begin_transaction();

        $query = "SELECT 1 FROM shop_items WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Product does not exist.']);
            exit;
        }
        $stmt->close();

        $query = "SELECT cart_id FROM shopping_cart WHERE product_id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $productId, $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $updateQuery = "UPDATE shopping_cart SET quantity = quantity + 1 WHERE product_id = ? AND user_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ii", $productId, $user_id);

            if ($updateStmt->execute()) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Quantity updated']);
            } else {
                throw new Exception('Failed to update quantity');
            }
            $updateStmt->close();
        } else {
            $insertQuery = "INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("ii", $user_id, $productId);

            if ($insertStmt->execute()) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Product added to cart']);
            } else {
                throw new Exception('Failed to add product');
            }
            $insertStmt->close();
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT sc.cart_id, sc.quantity, si.product_name, si.product_image_url, si.price 
              FROM shopping_cart sc
              JOIN shop_items si ON sc.product_id = si.id
              WHERE sc.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./images/crystal-dental-logo.png" type="image/x-icon">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/shoppingCart.css">
    <link rel="stylesheet" href="./css/header&footer.css">
    <link rel="stylesheet" href="./css/bot.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
<?php include("./partials/navigation.php"); ?>
<header>
    <h1>Your Shopping Cart</h1>
</header>

<section class="cart-items">
    <?php
    $totalPrice = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "
    <div class='cart-item'>
        <img src='" . $row['product_image_url'] . "' alt='" . $row['product_name'] . "'>
        <div class='item-details'>
            <h3>" . $row['product_name'] . "</h3>
            <p>Quantity: " . $row['quantity'] . "</p>
            <p>Price: US$" . number_format($row['price'] * $row['quantity'], 2) . "</p>
        </div>
        <button class='remove-item' data-cart-id='" . $row['cart_id'] . "'>Remove</button>
    </div>";
            $totalPrice += $row['price'] * $row['quantity'];
        }
    } else {
        echo "<p class='empty-cart-message'>Your cart is empty.</p>";
    } ?>
</section>
<section class="cart-summary">
    <div class="summary-details">
        <h3>Total Price: US$<?php echo number_format($totalPrice, 2); ?></h3>
    </div>
</section>

<section class="cart-actions">
    <button type="button" id="checkout-button" class="buy-now">Buy Now</button>
</section>

<?php include("./partials/footer.html"); ?>
<?php include("./partials/bot.html") ?>
<script src="js/bot.js"></script>
<script src="./js/navigation.js"></script>
<script src="js/shoppingCart.js"></script>
<script src="js/activityTracker.js"></script>
</body>
</html>