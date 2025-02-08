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
    <title>User Profile</title>
    <?php include("./profile/styles.html"); ?>
    <style>
      table.dataTable tbody td,
      table.dataTable thead th {
        text-align: left;
        vertical-align: middle;
      }
    </style>
</head>
<body>

<?php include("partials/navigation.php") ?>

<section id="main-section">

    <div class="profile-card">

        <?php echo "<h2>" . htmlspecialchars($name) . " " . htmlspecialchars($surname) . "</h2>"; ?>

        <div class="profile-picture">
            <img src="<?php echo empty($photo_path) ? 'images/profile/default.jpg' : $photo_path; ?>" alt="profile-photo">
        </div>

        <div class="profile-info">

                <span id="info-btn">
                    <img src="images/profile/dashboard/info.png" alt="info">
                    Personal Information
                </span>

            <span id="appointments-btn">
                    <img src="images/profile/dashboard/dashboard.png" alt="appointments">
                    Upcoming Appointments
                </span>

            <span id="schedule-btn">
                    <img src="images/profile/dashboard/schedule.png" alt="schedule">
                    Schedule
                </span>

            <span id="completed-btn">
                    <img src="images/profile/dashboard/appointments.png" alt="appointments">
                    Completed Appointments
                </span>

            <span id="payments-btn">
                    <img src="images/profile/dashboard/payments.png" alt="payments">
                    My Payments
                </span>

            <span id="password-btn">
                    <img src="images/profile/dashboard/password.png" alt="change password">
                    Change Password
                </span>

            <span id="logout-btn">
                    <img src="images/profile/dashboard/logout.png" alt="logout">
                    Logout
                </span>

            <span id="delete-btn">
                    <img src="images/profile/dashboard/delete.png" alt="delete">
                    Delete Account
                </span>

        </div>

    </div>

    <input type="hidden" id="user-role" value="<?php echo $_SESSION['role']; ?>">

    <div class="welcome-message" id="welcome-section">

        <h2 style="text-align: center">Welcome to Your Profile!</h2>

        <p>Here are some tips to navigate your profile:</p>

        <ul>
            <li><strong>Personal Information:</strong> Update your personal details, such as name, email, and medical condition.</li>
            <li><strong>Upcoming Appointments:</strong> View your upcoming appointments and manage them.</li>
            <li><strong>Schedule:</strong> Book a new appointment with your preferred doctor.</li>
            <li><strong>Completed Appointments:</strong> View your past appointments.</li>
            <li><strong>My Payments:</strong> View and manage your payment history.</li>
            <li><strong>Change Password:</strong> Update your account password for security.</li>
            <li><strong>Logout:</strong> Log out of your account.</li>
            <li><strong>Delete Account:</strong> If you want to delete your account, you can do so here.</li>
        </ul>

    </div>

    <div class="personal-info hidden" id="info-section">

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

    <div class="appointments hidden" id="appointments-section"></div>

    <div class="completed-appointments hidden" id="completed-appointments-section"></div>

    <div class="payments hidden" id="payments-section">
        <div class="data-table-container">
            <table id="usersTable" class="display" width="100%">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Time</th>
                </tr>
                </thead>
            </table>
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
<script src="./js/appointments.js"></script>
<script src="./js/navigation.js"></script>

</body>
</html>
