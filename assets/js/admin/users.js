document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar trên mobile
    const hamburger = document.getElementById('hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    }

    // Cập nhật vai trò người dùng
    const roleForms = document.querySelectorAll('.role-form');
    roleForms.forEach(form => {
        form.addEventListener('submit', function() {
            window.showToast('Đang cập nhật vai trò...');
        });
    });

    // Xử lý sự kiện "Xem chi tiết" bằng event delegation
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('view-details')) {
            const userId = event.target.getAttribute('data-user-id');
            fetchUserDetails(userId);
            document.getElementById('user-modal').style.display = 'block';
        }
    });

    // Đóng modal khi nhấn vào nút đóng hoặc bên ngoài modal
    const modal = document.getElementById('user-modal');
    const closeModal = document.querySelector('.close-modal');

    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Hàm lấy chi tiết người dùng từ server
    function fetchUserDetails(userId) {
        fetch(`../get_user_details.php?id=${userId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
                }
                return response.text();
            })
            .then(data => {
                document.getElementById('user-details').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('user-details').innerHTML = `Lỗi khi tải chi tiết người dùng: ${error.message}`;
                console.error('Error:', error);
            });
    }
});