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
    console.log('Number of Edit buttons found:', editButtons.length); // Kiểm tra xem có nút nào không
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Edit button clicked, user_id:', button.getAttribute('data-user-id')); // Xác nhận click
            const userId = button.getAttribute('data-user-id');
            fetch(`../get_user_details.php?id=${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    console.log('Data returned:', data); // Xác nhận dữ liệu
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
                    document.getElementById('edit-full-name').value = userData['Full Name'] || ''; // Sửa từ 'Full NameName' thành 'Full Name'
                    document.getElementById('edit-phone').value = userData['Phone Number'] || ''; // Sửa từ 'Phone' thành 'Phone Number'
                    document.getElementById('edit-address').value = userData['Address'] || ''; // Sửa từ 'Địa Chỉ' thành 'Address'
                    const roleMap = { 'customer': 2, 'staff': 3, 'admin': 1 }; // Thêm 'admin': 1
                    const roleName = userData['Role'] ? userData['Role'].toLowerCase() : 'customer'; // Sửa từ 'Vai Trò' thành 'Role', thêm kiểm tra undefined
                    document.getElementById('edit-role-id').value = roleMap[roleName] || 2;
                    document.getElementById('edit-user-modal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching user info:', error);
                    window.showToast('Error loading user information!');
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
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.text();
            })
            .then(data => {
                document.getElementById('user-details').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('user-details').innerHTML = `Error loading user details: ${error.message}`;
                console.error('Error:', error);
            });
    }
});