const reviewContainer = document.getElementById('review-card-container');

async function fetchReviews() {
    try {
        const response = await fetch('./get-reviews.php');
        const reviews = await response.json();

        if (reviews.error) {
            console.error(reviews.error);
            return;
        }

        reviewContainer.style.opacity = 0;

        setTimeout(() => {
            reviewContainer.innerHTML = '';

            reviewContainer.innerHTML = reviews.map(review => `
                <div class="review-card">
                  <div class="review-rating">Rating: ⭐️ ${review.rating}/5</div>
                  <p>${review.comment}</p>
                  <div class="review-footer">
                    <strong class="review-user">${review.user}</strong>
                    <span class="review-date">${new Date(review.created_at).toLocaleDateString()}</span>
                  </div>
                </div>
            `).join('');

            reviewContainer.style.opacity = 1;
        }, 500);
    } catch (error) {
        console.error('Error fetching reviews:', error);
    }
}

fetchReviews();
setInterval(fetchReviews, 20000);
