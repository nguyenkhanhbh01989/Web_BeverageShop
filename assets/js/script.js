// Hiển thị thông báo khi thêm vào giỏ hàng
document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon');
    const toast = document.getElementById('toast');

    // index.php: Thêm vào giỏ
    const addForms = document.querySelectorAll('.add-to-cart-form');
    addForms.forEach(form => {
        form.addEventListener('submit', function() {
            const productName = form.querySelector('input[name="product_name"]').value;
            showToast(`Đã thêm "${productName}" vào giỏ hàng!`);
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        });
    });

    // cart.php: Cập nhật giỏ
    const cartForm = document.querySelector('.cart-form');
    if (cartForm) {
        cartForm.addEventListener('submit', function() {
            showToast('Đã cập nhật giỏ hàng!');
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        });
    }

    // cart.php: Xóa sản phẩm
    const removeLinks = document.querySelectorAll('.remove-item');
    removeLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const productName = link.getAttribute('data-product');
            showToast(`Đã xóa "${productName}" khỏi giỏ hàng!`);
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        });
    });

    // Hàm hiển thị toast
    function showToast(message) {
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    }
});