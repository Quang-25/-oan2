<?php
// Bắt đầu session để header có thể kiểm tra trạng thái đăng nhập
session_start(); 
include __DIR__ . "/../config/db.php"; // Kết nối database

$msg = "";

// Phần xử lý logic PHP của bạn đã rất tốt, giữ nguyên không thay đổi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Name = trim($_POST['fullname']); 
    $Username = trim($_POST['username']);
    $Email = trim($_POST['email']);
    $Phone = trim($_POST['phone']);
    $Password = $_POST['password'];
    $Confirm = $_POST['confirm'];
    $Address = trim($_POST['address']);
    $errors = [];
    $phonePattern = "/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-5]|9[0-9])[0-9]{7}$/";

    if (empty($Name) || empty($Username) || empty($Email) || empty($Phone) || empty($Password) || empty($Confirm)) {
        $errors[] = "Vui lòng nhập đầy đủ các trường bắt buộc!";
    }
    if (!empty($Email) && !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Địa chỉ email không hợp lệ!";
    }
    if (!empty($Phone) && !preg_match($phonePattern, $Phone)) {
        $errors[] = "Số điện thoại không đúng định dạng Việt Nam!";
    }
    if (!empty($Password) && !empty($Confirm) && $Password !== $Confirm) {
        $errors[] = "Mật khẩu xác nhận không khớp!";
    }
    if (!empty($Username)) {
        try {
            $check = $conn->prepare("SELECT * FROM users WHERE Username = ?");
            $check->execute([$Username]);
            if ($check->rowCount() > 0) {
                $errors[] = "Tên truy cập đã tồn tại!";
            }
        } catch (PDOException $e) {
            $errors[] = "Lỗi hệ thống khi kiểm tra username!";
        }
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($Password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (Name, Username, Email, Phone, Password, Address, roles, money)
                    VALUES (:Name, :Username, :Email, :Phone, :Password, :Address, 'user', 0)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':Name' => $Name, ':Username' => $Username, ':Email' => $Email,
                ':Phone' => $Phone, ':Password' => $hashedPassword, ':Address' => $Address
            ]);
            header("Location: login.php?msg=success");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Lỗi hệ thống khi thêm người dùng: " . $e->getMessage();
        }
    }
    if (!empty($errors)) {
        $msg = "<div class='error-box'><ul>";
        foreach ($errors as $error) { $msg .= "<li>$error</li>"; }
        $msg .= "</ul></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <style>
        body.body2 {
            background: url('../Page/hop-qua-tet-banh-keo-dep-2021.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .container2 {
            width: 800px; /* Tăng chiều rộng để vừa 2 cột */
            background: rgba(255, 255, 255, 0.9);
            margin: 50px auto;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            text-align: center;
            backdrop-filter: blur(8px);
        }
        h2 { color: red; margin-bottom: 10px; }

        /* ** CSS CHO LAYOUT 2 CỘT ** */
        .form-row {
            display: flex;
            justify-content: space-between; /* Tạo khoảng cách giữa 2 cột */
            margin-bottom: 15px;
        }
        .form-col {
            width: 48%; /* Mỗi cột chiếm gần 1 nửa */
            text-align: left;
        }
        /* Style chung cho input và label */
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input {
            width: 100%; /* Input chiếm toàn bộ chiều rộng của cột */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Quan trọng để padding không làm vỡ layout */
        }
        /* Trường địa chỉ chiếm toàn bộ chiều rộng */
        .full-width {
            width: 100%;
            text-align: left;
            margin-bottom: 15px;
        }
        
        button {
            width: 40%;
            background: red; color: white; border: none; padding: 12px;
            margin-top: 15px; border-radius: 5px; cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background: darkred; }
        .link { margin-top: 20px; }
        .link a { color: blue; text-decoration: none; }
        .link a:hover { text-decoration: underline; }

        /* Style cho phần thông báo lỗi */
        .error-box {
            background: #ffe9e9; border: 1px solid #ffb3b3; color: #d8000c;
            padding: 12px; border-radius: 8px; margin-top: 20px;
            font-size: 15px; text-align: left;
        }
        .error-box ul { margin: 0; padding-left: 20px; }
    </style>
</head>

<?php include(__DIR__ . "/../include/Header.php"); ?>

<body class="body2">
    <div class="container2">
        <h2>ĐĂNG KÝ TÀI KHOẢN</h2>
        <p>Trang chủ / <span style="color:red;">Đăng ký</span></p>

        <form method="POST" id="registerForm">
            <div class="form-row">
                <div class="form-col">
                    <label for="fullname">Họ và tên</label>
                    <input id="fullname" type="text" name="fullname" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
                </div>
                <div class="form-col">
                    <label for="username">Tên truy cập</label>
                    <input id="username" type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-col">
                    <label for="phone">Điện thoại</label>
                    <input id="phone" type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="password">Mật khẩu</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div class="form-col">
                    <label for="confirm">Xác nhận mật khẩu</label>
                    <input id="confirm" type="password" name="confirm" required>
                </div>
            </div>
            
            <div class="full-width">
                 <label for="address">Địa chỉ</label>
                 <input id="address" type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>

            <?php if (!empty($msg)) echo $msg; ?>

            <button type="submit">Đăng ký</button>

            <div class="link">
                <a href="login.php">Đã có tài khoản? Đăng nhập ngay</a>
            </div>
        </form>
    </div>

<?php include(__DIR__ . "/../include/Footer.php"); ?>
</body>
</html>