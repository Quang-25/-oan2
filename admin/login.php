<?php
session_start();
include __DIR__ . "/../config/db.php";

// Nếu đã đăng nhập thì chuyển về trang admin
if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE Username = :username AND roles = 'admin'");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['admin'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    } catch (PDOException $e) {
        $error = "Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.";
        // error_log($e->getMessage()); // Ghi lỗi thực tế ra file log
    }
}

// Cài đặt cho header
$title = "Đăng nhập Admin";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* --- CSS cho Trang Login --- */
       .body-login {
            /* LỚP PHỦ: 
              Một lớp phủ màu đỏ mờ (độ mờ 0.85)
              nằm TRÊN ảnh nền của bạn.
            */
            background: 
                linear-gradient(135deg, rgba(255, 147, 147, 0.7), rgba(255, 147, 156, 0.7)),
                url('../images/ảnh nền.jpg'); /* <-- THAY ĐỔI URL ẢNH CỦA BẠN TẠI ĐÂY */
            
            /* Các thuộc tính để đảm bảo ảnh nền phủ kín và đẹp */
            background-size: cover;      /* Phủ kín toàn bộ trang */
            background-position: center; /* Căn ảnh ra giữa */
            background-repeat: no-repeat;  /* Không lặp lại ảnh */
            background-attachment: fixed;  /* Giữ ảnh cố định khi cuộn (tùy chọn) */

            /* Các thuộc tính cũ bạn đã có */
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: #fff;
            color: #000;
            border-radius: 15px;
            padding: 40px;
            width: 380px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .btn-red {
            background-color: #b91c1c;
            color: white;
            border: none;
            font-weight: 500;
            padding: 10px;
        }
        .btn-red:hover {
            background-color: #dc2626;
            color: white;
        }
    </style>
</head>
<body class="body-login">

<div class="login-box">
    <h3 class="text-center mb-4 text-danger fw-bold">
        <i class="fa-solid fa-shield-halved"></i> Admin Login
    </h3>
    
    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Tên đăng nhập</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Mật khẩu</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-red w-100 mt-2">Đăng nhập</button>
    </form>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>