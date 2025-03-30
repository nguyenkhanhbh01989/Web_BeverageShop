document.addEventListener('DOMContentLoaded', function() {
    const cancelLinks = document.querySelectorAll('.cancel-order');
    cancelLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                e.preventDefault();
            } else {
                window.showToast('Đang hủy đơn hàng...');
            }
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
        fetch(`get_order_details.php?id=${orderId}`)
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