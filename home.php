<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" href="./images/crystal-dental-logo.png" type="image/x-icon">
    <title>Crystal Dental</title>
    <link rel="stylesheet" href="./css/main.css"/>
    <link rel="stylesheet" href="./css/animations.css">
    <link rel="stylesheet" href="./css/header&footer.css"/>
    <link rel="stylesheet" href="./css/bot.css"/>
    <style>
      .chat-widget {
        right: -195px;
      }
    </style>
</head>
<body>
<section id="main-section">
    <?php include("./partials/navigation.php") ?>

    <div id="hero" class="section">
        <div id="hero-heading">
            <h1>Crystal Dental</h1>
            <p>For the smile, you love</p>
            <a href="./services.php#services">Book now</a>
        </div>

        <img src="./images/purple-tooth.png" alt="purple tooth logo"/>
    </div>
</section>

<section id="about-us-section" class="section">
    <h2>About Us</h2>
    <div id="about-us">
        <img src="./images/about-us.jpg" alt="Doctor Team"/>

        <p>
            At Crystal Dental, we are committed to delivering exceptional dental care with precision, compassion, and
            professionalism. Our modern clinic
            is designed to provide a comfortable, stress-free experience for patients of all ages. Equipped with
            state-of-the-art technology and guided by
            the latest advancements in dental science, we strive to ensure every visit leaves you with a healthier, more
            confident smile.
        </p>
    </div>

    <a href="./staff.php">Our Staff</a>
</section>

<section id="review-section" class="section">
    <h2>Check out our customer reviews</h2>
    <div id="review-card-container">

    </div>

    <div id="review-buttons">
        <a href="./services.php#services">Book now</a>
        <a href="./reviews.php">Reviews</a>
    </div>
</section>

<section id="quote-section" class="section">
    <h2>What our doctors say</h2>

    <div id="quote-card">
        <p>
            "At Crystal Dental, we believe that a healthy smile is more than just appearance — it's a reflection of
            overall
            well-being and confidence.
            That's why we approach every patient with compassion, precision, and a personalized touch. Our goal is to
            create a
            space where you feel heard,
            valued, and cared for at every step of your dental journey. Whether it's a routine checkup or a
            life-changing
            restoration, we're here to
            ensure you leave with a smile that's healthier, brighter, and stronger than ever before."
            <br/>
            — Dr. Emily Hartman, Lead Dentist at Crystal Dental
        </p>

        <img src="./images/dentist.png" alt="dentist photo"/>
    </div>
</section>

<section id="location-section" class="section">
    <h2>Our Clinic</h2>
    <div>
        <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4832.834424007673!2d19.821553801438526!3d41.316947312847546!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDEuMzE2OTQ3LCAxOS44MjE1NTM!5e0!3m2!1sen!2sus!4v1602593287528!5m2!1sen!2sus"
                width="70%"
                height="400"
                style="border: 0"
                allowfullscreen=""
                loading="lazy"
        >
        </iframe>
    </div>
</section>

<?php include("./partials/bot.html") ?>
<?php include("./partials/footer.html") ?>

<script src="./js/animations.js"></script>
<script src="./js/activityTracker.js"></script>
<script src="./js/reviews.js"></script>
<script src="./js/bot.js"></script>
<script src="./js/navigation.js"></script>

</body>
</html>
