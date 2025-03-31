<?php 
session_start();
include 'includes/db_connect.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý khi người dùng cập nhật hồ sơ
if (isset($_POST['update_profile'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Kiểm tra xem tên người dùng hoặc email đã tồn tại chưa (ngoại trừ user hiện tại)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND user_id != :user_id");
    $stmt->execute([':username' => $username, ':email' => $email, ':user_id' => $user_id]);
    
    if ($stmt->fetchColumn() > 0) {
        $error = "Username or email already exists!";
    } else {
        // Cập nhật thông tin người dùng
        $query = "UPDATE users SET username = :username, email = :email, full_name = :full_name, phone = :phone, address = :address";
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address,
            ':user_id' => $user_id
        ];
        
        if ($password) {
            $query .= ", password = :password";
            $params[':password'] = $password;
        }
        
        $query .= " WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute($params);
        
        if ($result) {
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['phone'] = $phone;
            $_SESSION['address'] = $address;
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile!";
        }
    }
}

// Lấy thông tin hiện tại của người dùng
$stmt = $conn->prepare("SELECT username, email, full_name, phone, address FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/global.css">
<link rel="stylesheet" href="assets/css/home.css">
<div class="profile-form">
    <h2>User Profile</h2>
    <?php if (isset($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        <label>Address</label>
        <textarea name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
        <label>New Password (leave blank if not changing)</label>
        <input type="password" name="password" placeholder="Enter new password">
        <button type="submit" name="update_profile">Update Profile</button>
    </form>
</div>

<style>
.profile-form {
    max-width: 500px;
    margin: 20px auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
.profile-form label {
    margin-top: 10px;
    display: block;
}
.profile-form input, .profile-form textarea {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}
.profile-form button {
    background-color: #28A745;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
}
.profile-form button:hover {
    background-color: #218838;
}
.success { color: green; }
.error { color: red; }
</style>
</body>
</html>
