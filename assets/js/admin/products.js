document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar trên mobile
    const hamburger = document.getElementById('hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    }

    // Modal sửa sản phẩm
    const editButtons = document.querySelectorAll('.edit-product');
    const editModal = document.getElementById('edit-modal');
    const closeModal = document.querySelector(".close-modal");

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = button.getAttribute('data-product-id');
            const row = button.closest('tr');
            const name = row.cells[1].textContent;
            const price = row.cells[2].textContent.replace(/[^0-9]/g, '');
            const stock = row.cells[3].textContent;
            const category = row.cells[4].textContent;

            document.getElementById('edit-product-id').value = productId;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-stock').value = stock;
            document.getElementById('edit-category').value = category;

            editModal.style.display = 'block';
        });
    });

    // Đóng modal khi nhấn vào dấu (×)
    if (closeModal) {
        closeModal.addEventListener("click", function() {
            console.log("Đóng modal"); // Kiểm tra xem sự kiện có chạy không
            editModal.style.display = "none";
        });
    }

    // Đóng modal khi nhấn ra ngoài modal-content
    window.addEventListener("click", function(event) {
        if (event.target === editModal) {
            console.log("Nhấn ra ngoài modal, đóng modal"); // Kiểm tra sự kiện có hoạt động không
            editModal.style.display = "none";
        }
    });
});