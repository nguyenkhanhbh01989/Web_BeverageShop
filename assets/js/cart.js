document.addEventListener('DOMContentLoaded', function() {
    const cartForm = document.querySelector('.cart-form');
    if (cartForm) {
        cartForm.addEventListener('submit', function() {
            window.showToast('Đã cập nhật giỏ hàng!');
            window.animateCart();
        });
    }

    const removeLinks = document.querySelectorAll('.remove-item');
    removeLinks.forEach(link => {
        link.addEventListener('click', function() {
            const productName = link.getAttribute('data-product');
            window.showToast(`Đã xóa "${productName}" khỏi giỏ hàng!`);
            window.animateCart();
        });
    });
});