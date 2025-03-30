document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar trên mobile
    const hamburger = document.getElementById('hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    }

    // Xem chi tiết người dùng
    const viewButtons = document.querySelectorAll('.view-details');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = button.getAttribute('data-user-id');
            fetchUserDetails(userId);
            document.getElementById('user-modal').style.display = 'block';
        });
    });

    // Chỉnh sửa người dùng
    const editButtons = document.querySelectorAll('.edit-user');
    console.log('Số nút Sửa tìm thấy:', editButtons.length); // Kiểm tra xem có nút nào không
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Nút Sửa được click, user_id:', button.getAttribute('data-user-id')); // Xác nhận click
            const userId = button.getAttribute('data-user-id');
            fetch(`../get_user_details.php?id=${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Lỗi HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    console.log('Dữ liệu trả về:', data); // Xác nhận dữ liệu
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const rows = doc.querySelectorAll('table tr');
                    const userData = {};
                    rows.forEach(row => {
                        const key = row.querySelector('th').textContent.trim();
                        const value = row.querySelector('td').textContent.trim();
                        userData[key] = value;
                    });
                    document.getElementById('edit-user-id').value = userId;
                    document.getElementById('edit-full-name').value = userData['Họ và Tên'] || '';
                    document.getElementById('edit-phone').value = userData['Số Điện Thoại'] || '';
                    document.getElementById('edit-address').value = userData['Địa Chỉ'] || '';
                    const roleMap = { 'customer': 2, 'staff': 3 };
                    document.getElementById('edit-role-id').value = roleMap[userData['Vai Trò'].toLowerCase()] || 2;
                    document.getElementById('edit-user-modal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Lỗi khi lấy thông tin người dùng:', error);
                    window.showToast('Lỗi khi tải thông tin người dùng!');
                });
        });
    });

    // Đóng modal
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const closeButton = modal.querySelector('.close-modal');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    function fetchUserDetails(userId) {
        fetch(`../get_user_details.php?id=${userId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Lỗi HTTP: ${response.status}`);
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