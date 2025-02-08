<?php
session_start();
$conn = require "database.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    if (isset($_GET['doctor_id'])) {
        $doctorIdToDelete = intval($_GET['doctor_id']);

        $deleteQuery = "DELETE FROM doctors WHERE doctor_id = $doctorIdToDelete";

        if ($conn->query($deleteQuery) === TRUE) {
            echo "<p style='color: green;'>Doctor deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting doctor: " . $conn->error . "</p>";
        }

        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctorId = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $about = mysqli_real_escape_string($conn, $_POST['about']);

    $photoUrl = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $photoUrl = $targetDir . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photoUrl);
    }

    if ($doctorId) {
        $updateQuery = "UPDATE doctors SET 
                        name = '$name', 
                        specialization = '$specialization', 
                        years_of_experience = '$experience', 
                        about = '$about'";

        if ($photoUrl) {
            $updateQuery .= ", photo_url = '$photoUrl'";
        }

        $updateQuery .= " WHERE doctor_id = $doctorId";

        if ($conn->query($updateQuery) === TRUE) {
            echo "<p style='color: green;'>Doctor details updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        }
    } else {
        $sql = "INSERT INTO doctors (name, specialization, years_of_experience, about, photo_url)
                VALUES ('$name', '$specialization', '$experience', '$about', '$photoUrl')";

        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>New dentist added successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        }
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="./images/crystal-dental-logo.png" type="image/x-icon">
  <title>Our Dentists</title>
  <link rel="stylesheet" href="./css/staff.css">
  <link rel="stylesheet" href="./css/header&footer.css"/>
  <link rel="stylesheet" href="./css/bot.css">
</head>
<body>

<?php include("./partials/navigation.php") ?>

<div class="main-content">
  <div class="staff-container">
    <h2 class="staff-title">Meet Our Dentists</h2>
    <div class="staff-grid">
        <?php
        $sql = "SELECT doctor_id, name, specialization, about, photo_url, years_of_experience FROM doctors WHERE specialization = 'Dentist'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                    <div class="staff-card">
                        <div class="details">
                            <img src="' . $row['photo_url'] . '" alt="Photo of ' . $row['name'] . '">
                            <h3>' . $row['name'] . '</h3>
                            <p>Specialization: ' . $row['specialization'] . '</p>
                            <p>Years of Experience: ' . $row['years_of_experience'] . '</p>
                        </div>
                        <div class="description">
                            <p>' . nl2br($row['about']) . '</p>
                            <div class="action-buttons">';

                if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'ADMIN') {
                    echo '
                              <button class="edit-btn" data-doctor-id="' . $row['doctor_id'] . '"
                                      data-name="' . htmlspecialchars($row['name']) . '"
                                      data-specialization="' . htmlspecialchars($row['specialization']) . '"
                                      data-experience="' . htmlspecialchars($row['years_of_experience']) . '"
                                      data-about="' . htmlspecialchars($row['about']) . '"
                                      data-photo="' . htmlspecialchars($row['photo_url']) . '">Edit</button>
                              <button class="delete-btn" data-doctor-id="' . $row['doctor_id'] . '">Delete</button>';
                }

                echo '
                            </div>
                        </div>
                    </div>';
            }
        } else {
            echo '<p>No dentists found in the database!</p>';
        }
        $conn->close();
        ?>
    </div>
  </div>
</div>

<div style="text-align: center; margin-top: 2rem;">
  <button
      id="add-doctor-btn" <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') echo "style='display:none;'" ?>>
    Add a New Dentist
  </button>
</div>

<div id="add-doctor-form">
  <form action="" method="POST" enctype="multipart/form-data">
    <h2>Add a New Dentist</h2>

    <input type="hidden" id="doctor_id" name="doctor_id">

    <input type="text" name="name" placeholder="Name" required>

    <input type="text" name="specialization" value="Dentist" readonly>

    <input type="number" name="experience" placeholder="Years of Experience" required>

    <textarea name="about" placeholder="About the Dentist" required></textarea>

    <input type="file" name="photo" accept="image/*">

    <button type="submit">Save</button>
  </form>
</div>

<?php include("./partials/footer.html") ?>
<?php include("./partials/bot.html") ?>

<script src="./js/staff.js"></script>
<script src="js/bot.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="./js/navigation.js"></script>
<script src="js/activityTracker.js"></script>
</body>
</html>