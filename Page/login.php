<?php
include __DIR__ . "/../config/db.php";
session_start();
 
$msg = "";

// Khi người dùng gửi form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if ($username === "" || $password === "") {
        $msg = "⚠️ Vui lòng nhập đầy đủ thông tin!";
    } else {
        try {
            // Lấy dữ liệu từ bảng users
            $stmt = $conn->prepare("SELECT * FROM users WHERE Username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user["Password"])) {
                $_SESSION["ID_user"] = $user["ID_user"];
                $_SESSION["username"] = $user["Username"];
                $_SESSION["name"] = $user["Name"];
                $_SESSION["roles"] = $user["roles"];
                // ✅ Chuyển sang trang home.php
                header("Location: home.php");
                exit();
            } else {
                $msg = "❌ Sai tên đăng nhập hoặc mật khẩu!";
            }
        } catch (PDOException $e) {
            $msg = "Lỗi truy vấn CSDL: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng nhập</title>

<style>
.body2 {
    background: url('../Page/hop-qua-tet-banh-keo-dep-2021.jpg') no-repeat center center fixed;
    background-size: cover; /* Giữ tỉ lệ ảnh, phóng vừa đủ để phủ toàn màn hình */
    background-attachment: fixed;
    background-repeat: no-repeat;
    font-family: Arial, sans-serif;
}
.container2 {
    width: 450px;
    background: rgba(255, 255, 255, 0.85);
    margin: 80px auto;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    text-align: center;
    backdrop-filter: blur(8px);
}
h2 {
    color: red;
    margin-bottom: 10px;
}
input {
    width:70%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    width: 70%;
    background: red;
    color: white;
    border: none;
    padding: 10px;
    margin-top: 15px;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background: darkred;
}
.error {
    color: red;
    margin-top: 10px;
}
.success {
    color: green;
    margin-top: 10px;
}
.link {
    margin-top: 15px;
}
a {
    color: blue;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
</head>
<?php include(__DIR__ . "/../include/Header.php");?>
<body class="body2">
<div class="container2">
    <h2>ĐĂNG NHẬP</h2>
    <p>Trang chủ / <span style="color:red;">Đăng nhập</span></p>

    <?php 
    // Hiển thị thông báo đăng ký thành công (từ register.php chuyển qua)
    if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
        echo "<p class='success'>🎉 Đăng ký thành công! Mời bạn đăng nhập.</p>";
    }
    ?>

    <form method="POST">
        <div>
            <label> Tên truy cập</label><br>
            <input type="text" name="username" required>
        </div>
        <div>
            <label> Mật khẩu</label><br>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Đăng nhập</button>

        <?php if ($msg != "") echo "<p class='error'>$msg</p>"; ?>

        <div class="link">
            <a href="register.php">Chưa có tài khoản? Đăng ký ngay</a>
        </div>
    </form>
</div>
</body>
</html>
