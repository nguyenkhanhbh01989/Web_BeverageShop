document.addEventListener('DOMContentLoaded', function() {
    const placeOrderForm = document.querySelector('.place-order-form');
    if (placeOrderForm) {
        placeOrderForm.addEventListener('submit', function() {
            window.showToast('Processing order...');
            window.animateCart();
        });

        const successMessage = document.querySelector('.success-message');
        if (successMessage && successMessage.getAttribute('data-message')) {
            const message = successMessage.getAttribute('data-message');
            window.showToast(message);
            window.animateCart();
        }
    }
});