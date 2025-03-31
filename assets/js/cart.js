document.addEventListener('DOMContentLoaded', function() {
    const cartForm = document.querySelector('.cart-form');
    if (cartForm) {
        cartForm.addEventListener('submit', function() {
            window.showToast('Cart updated!');
            window.animateCart();
        });
    }

    const removeLinks = document.querySelectorAll('.remove-item');
    removeLinks.forEach(link => {
        link.addEventListener('click', function() {
            const productName = link.getAttribute('data-product');
            window.showToast(`Removed "${productName}" from cart!`);
            window.animateCart();
        });
    });
});