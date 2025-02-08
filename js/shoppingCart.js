document.querySelector('#checkout-button').addEventListener('click', function () {
    Swal.fire({
        title: 'Enter Card Information',
        html: `
            <div id="swal-stripe-container">
                <div id="stripe-sweetalert-card-element"></div>
                <div id="stripe-sweetalert-card-errors" class="card-errors" role="alert" style="color: red; margin-top: 10px;"></div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Payment',
        cancelButtonText: 'Cancel',
        didOpen: () => {
            const stripe = Stripe("pk_test_51QdZaOIA6j8AgjdoONN2YmHKTojcogE82ZcF8ntm0l1YwdZNUKNnlDgxb62vZ7IBVbS1NfyGQoRNxjWn6o0bvJxE00alHhiENc");
            const elements = stripe.elements();

            const card = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a',
                    },
                },
            });

            card.mount('#stripe-sweetalert-card-element');

            card.on('change', (event) => {
                const displayError = document.getElementById('stripe-sweetalert-card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            Swal.cardElement = card;
            Swal.stripe = stripe;
        },
        preConfirm: () => {
            const card = Swal.cardElement;
            const stripe = Swal.stripe;

            if (!card || !stripe) {
                Swal.showValidationMessage("Stripe Element is not properly initialized.");
                return;
            }

            const formData = new FormData();
            formData.append('action', 'checkout');

            return fetch('', {
                method: 'POST',
                body: formData
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.error || 'Failed to create PaymentIntent. No success message received.');
                    }

                    return stripe.confirmCardPayment(data.client_secret, {
                        payment_method: {
                            card: card,
                            billing_details: {
                                name: 'Customer Name',
                            },
                        },
                    });
                })
                .then((result) => {
                    if (result.error) {
                        throw new Error(result.error.message);
                    }
                    return result;
                })
                .catch((error) => {
                    Swal.showValidationMessage(`Payment failed: ${error.message}`);
                });
        },
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Payment Succeeded',
                'Your payment has been completed successfully.',
                'success'
            ).then(() => {
                location.reload();
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire(
                'Payment Cancelled',
                'You have cancelled the payment.',
                'info'
            );
        }
    });
});

document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function () {
        const cartId = this.dataset.cartId;

        Swal.fire({
            title: 'Are you sure?',
            text: 'This item will be removed from your cart.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('cartId', cartId);

                fetch('remove_from_cart.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Removed!', 'The item has been removed.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', 'Failed to remove the item.', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'An unexpected error occurred.', 'error');
                    });
            }
        });
    });
});