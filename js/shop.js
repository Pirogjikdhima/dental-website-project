const gridView = document.querySelector('.grid-view');
const listView = document.querySelector('.list-view');
const productsSection = document.querySelector('.products-section');
const sortDropdown = document.querySelector('.sort-dropdown');


gridView.addEventListener('click', () => {
    productsSection.classList.remove('product-list');
    productsSection.classList.add('product-grid');
    Swal.fire('Grid View Enabled', 'Products are now displayed in grid view.', 'info');
});

listView.addEventListener('click', () => {
    productsSection.classList.remove('product-grid');
    productsSection.classList.add('product-list');
    Swal.fire('List View Enabled', 'Products are now displayed in list view.', 'info');
});
sortDropdown.addEventListener('change', (e) => {
    const products = Array.from(document.querySelectorAll('.product-container'));
    const container = document.querySelector('.products-section');

    for (let i = 0; i < products.length - 1; i++) {
        let minIndex = i;

        for (let j = i + 1; j < products.length; j++) {
            const priceA = extractPrice(products[minIndex]);
            const priceB = extractPrice(products[j]);
            const ratingA = extractRating(products[minIndex]);
            const ratingB = extractRating(products[j]);

            let comparisonResult;
            switch (e.target.value) {
                case 'price-low-high':
                    comparisonResult = priceA > priceB;
                    break;
                case 'price-high-low':
                    comparisonResult = priceA < priceB;
                    break;
                case 'rating':
                    comparisonResult = ratingA < ratingB;
                    break;
                default:
                    comparisonResult = false;
                    break;
            }

            if (comparisonResult) {
                minIndex = j;
            }
        }
        if (minIndex !== i) {
            [products[i], products[minIndex]] = [products[minIndex], products[i]];
        }
    }
    container.innerHTML = '';
    products.forEach(product => container.appendChild(product));
});


function extractPrice(product) {
    const priceText = product.querySelector('.price').textContent;
    const match = priceText.match(/\d+\.?\d*/);
    return match ? parseFloat(match[0]) : 0;
}

function extractRating(product) {
    const ratingText = product.querySelector('.rating span').textContent;
    return parseInt(ratingText) || 0;
}

window.addEventListener('load', () => {
    productsSection.classList.add('product-grid');
});

document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.add-to-cart-button');

    buttons.forEach(button => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();

            try {
                const productCard = event.target.closest('.product-container');
                const productId = productCard.getAttribute('data-product-id');

                if (!productId) {
                    throw new Error('Product ID not found');
                }

                const formData = new FormData();
                formData.append('productId', productId);

                const response = await fetch('http://localhost/DetyreKursi/shopping-cart.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Server error');
                }

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Success', 'Product added to cart successfully!', 'success');
                } else {
                    throw new Error(data.error || 'Failed to add product to cart');
                }
            } catch (error) {
                Swal.fire('Error', error.message || 'An error occurred. Please try again later.', 'error');
            }
        });
    });
});