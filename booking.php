<?php
session_start();
$conn = require "database.php";

$query_services = "SELECT service_id, service_name, icon_url, description, price FROM services";
$service_result = mysqli_query($conn, $query_services);

$query_doctors = "SELECT doctor_id, name FROM doctors";
$doctor_result = mysqli_query($conn, $query_doctors);

$selected_service_name = isset($_GET['service']) ? urldecode($_GET['service']) : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Crystal Dental | Booking</title>
  <link rel="stylesheet" href="./css/booking.css">
  <link rel="stylesheet" href="./css/bot.css">
  <link rel="stylesheet" href="./css/header&footer.css">
</head>
<body>

<?php include("./partials/navigation.php"); ?>

<header id="form-header">
  <h1>Book an Appointment</h1>
  <p>Plan your visit with ease. Fill in the form below to reserve your time slot and the service you need.</p>
</header>

<section id="form-container">
  <div id="service-description">
    <h2></h2>
    <p id="service-description-text"></p>
    <img id="service-icon" src="" alt=""/>
  </div>

  <form action="./handleBooking.php" method="post">
    <label for="service">Service:</label>
    <select id="service" name="service" required onchange="updateServiceDetails()">
      <option value="" disabled selected>Select a Service</option>
        <?php if (mysqli_num_rows($service_result) > 0): ?>
            <?php while ($service = mysqli_fetch_assoc($service_result)): ?>
            <option value="<?php echo $service['service_id']; ?>"
                <?php echo ($selected_service_name == $service['service_name']) ? 'selected' : ''; ?>
                    data-icon="<?php echo $service['icon_url']; ?>"
                    data-description="<?php echo htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-price="<?php echo $service['price']; ?>">
                <?php echo htmlspecialchars($service['service_name'], ENT_QUOTES, 'UTF-8'); ?>
            </option>
            <?php endwhile; ?>
        <?php else: ?>
          <option disabled>No services available</option>
        <?php endif; ?>
    </select>

    <label for="doctor_id">Available Doctor:</label>
      <?php if (mysqli_num_rows($doctor_result) > 0): ?>
        <select id="doctor_id" name="doctor_id" required>
          <option value="" disabled selected>Select a Doctor</option>
            <?php while ($doctor = mysqli_fetch_assoc($doctor_result)): ?>
              <option value="<?php echo $doctor['doctor_id']; ?>">
                  <?php echo htmlspecialchars($doctor['name'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endwhile; ?>
        </select>
      <?php else: ?>
        <p style="color: red;">No doctors are available at the moment. Please try again later.</p>
        <script>
            document.querySelector("form button[type='submit']").disabled = true;
        </script>
      <?php endif; ?>

    <label for="date">Available Date:</label>
    <select id="date" name="date" required>
      <option value="" disabled selected>Select a Date</option>
    </select>

    <label for="time">Available Time:</label>
    <select id="time" name="time" required>
      <option value="" disabled selected>Select a Time</option>
    </select>

    <label for="additional-info">Additional Info:</label>
    <textarea id="additional-info" name="description"></textarea>

    <?php if (isset($_SESSION["user_id"])): ?>
    <button type="submit">Book Now</button>
    <?php else: ?>
    <button type="submit" disabled class="button-disabled">Book Now</button>
    <p style="color: gray;">Please login to book an appointment.</p>
    <?php endif; ?>
  </form>
</section>

<?php include("./partials/bot.html") ?>
<?php include("./partials/footer.html"); ?>

<script src="js/bot.js"></script>
<script src="js/booking.js"></script>
<script src="js/activityTracker.js"></script>
<script src="./js/navigation.js"></script>
</body>
</html>
