<?php
include __DIR__ . "/../config/db.php"; // Kết nối database

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Lấy dữ liệu từ form
    $Name = trim($_POST['fullname']); 
    $Username = trim($_POST['username']);
    $Email = trim($_POST['email']);
    $Phone = trim($_POST['phone']);
    $Password = $_POST['password'];
    $Confirm = $_POST['confirm'];
    $Address = trim($_POST['address']);

    // Mảng lưu lỗi
    $errors = [];

    // Regex cho số điện thoại VN
    $phonePattern = "/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-5]|9[0-9])[0-9]{7}$/";

    // 1️⃣ Kiểm tra rỗng
    if (empty($Name) || empty($Username) || empty($Email) || empty($Phone) || empty($Password) || empty($Confirm)) {
        $errors[] = "⚠️ Vui lòng nhập đầy đủ các trường bắt buộc!";
    }

    // 2️⃣ Kiểm tra định dạng email
    if (!empty($Email) && !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "⚠️ Địa chỉ email không hợp lệ!";
    }

    // 3️⃣ Kiểm tra định dạng SĐT Việt Nam
    if (!empty($Phone) && !preg_match($phonePattern, $Phone)) {
        $errors[] = "⚠️ Số điện thoại không đúng định dạng Việt Nam!";
    }

    // 4️⃣ Kiểm tra mật khẩu khớp
    if (!empty($Password) && !empty($Confirm) && $Password !== $Confirm) {
        $errors[] = "⚠️ Mật khẩu xác nhận không khớp!";
    }

    // 5️⃣ Kiểm tra username đã tồn tại
    if (!empty($Username)) {
        try {
            $check = $conn->prepare("SELECT * FROM users WHERE Username = ?");
            $check->execute([$Username]);

            if ($check->rowCount() > 0) {
                $errors[] = "⚠️ Tên truy cập đã tồn tại!";
            }
        } catch (PDOException $e) {
            $errors[] = "❌ Lỗi hệ thống khi kiểm tra username!";
        }
    }

    // 6️⃣ Nếu không có lỗi -> thêm người dùng
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($Password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (Name, Username, Email, Phone, Password, Address, roles, money)
                    VALUES (:Name, :Username, :Email, :Phone, :Password, :Address, 'user', 0)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':Name' => $Name,
                ':Username' => $Username,
                ':Email' => $Email,
                ':Phone' => $Phone,
                ':Password' => $hashedPassword,
                ':Address' => $Address
            ]);

            header("Location: login.php?msg=success");
            exit();
        } catch (PDOException $e) {
            $errors[] = "❌ Lỗi hệ thống khi thêm người dùng: " . $e->getMessage();
        }
    }

    // Gom tất cả lỗi để hiển thị
    if (!empty($errors)) {
        $msg = "<div class='error-box'><ul>";
        foreach ($errors as $error) {
            $msg .= "<li>$error</li>";
        }
        $msg .= "</ul></div>";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký</title>
<style>
body {
    background: url('../Page/gio-qua-tet-4.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
}
.container {
    width: 600px;
    background: rgba(255, 255, 255, 0.9);
    margin: 50px auto;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
}
h2 { color: red; text-align: center; margin-bottom: 10px; }
label { font-weight: bold; }
.row { display: flex; justify-content: space-between; margin-bottom: 15px; }
.col { width: 48%; }
input {
    width: 84%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;
}
button {
    padding: 10px 10px; border: none; border-radius: 5px; cursor: pointer;
}
.btn-primary { background: red; color: white; }
.btn-secondary { background: #555; color: white; }
.btn-home { background: #007BFF; color: white; text-decoration: none; padding: 9px 10px; border-radius: 5px; }
.center { text-align: center; margin-top: 20px; }

.error-box {
    background: #ffe9e9;
    border: 1px solid #ffb3b3;
    color: #d8000c;
    padding: 12px 18px;
    border-radius: 8px;
    margin-top: 20px;
    font-size: 15px;
}
.error-box ul {
    margin: 0;
    padding-left: 25px;
}
.error-box li {
    list-style-type: "⚠️ ";
    margin-bottom: 5px;
}
</style>
</head>
<body>
<div class="container">
    <h2>ĐĂNG KÝ</h2>
    <p style="text-align:center;">Trang chủ / <span style="color:red;">Đăng ký</span></p>

    <form method="POST" id="registerForm">
        <div class="row">
            <div class="col">
                <label>Họ và tên</label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
            </div>
            <div class="col">
                <label>Tên truy cập</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>Điện thoại</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
            </div>
            <div class="col">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>Địa chỉ</label>
                <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>
            <div class="col">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="confirm" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
        </div>

        <div class="center">
            <button type="submit" class="btn-primary">Đăng ký</button>
            <button type="button" class="btn-secondary" onclick="resetForm()">Làm lại</button>
            <a href="login.php" class="btn-home">Đăng nhập</a>
        </div>

        <?= $msg ?>
    </form>
</div>

<script>
function resetForm() {
    // Lấy form
    const form = document.getElementById('registerForm');

    // Reset form
    form.reset();

    // Xóa nội dung tất cả các input text còn giữ value do PHP echo
    document.querySelectorAll('input').forEach(i => i.value = '');

    // Xóa thông báo lỗi
    const errors = document.querySelector('.error-box');
    if (errors) errors.remove();

    // Đưa con trỏ về ô đầu tiên
    document.querySelector('input[name="fullname"]').focus();
}
</script>
</body>
</html>
