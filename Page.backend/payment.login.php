<?php
session_start();
include("../config/db.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = ''; 
$errors = [];

if (!isset($_SESSION['ID_user'])) {
    header("Location: ../Page/login.php");
    exit;
}

$user_id = $_SESSION['ID_user'];
$cartItems = $_SESSION['cart'] ?? [];

$sqlusers = "SELECT Name, Email, Address, Phone FROM users WHERE ID_user = :id_user";
$stmt = $conn->prepare($sqlusers);
$stmt->execute(['id_user' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$formData = [
    'fullname' => $user['Name'] ?? '',
    'email'    => $user['Email'] ?? '',
    'phone'    => $user['Phone'] ?? '',
    'address'  => $user['Address'] ?? '',
    'note'     => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $formData = [
        'fullname' => trim($_POST['fullname'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'phone'    => trim($_POST['phone'] ?? ''),
        'address'  => trim($_POST['address'] ?? ''),
        'note'     => trim($_POST['note'] ?? '')
    ];

    if (empty($cartItems)) {
        $errors['cart'] = "🛒 Giỏ hàng của bạn đang trống!";
    }

    if (!empty($formData['email']) && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "⚠️ Email không hợp lệ!";
    }

    $phonePattern = "/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-5]|9[0-9])[0-9]{7}$/";
    if (empty($formData['phone'])) {
        $errors['phone'] = "⚠️ Vui lòng nhập số điện thoại!";
    } elseif (!preg_match($phonePattern, $formData['phone'])) {
        $errors['phone'] = "⚠️ Số điện thoại không đúng định dạng Việt Nam!";
    }

    if (empty($formData['address'])) {
        $errors['address'] = "⚠️ Vui lòng nhập địa chỉ nhận hàng!";
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            $order_date = date('Y-m-d H:i:s');
            $payment_method = $_POST['payment_method'] ?? 'COD';

            foreach ($cartItems as $item) {
                $product_id = $item['id'] ?? $item['id_product'];
                $quantity = $item['quantity'];
                $totalamount = $item['price'] * $quantity;

                // ✅ Thêm quantity vào INSERT để tránh lỗi
                $sql = "INSERT INTO orders (quantity, totalamount, User_ID, Product_ID, order_date, payment_method)
                        VALUES (:quantity, :totalamount, :user_id, :product_id, :order_date, :payment_method)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':quantity' => $quantity,
                    ':totalamount' => $totalamount,
                    ':user_id' => $user_id,
                    ':product_id' => $product_id,
                    ':order_date' => $order_date,
                    ':payment_method' => $payment_method
                ]);

                // 🔹 Trừ số lượng trong bảng products
                $updateSql = "UPDATE products 
                              SET totalquantity = totalquantity - :qty, 
                                  quantitySold = quantitySold + :qty 
                              WHERE id_product = :pid";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->execute([
                    ':qty' => $quantity,
                    ':pid' => $product_id
                ]);
            }

            $conn->commit();

            // ✅ Gửi email xác nhận
            require '../vendor/autoload.php';
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'Cohoi2512@gmail.com';
                $mail->Password   = 'higt jgrf aavo qnhg';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('Cohoi2512@gmail.com', 'Giỏ Hàng Tết Việt');
                $mail->addAddress($formData['email'], $formData['fullname']);
                $mail->isHTML(true);
                $mail->Subject = 'Xác nhận đơn hàng từ Giỏ Hàng Tết Việt';
                $mail->Body = "
                    <h3>Xin chào {$formData['fullname']},</h3>
                    <p>Cảm ơn bạn đã đặt hàng tại <strong>Giỏ Hàng Tết Việt</strong>!</p>
                    <p>Địa chỉ giao hàng: <strong>{$formData['address']}</strong></p>
                    <p>Liên hệ: <strong>{$formData['phone']}</strong></p>
                    <p>Phương thức thanh toán: <strong>{$payment_method}</strong></p>
                    <br><p>Trân trọng,<br>Đội ngũ Giỏ Hàng Tết Việt</p>";
                $mail->send();
            } catch (Exception $e) {
                error_log("Lỗi gửi email: " . $mail->ErrorInfo);
            }

            unset($_SESSION['cart']);
            header("Location: ../Page/Home.php");
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            echo "Lỗi khi thanh toán: " . $e->getMessage();
        }
    }
}
?>