// Hiển thị thông báo khi thêm vào giỏ hàng
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.add-to-cart-form');
    const toast = document.getElementById('toast');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const productName = form.querySelector('input[name="product_name"]').value;
            toast.textContent = `Đã thêm "${productName}" vào giỏ hàng!`;
            toast.classList.add('show');

            // Ẩn thông báo sau 2 giây
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        });
    });

    // Nếu có thông báo từ PHP (trang tải lại), hiển thị ngay
    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
        const productName = successMessage.getAttribute('data-product');
        toast.textContent = `Đã thêm "${productName}" vào giỏ hàng!`;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2000);
    }
});