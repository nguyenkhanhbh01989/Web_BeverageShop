<?php
$host = 'localhost';        // Máy chủ MySQL (localhost vì dùng XAMPP)
$dbname = 'beverage_store'; // Tên cơ sở dữ liệu
$username = 'root';         // Tên người dùng MySQL mặc định trong XAMPP
$password = '';             // Mật khẩu mặc định trong XAMPP (rỗng)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Kết nối thành công"; // Uncomment để kiểm tra
} catch (PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
}
?>