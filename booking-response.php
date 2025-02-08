<?php
session_start();
header('Content-Type: text/html');
$status = $_GET['status'] ?? 'info';
$message = $_GET['message'] ?? 'No additional information available';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="5;url=home.php">
  <title>Booking Status</title>
  <link rel="stylesheet" href="./css/booking-response.css">
</head>
<body>

<div class="main-content">
  <div class="staff-container">
    <h2 class="staff-title">Booking Status</h2>
    <div class="response-container <?php echo htmlspecialchars($status); ?>">
      <h3 class="response-title">
          <?php if ($status === 'success') : ?>
            🎉 Success!
          <?php elseif ($status === 'error') : ?>
            ❌ Error!
          <?php else : ?>
            ℹ️ Information
          <?php endif; ?>
      </h3>
      <p class="response-message"><?php echo htmlspecialchars($message); ?></p>
    </div>
    <div class="button-container">
      <a href="home.php" class="action-button">Return to Home</a>
    </div>
  </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const responseContainer = document.querySelector('.response-container');

        responseContainer.style.transition = 'all 0.3s ease-in-out';
        responseContainer.style.transform = 'scale(1)';
    });
</script>
</body>
</html>