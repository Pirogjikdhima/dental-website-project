<?php
require_once 'sessionCheck.php';
$conn = require_once 'database.php';
require_once 'profile/functions.php';

$userInfo = fetchUserInfo($conn, $_SESSION['user_id']);
if (isset($userInfo['error'])) {
    session_unset();
    session_destroy();
    $conn->close();
    header('Location: 404.html');
    exit;
}

$email = $userInfo['email'];
$name = $_SESSION['first_name'];
$surname = $_SESSION['last_name'];
$phone_number = $userInfo['phone_number'];
$gender = $userInfo['gender'];
$photo_path = $userInfo['photo_path'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./images/crystal-dental-logo.png" type="image/x-icon">
    <title>Admin Profile</title>
    <?php include("./profile/styles.html"); ?>
</head>
<body>

<?php include("./partials/navigation.php") ?>

<section id="main-section">

    <div class="profile-card">
        <h2><?php echo $name, " ", $surname; ?></h2>

        <div class="profile-picture">
            <img src="<?php echo empty($photo_path) ? './images/profile/default.jpg' : $photo_path; ?>" alt="profile-photo">
        </div>

        <div class="profile-info">
                <span id="info-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/info.png" alt="info">
                    Personal Information
                </span>

            <span id="dashboard-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/dashboard.png" alt="dashboard">
                    Dashboard
                </span>
            <span id="reviews-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/review.png" alt="reviews">
                    Reviews
                </span>
            <span id="newsletter-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/newsletter.png" alt="newsletter">
                    Newsletter
                </span>
            <span id="booking-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/bookings.png" alt="booking">
                    Bookings
                </span>
            <span id="products-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/products.png" alt="products">
                    Products
                </span>
            <span id="services-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/services.png" alt="services">
                    Services
            </span>

            <span id="password-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/password.png" alt="change password">
                    Change Password
                </span>
            <span id="logout-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/logout.png" alt="logout">
                    Logout
                </span>

            <span id="delete-btn" style=" margin:3px;">
                    <img src="./images/profile/dashboard/delete.png" alt="delete">
                    Delete Account
                </span>

        </div>

    </div>

    <input type="hidden" id="user-role" value="<?php echo $_SESSION['role']; ?>">

    <div class="welcome-message" id="welcome-section">
        <h2 style="text-align: center">Welcome, Admin!</h2>
        <p>Here are your key functions to manage the platform:</p>
        <ul>
            <li><strong>Personal Information:</strong> Update your personal details, such as name and email.</li>
            <li><strong>Dashboard:</strong> View and manage the users of the platform.</li>
            <li><strong>Reviews:</strong> Manage user reviews for services or products.</li>
            <li><strong>Newsletter:</strong> Create and send newsletters to users.</li>
            <li><strong>Bookings:</strong> Oversee and manage user bookings.</li>
            <li><strong>Products:</strong> Manage the products or services offered on the platform.</li>
            <li><strong>Services:</strong> Manage the services provided on the platform.</li>
            <li><strong>Change Password:</strong> Update your account password for security.</li>
            <li><strong>Logout:</strong> Log out of the admin account securely.</li>
            <li><strong>Delete Account:</strong> Permanently delete your admin account if necessary.</li>
        </ul>
    </div>

    <div class="personal-info hidden" id="info-section">
        <h2>Update Your Personal Information</h2>

        <form class="update-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="updateProfile">

            <div class="form-group">
                <label for="profile-picture">Profile Picture</label>
                <input type="file" id="profile-picture" name="profile-picture" accept="image/*">

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $name ?>" autocomplete="given-name">
                    <span id="nameError" class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" id="surname" name="surname" value="<?php echo $surname ?>"
                           autocomplete="family-name">
                    <span id="surnameError" class="error-message"></span>
                </div>

            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $email ?>" autocomplete="email">
                <span id="emailError" class="error-message"></span>
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo $phone_number ?>" autocomplete="tel">
                    <span id="phoneError" class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="male" <?php if ($gender == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if ($gender == 'female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>

            </div>

            <button type="submit" class="update-btn">Update Profile</button>

        </form>

    </div>

    <div class="dashboard hidden" id="dashboard-section">

        <div class="data-table-container">
            <table id="usersTable" class="table-centered display" width="100%">
                <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </thead>
            </table>
            <div class="button-right" style="text-align: right; margin-top: 10px;">
                <button class="action-btn add-btn" id="add-user-btn">Add User</button>
            </div>
        </div>

    </div>

    <div class="newsletter hidden" id="newsletter-section">

        <h2 style="text-align:center">Submit Your Newsletter</h2>

        <form id="newsletterForm" class="update-form" method="POST">
            <input type="hidden" name="action" value="newsletter">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" placeholder="Enter the subject" required>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" placeholder="Write your newsletter here..." required></textarea>
            </div>
            <button type="submit" class="update-btn">Submit</button>
        </form>
    </div>

    <div class="reviews hidden" id="reviews-section">
        <div class="tab-content">
            <div class="table-container">
                <table id="reviewsTable" class="display" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Rating</th>
                        <th width="150">Comment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="booking hidden" id="booking-section">
        <div class="tab-content">
            <div class="table-container">
                <table id="bookingTable" class="display" width="100%">
                    <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Service</th>
                        <th>Name</th>
                        <th>User Email</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="change-password hidden" id="change-password-section">

        <h2 style="text-align:center">Change your Password</h2>

        <form class="update-form" method="POST">

            <input type="hidden" name="action" value="updatePassword">

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password">
                <i class="bi bi-eye-slash" onclick="togglePassword('password',this)"></i>
                <span id="password-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password">
                <i class="bi bi-eye-slash" onclick="togglePassword('confirm-password',this)"></i>
                <span id="confirm-password-error" class="error-message"></span>
            </div>

            <button type="submit" class="update-btn">Change Password</button>

        </form>

    </div>

</section>

<?php include("./partials/bot.html") ?>
<?php include("./partials/footer.html") ?>
<?php include("./profile/scripts.html") ?>

<script src="./js/navigation.js"></script>
</body>
</html>
