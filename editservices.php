<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: 404.html");
    exit();
}
$conn = require "database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = '';
    $message = '';

    if (isset($_POST['update'])) {
        $service_id = $_POST['service_id'];
        $service_name = $_POST['service_name'];
        $description = $_POST['description'];
        $icon_url = $_POST['icon_url'];
        $price = $_POST['price'];

        if ($price <= 0) {
            $status = 'error';
            $message = 'Price should be positive.';
        } else {
            $query_old = "SELECT service_name, description, icon_url, price FROM services WHERE service_id = $service_id";
            $result_old = mysqli_query($conn, $query_old);
            $row_old = mysqli_fetch_assoc($result_old);

            if ($row_old['service_name'] == $service_name && $row_old['description'] == $description && $row_old['icon_url'] == $icon_url && $row_old['price'] == $price) {
                $status = 'error';
                $message = 'You didn\'t change anything.';
            } else {
                $query = "UPDATE services SET service_name='$service_name', description='$description', icon_url='$icon_url', price='$price' WHERE service_id=$service_id";
                if (mysqli_query($conn, $query)) {
                    $status = 'success';
                    $message = 'Service updated successfully.';
                } else {
                    $status = 'error';
                    $message = 'Failed to update service.';
                }
            }
        }
    }

    if (isset($_POST['delete'])) {
        $service_id = $_POST['service_id'];

        if (empty($service_id)) {
            $status = 'error';
            $message = 'Service ID is required to delete.';
        } else {
            $query = "DELETE FROM services WHERE service_id=$service_id";
            if (mysqli_query($conn, $query)) {
                $status = 'success';
                $message = 'Service deleted successfully.';
            } else {
                $status = 'error';
                $message = 'Failed to delete service.';
            }
        }
    }

    if (isset($_POST['add'])) {
        $service_name = $_POST['service_name'];
        $description = $_POST['description'];
        $icon_url = $_POST['icon_url'];
        $price = $_POST['price'];

        if (empty($service_name) || empty($description) || empty($icon_url) || empty($price)) {
            $status = 'error';
            $message = 'All fields are required to add a service.';
        } elseif ($price <= 0) {
            $status = 'error';
            $message = 'Price should be positive.';
        } else {
            $query = "INSERT INTO services (service_name, description, icon_url, price) VALUES ('$service_name', '$description', '$icon_url', '$price')";
            if (mysqli_query($conn, $query)) {
                $status = 'success';
                $message = 'Service added successfully.';
            } else {
                $status = 'error';
                $message = 'Failed to add service.';
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?status=$status&message=" . urlencode($message));
    exit();
}

$query = "SELECT * FROM services";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Services</title>
    <link rel="stylesheet" href="css/editservices.css">
    <link rel="stylesheet" href="./css/header&footer.css"/>
    <link rel="stylesheet" href="./css/bot.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include("./partials/navigation.php") ?>
<h1>Edit Services Here</h1>
<table>
    <thead>
    <tr>
        <th>Icon</th>
        <th>Service Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <form method="POST">
                <td><label>
                        <input type="text" name="icon_url" value="<?= htmlspecialchars($row['icon_url']) ?>">
                    </label>
                </td>
                <td><label>
                        <input type="text" name="service_name" value="<?= htmlspecialchars($row['service_name']) ?>">
                    </label>
                </td>
                <td><label>
                        <textarea name="description"><?= htmlspecialchars($row['description']) ?></textarea>
                    </label>
                </td>
                <td><label>
                        <input type="text" name="price" value="<?= htmlspecialchars($row['price']) ?>">
                    </label>
                </td>
                <td>
                    <input type="hidden" name="service_id" value="<?= $row['service_id'] ?>">
                    <button type="submit" name="update">Update</button>
                    <button type="submit" name="delete">Delete</button>
                </td>
            </form>
        </tr>
    <?php endwhile; ?>
    <tr>
        <form method="POST">
            <td><label>
                    <input type="text" name="icon_url" placeholder="Icon URL">
                </label>
            </td>
            <td><label>
                    <input type="text" name="service_name" placeholder="Service Name">
                </label>
            </td>
            <td><label>
                    <textarea name="description" placeholder="Description"></textarea>
                </label>
            </td>
            <td><label>
                    <input type="text" name="price" placeholder="Price">
                </label>
            </td>
            <td>
                <button type="submit" name="add">Add</button>
            </td>
        </form>
    </tr>
    </tbody>
</table>
<?php include("./partials/footer.html") ?>
<?php include("./partials/bot.html") ?>
<script src="js/bot.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const message = urlParams.get('message');

        if (status && message) {
            if (status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                });
            } else if (status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                });
            }
            const newUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });

</script>
<script src="./js/navigation.js"></script>
<script src="js/activityTracker.js"></script>
</body>
</html>
