document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar trên mobile
    const hamburger = document.getElementById('hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    }

    // Admin/orders.php: Cập nhật trạng thái
    const statusForms = document.querySelectorAll('.status-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function() {
            window.showToast('Đang cập nhật trạng thái...');
        });
    });

    const viewButtons = document.querySelectorAll('.view-details');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = button.getAttribute('data-order-id');
            fetchOrderDetails(orderId);
            document.getElementById('order-modal').style.display = 'block';
        });
    });

    function fetchOrderDetails(orderId) {
        fetch(`../get_order_details.php?id=${orderId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi mạng hoặc phản hồi không thành công: ' + response.status);
                }
                return response.text();
            })
            .then(data => {
                document.getElementById('order-details').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('order-details').innerHTML = 'Lỗi khi tải chi tiết đơn hàng!';
                console.error('Error:', error);
            });
    }
});