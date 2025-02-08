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
    <title>Services</title>
    <link rel="stylesheet" href="./css/services.css">
    <link rel="stylesheet" href="./css/header&footer.css"/>
    <link rel="stylesheet" href="./css/bot.css"/>
</head>
<body>

<?php include("./partials/navigation.php") ?>

<section id="what-to-expect">
    <div class="content">
        <div class="image">
            <img src="images/team.jpg" alt="Team of dentists" class="team-image">
        </div>
        <div class="text">
            <h2>What to Expect</h2>
            <p>
                We specialize in addressing issues related to teeth, jaws, bones, and facial structures that affect your
                ability
                to eat, speak, and breathe comfortably. Your first visit may include a review of your oral health
                history,
                diagnostic imaging, and a discussion about your goals and expectations.
            </p>
            <p>
                Our team consists of certified technicians dedicated to diagnosing and treating a wide range of oral and
                facial
                conditions.
            </p>
        </div>
    </div>
</section>
<main>
    <section id="services">
        <h2>Our Services</h2>
        <div class="services-container">
            <?php
            try {
                $query = "SELECT * FROM services";
                $result = $conn->query($query);

                if (!$result) {
                    throw new Exception("Error fetching services: " . $conn->error);
                }

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<a href="./booking.php?service=' . urlencode($row['service_name']) . '">';
                        echo '<div class="service-item">';
                        echo '<h3>';
                        echo '<img src="' . htmlspecialchars($row['icon_url'], ENT_QUOTES) . '" alt="' . htmlspecialchars($row['service_name'], ENT_QUOTES) . '" style="width: 50px; height: auto;">';
                        echo htmlspecialchars($row['service_name'], ENT_QUOTES);
                        echo '</h3>';
                        echo '<p>' . htmlspecialchars($row['description'], ENT_QUOTES) . '</p>';
                        echo '</div>';
                        echo '</a>';
                    }
                } else {
                    echo '<p>No services available at the moment.</p>';
                }
            } catch (Exception $e) {
                echo '<p>Error loading services: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
    </section>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'ADMIN'): ?>
        <div class="admin-section">
            <button class="admin-button" onclick="window.location.href='editservices.php';">Edit Services</button>
        </div>
    <?php endif; ?>


    <section id="insurance">
        <h2>
            <picture>
                <source srcset="https://cdn-icons-png.flaticon.com/512/2347/2347686.png" type="image/png">
                <img src="https://cdn-icons-png.flaticon.com/512/2347/2347686.png" alt="Insurance Icon"
                     style="width: 50px; height: auto;">
            </picture>
            Insurance
        </h2>
        <p>
            At Crystal Dental, we understand that your smile is one of your most valuable assets. Our dental insurance
            plans
            are designed to help you maintain optimal oral health while staying within your budget.
        </p>
    </section>

    <?php include("./partials/bot.html") ?>
    <?php include("./partials/footer.html") ?>
</main>
<script src="js/bot.js"></script>
<script src="js/activityTracker.js"></script>
<script src="./js/navigation.js"></script>
</body>
</html>
