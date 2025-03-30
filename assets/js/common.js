document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon');
    const toast = document.getElementById('toast');

    // Hiệu ứng cart icon khi có hành động
    if (cartIcon) {
        function animateCart() {
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        }
        window.animateCart = animateCart; // Expose để các file khác gọi
    }

    // Modal chi tiết đơn hàng
    const modal = document.getElementById('order-modal');
    const closeModal = document.querySelector('.close-modal');
    const orderDetails = document.getElementById('order-details');

    if (modal && closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Hàm hiển thị toast
    function showToast(message) {
        if (toast) {
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2000);
        }
    }
    window.showToast = showToast; // Expose để các file khác gọi
});