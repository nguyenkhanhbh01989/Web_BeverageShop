document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.product-list') || document.querySelector('.product-detail')) {
        const addForms = document.querySelectorAll('.add-to-cart-form');
        addForms.forEach(form => {
            form.addEventListener('submit', function() {
                const productName = form.querySelector('input[name="product_name"]').value;
                window.showToast(`Added "${productName}" to cart!`);
                window.animateCart();
            });
        });

        const successMessage = document.querySelector('.success-message');
        if (successMessage && successMessage.getAttribute('data-product')) {
            const productName = successMessage.getAttribute('data-product');
            window.showToast(`Added "${productName}" to cart!`);
            window.animateCart();
        }
    }
});