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

                // 🚫 Kiểm tra nếu là admin thì không cho đăng nhập ở đây
                if ($user["roles"] === "admin" || $user["roles"] == 1) {
                    $msg = "❌ Tài khoản admin không thể đăng nhập vào khu vực người dùng!";
                } else {
                    // ✅ Tạo session người dùng
                    $_SESSION["ID_user"] = $user["ID_user"];
                    $_SESSION["username"] = $user["Username"];
                    $_SESSION["name"] = $user["Name"];
                    $_SESSION["roles"] = $user["roles"];

                    // ✅ Khôi phục giỏ hàng từ bảng user_cart
                    $_SESSION['cart'] = [];
                    $cartQuery = $conn->prepare("
                        SELECT uc.product_id, uc.quantity, 
                               p.products_name, p.price, p.images, p.image1, p.image2
                        FROM user_carts uc
                        JOIN products p ON uc.product_id = p.id_product
                        WHERE uc.user_id = :uid
                    ");
                    $cartQuery->execute(['uid' => $_SESSION['ID_user']]);
                    while ($row = $cartQuery->fetch(PDO::FETCH_ASSOC)) {
                        $img = $row['images'] ?: ($row['image1'] ?: ($row['image2'] ?: '../images/no-image.jpg'));
                        $_SESSION['cart'][$row['product_id']] = [
                            'id'       => $row['product_id'],
                            'name'     => $row['products_name'],
                            'price'    => $row['price'],
                            'image'    => $img,
                            'quantity' => $row['quantity']
                        ];
                    }

                    // ✅ Chuyển sang trang home.php
                    header("Location: home.php");
                    exit();
                } // <-- đóng if roles
            } else {
                $msg = "❌ Sai tên đăng nhập hoặc mật khẩu!";
            } // <-- đóng if kiểm tra user/password
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
            background-size: cover;
            /* Giữ tỉ lệ ảnh, phóng vừa đủ để phủ toàn màn hình */
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
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            backdrop-filter: blur(8px);
        }

        h2 {
            color: red;
            margin-bottom: 10px;
        }

        input {
            width: 70%;
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
<?php include(__DIR__ . "/../include/Header.php"); ?>

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
    <?php include(__DIR__ . "/../include/Footer.php"); ?>
</body>

</html>