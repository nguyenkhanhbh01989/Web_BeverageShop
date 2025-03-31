//gộp lại các file css
// const gulp = require('gulp');
// const concat = require('gulp-concat');
// const cleanCSS = require('gulp-clean-css');

// gulp.task('css', function() {
//     return gulp.src('assets/css/*.css')
//         .pipe(concat('all.min.css'))
//         .pipe(cleanCSS())
//         .pipe(gulp.dest('assets/css/dist'));
// });


document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon');
    const toast = document.getElementById('toast');

    // index.php & product_detail.php: Thêm vào giỏ
    if (document.querySelector('.product-list') || document.querySelector('.product-detail')) {
        const addForms = document.querySelectorAll('.add-to-cart-form');
        addForms.forEach(form => {
            form.addEventListener('submit', function() {
                const productName = form.querySelector('input[name="product_name"]').value;
                showToast(`Added "${productName}" to cart!`);
                cartIcon.classList.add('active');
                setTimeout(() => cartIcon.classList.remove('active'), 500);
            });
        });

        const successMessage = document.querySelector('.success-message');
        if (successMessage && successMessage.getAttribute('data-product')) {
            const productName = successMessage.getAttribute('data-product');
            showToast(`Added "${productName}" to cart!`);
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        }
    }

    // cart.php: Cập nhật giỏ
    const cartForm = document.querySelector('.cart-form');
    if (cartForm) {
        cartForm.addEventListener('submit', function() {
            showToast('Cart updated!');
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        });
    }

    // cart.php: Xóa sản phẩm
    const removeLinks = document.querySelectorAll('.remove-item');
    removeLinks.forEach(link => {
        link.addEventListener('click', function() {
            const productName = link.getAttribute('data-product');
            showToast(`Removed "${productName}" from cart!`);
            cartIcon.classList.add('active');
            setTimeout(() => cartIcon.classList.remove('active'), 500);
        });
    });

    // checkout.php: Đặt hàng
    const placeOrderForm = document.querySelector('.place-order-form');
    if (placeOrderForm) {
        placeOrderForm.addEventListener('submit', function() {
            showToast('Processing order...');
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

    // order_history.php & admin/orders.php: Hủy đơn & Modal
    const cancelLinks = document.querySelectorAll('.cancel-order');
    cancelLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to cancel this order?')) {
                e.preventDefault();
            } else {
                showToast('Cancelling order...');
            }
        });
    });

    // login.php & register.php: Thông báo lỗi/thành công
    const authForm = document.querySelector('.auth-form');
    if (authForm) {
        authForm.addEventListener('submit', function() {
            showToast('Processing...');
        });

        const errorMessage = document.querySelector('.error-message');
        if (errorMessage && errorMessage.getAttribute('data-message')) {
            const message = errorMessage.getAttribute('data JUDGEessage');
            showToast(message);
        }

        const successMessage = document.querySelector('.success-message');
        if (successMessage && successMessage.getAttribute('data-message')) {
            const message = successMessage.getAttribute('data-message');
            showToast(message);
        }
    }

    // admin/orders.php: Cập nhật trạng thái
    const statusForms = document.querySelectorAll('.status-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function() {
            showToast('Đang cập nhật trạng thái...');
        });
    });

    // Modal cho order_history.php & admin/orders.php
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

    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    function fetchOrderDetails(orderId) {
        fetch(`get_order_details.php?id=${orderId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi mạng hoặc phản hồi không thành công');
                }
                return response.text();
            })
            .then(data => {
                orderDetails.innerHTML = data;
            })
            .catch(error => {
                orderDetails.innerHTML = 'Error loading order details!';
                console.error('Error:', error);
            });
    }

    // Hàm hiển thị toast
    function showToast(message) {
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
    }
});