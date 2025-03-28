document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon');
    const toast = document.getElementById('toast');

    // index.php & product_detail.php: Thêm vào giỏ
    if (document.querySelector('.product-list') || document.querySelector('.product-detail')) {
        const addForms = document.querySelectorAll('.add-to-cart-form');
        addForms.forEach(form => {
            form.addEventListener('submit', function() {
                const productName = form.querySelector('input[name="product_name"]').value;
                showToast(`Đã thêm "${productName}" vào giỏ hàng!`);
                cartIcon.classList.add('active');
                setTimeout(() => cartIcon.classList.remove('active'), 500);
            });
        });

        const successMessage = document.querySelector('.success-message');
        if (successMessage && successMessage.getAttribute('data-product')) {
            const productName = successMessage.getAttribute('data-product');
            showToast(`Đã thêm "${productName}" vào giỏ hàng!`);
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        }
    }

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
        link.addEventListener('click', function() {
            const productName = link.getAttribute('data-product');
            showToast(`Đã xóa "${productName}" khỏi giỏ hàng!`);
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        });
    });

    // checkout.php: Đặt hàng
    const placeOrderForm = document.querySelector('.place-order-form');
    if (placeOrderForm) {
        placeOrderForm.addEventListener('submit', function() {
            showToast('Đang xử lý đơn hàng...');
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        });

        const successMessage = document.querySelector('.success-message');
        if (successMessage && successMessage.getAttribute('data-message')) {
            const message = successMessage.getAttribute('data-message');
            showToast(message);
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        }
    }

    // order_history.php: Hủy đơn & Modal
    const cancelLinks = document.querySelectorAll('.cancel-order');
    cancelLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                e.preventDefault();
            } else {
                showToast('Đang hủy đơn hàng...');
            }
        });
    });

    const successMessage = document.querySelector('.success-message');
    if (successMessage && successMessage.getAttribute('data-message')) {
        const message = successMessage.getAttribute('data-message');
        showToast(message);
    }

    const modal = document.getElementById('order-modal');
    const closeModal = document.querySelector('.close-modal');
    const orderDetails = document.getElementById('order-details');
    const viewButtons = document.querySelectorAll('.view-details');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = button.getAttribute('data-order-id');
            fetchOrderDetails(orderId);
            modal.style.display = 'block';
        });
    });

    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    function fetchOrderDetails(orderId) {
        // Giả lập dữ liệu chi tiết (thay bằng Ajax nếu cần)
        const dummyDetails = `
            <table>
                <thead>
                    <tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th></tr>
                </thead>
                <tbody>
                    <tr><td>Trà Sữa Trân Châu</td><td>2</td><td>60,000 VND</td></tr>
                    <tr><td>Cà Phê Sữa</td><td>1</td><td>35,000 VND</td></tr>
                </tbody>
            </table>
            <p><strong>Tổng cộng: 95,000 VND</strong></p>
        `;
        orderDetails.innerHTML = dummyDetails; // Thay bằng Ajax thực tế
    }

    // Hàm hiển thị toast
    function showToast(message) {
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    }
});
// Hiện tại dùng dữ liệu giả trong fetchOrderDetails.Nếu muốn lấy dữ
//  liệu thực từ database, cần tạo file PHP(ví dụ: get_order_details.php) và dùng Ajax:

function fetchOrderDetails(orderId) {
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(data => orderDetails.innerHTML = data);
}