document.addEventListener('DOMContentLoaded', function() {
    console.log('orders.js đã chạy'); // Kiểm tra file có chạy không

    const viewButtons = document.querySelectorAll('.view-details');
    console.log('Số nút "Xem chi tiết" tìm thấy:', viewButtons.length); // Kiểm tra số nút

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Nút được click:', button); // Kiểm tra khi click
            const orderId = button.getAttribute('data-order-id');
            fetchOrderDetails(orderId);
            const modal = document.getElementById('order-modal');
            if (modal) {
                console.log('Hiển thị modal');
                modal.style.display = 'block';
            } else {
                console.error('Không tìm thấy #order-modal');
            }
        });
    });
    //xem chi tiết đơn hàng
    function fetchOrderDetails(orderId) {
        console.log('Đang fetch chi tiết đơn hàng:', orderId);
        fetch(`../get_order_details.php?id=${orderId}`)
            .then(response => {
                console.log('Phản hồi từ server:', response.status);
                if (!response.ok) {
                    throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
                }
                return response.text();
            })
            .then(data => {
                document.getElementById('order-details').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('order-details').innerHTML = `Lỗi khi tải chi tiết đơn hàng: ${error.message}`;
                console.error('Error:', error);
            });
    }
});