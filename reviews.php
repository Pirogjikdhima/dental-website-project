<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./images/crystal-dental-logo.png" type="image/x-icon">
    <title>Testimonials with Review Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">
    <script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
    <link rel="stylesheet" href="./css/reviews.css">
    <link rel="stylesheet" href="./css/header&footer.css"/>
    <link rel="stylesheet" href="./css/bot.css">
</head>
<body>

<?php include("./partials/navigation.php") ?>

<div class="testimonials-container">
    <h2 style="text-align: center; color: #4a3b7c; margin-bottom: 2rem;">Client Testimonials</h2>
    <div id="testimonials">
    </div>
</div>

<div class="leave-review-card" id="leave-review-card"
    <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'USER') echo "style='display:none;'" ?>
>
    <span>✎</span> Leave Your Review
</div>

<div class="new-review-form" id="review-form" style="display:none;">
    <h3 style="color: #4a3b7c;">Share Your Thoughts</h3>
    <p class="error-message" id="error-message" style="display: none;">All fields are required!</p>

    <form id="review-form-submit" method="POST">
        <input type="hidden" name="action" value="submit_review"/>
        <select id="stars" name="rating" required>
            <option value="" disabled selected>Choose a rating</option>
            <option value="1">★☆☆☆☆</option>
            <option value="2">★★☆☆☆</option>
            <option value="3">★★★☆☆</option>
            <option value="4">★★★★☆</option>
            <option value="5">★★★★★</option>
        </select>
        <textarea id="message" name="message" rows="4" placeholder="Your Review" required></textarea>
        <button type="submit" id="submit-review">Submit Review</button>
    </form>
</div>

<?php include("./partials/bot.html") ?>
<?php include("./partials/footer.html") ?>
<script src="js/bot.js"></script>

<?php
//$isLoggedOut = isset($_COOKIE['logged_out']) && $_COOKIE['logged_out'] === "true" ? 'true' : 'false';
//?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    //const isLoggedOut = <?php //echo $isLoggedOut; ?>//;

    function fetchReviews() {
        $.ajax({
            url: 'http://localhost/DetyreKursi/reviewsGetAll.php',
            method: 'GET',
            success: function (data) {
                if (data.success) {
                    displayReviews(data.reviews);
                }
            }
        });
    }

    function displayReviews(reviews) {
        const testimonialsContainer = document.getElementById('testimonials');
        testimonialsContainer.innerHTML = '';
        reviews.forEach(function (review) {
            const testimonialDiv = document.createElement('div');
            testimonialDiv.className = 'testimonial';
            testimonialDiv.innerHTML = `
                <div class="name">${review.first_name} ${review.last_name}</div>
                <div class="stars">${'★'.repeat(review.rating) + '☆'.repeat(5 - review.rating)}</div>
                <div class="message">${review.comment}</div>
                <div class="timestamp">${new Date(review.created_at).toLocaleString()}</div>
            `;
            testimonialsContainer.appendChild(testimonialDiv);
        });
    }

    document.getElementById('leave-review-card').addEventListener('click', function () {
        const reviewForm = document.getElementById('review-form');
        // const existingError = document.getElementById('error-message');
        //
        // if (existingError) {
        //     existingError.remove();
        // }

        // if (isLoggedOut) {
        //     const errorMessage = document.createElement('p');
        //     errorMessage.id = 'error-message';
        //     errorMessage.textContent = "You must be logged in to submit a review.";
        //     document.body.insertBefore(errorMessage, document.getElementById('leave-review-card').nextSibling);
        //     return;
        // }
        reviewForm.style.display = reviewForm.style.display === 'block' ? 'none' : 'block';
        if (reviewForm.style.display === 'block') {
            reviewForm.scrollIntoView({behavior: 'smooth'});
        }
    });
    document.getElementById('review-form-submit').addEventListener('submit', function (event) {
        event.preventDefault();

        const rating = document.getElementById('stars').value;
        const comment = document.getElementById('message').value.trim();
        const errorMessage = document.getElementById('error-message');
        const reviewForm = document.getElementById('review-form');

        if (!rating || !comment) {
            if (!errorMessage) {
                const errorElement = document.createElement('p');
                errorElement.id = 'error-message';
                errorElement.textContent = 'Please fill in all the fields.';
                reviewForm.appendChild(errorElement);
            }
        } else {
            if (errorMessage) errorMessage.remove();
            $.ajax({
                url: 'http://localhost/DetyreKursi/connection.php',
                method: 'POST',
                data: {
                    action: 'submit_review',
                    rating: rating,
                    comment: comment,
                },
                success: function (data) {
                    if (data.success) {
                        fetchReviews();
                        document.getElementById('review-form-submit').reset();
                        reviewForm.style.display = 'none';
                        iziToast.success({
                            title: 'Success',
                            message: data.message,
                            position: 'topCenter',
                            timeout: 3000,
                            backgroundColor: '#7066e0',
                            titleColor: '#FFFFFF',
                            messageColor: '#FFFFFF',
                            pauseOnHover: false,
                        });
                    } else {
                        iziToast.error({
                            title: 'Error',
                            message: data.message,
                            position: 'topCenter',
                            timeout: 3000,
                            backgroundColor: '#E74C3C',
                            titleColor: '#FFFFFF',
                            messageColor: '#FFFFFF',
                            pauseOnHover: false,
                        });
                    }
                }
            });
        }
    });
    fetchReviews();
</script>
<script src="js/activityTracker.js"></script>
<script src="./js/navigation.js"></script>
</body>
</html>
