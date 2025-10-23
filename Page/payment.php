<?php
session_start();
include("../config/db.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$success = ''; 
// Nếu chưa đăng nhập → quay lại trang đăng nhập
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../Page/login.php");
    exit;
}

$user_id = $_SESSION['ID_user'];

// ===== Lấy thông tin giỏ hàng từ session =====
$cartItems = $_SESSION['cart'] ?? [];

// Lấy thông tin người dùng
$sqlusers = "SELECT Name, Email, Address, Phone FROM users WHERE ID_user = :id_user";
$stmt = $conn->prepare($sqlusers);
$stmt->execute(['id_user' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];
$formData = [
    'fullname' => $user['Name'] ?? '',
    'email' => $user['Email'] ?? '',
    'phone' => $user['Phone'] ?? '',
    'address' => $user['Address'] ?? '',
    'note' => ''
];

// Khi người dùng nhấn nút thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {

    $formData = [
        'fullname' => trim($_POST['fullname'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'note' => trim($_POST['note'] ?? '')
    ];

    if (empty($cartItems)) {
        $errors['cart'] = "🛒 Giỏ hàng của bạn đang trống!";
    } else {
        $phonePattern = "/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-5]|9[0-9])[0-9]{7}$/";
        
        if (!empty($Email) && !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "⚠️ Địa chỉ email không hợp lệ!";
    }
        if (empty($formData['phone'])) {
            $errors['phone'] = "⚠️ Vui lòng nhập số điện thoại!";
        } elseif (!preg_match($phonePattern, $formData['phone'])) {
            $errors['phone'] = "⚠️ Số điện thoại không đúng định dạng Việt Nam!";
        }

        if (empty($formData['address'])) {
            $errors['address'] = "⚠️ Vui lòng nhập địa chỉ nhận hàng!";
        }

        // Nếu không có lỗi → lưu đơn hàng
        if (empty($errors)) {
            try {
                $conn->beginTransaction();

                $order_date = date('Y-m-d H:i:s');
                $payment_method = $_POST['payment_method'] ?? 'COD';

                foreach ($cartItems as $item) {
                    $product_id = $item['id_product'] ?? ($item['id'] ?? null);
                    if (!$product_id) continue;

                    $quantity = $item['quantity'] ?? 1;
                    $totalamount = ($item['price'] ?? 0) * $quantity;

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
                    $mail->Password   = 'higt jgrf aavo qnhg'; // Mật khẩu ứng dụng Gmail
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('Cohoi2512@gmail.com', 'GIỎ HÀNG TẾT VIỆT');
                    $mail->addAddress($formData['email'], $formData['fullname']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Xác nhận đơn hàng từ GIỎ HÀNG TẾT VIỆT';
                    $mail->Body = '
                        <h3>Xin chào ' . htmlspecialchars($formData['Name']) . ',</h3>
                        <p>Cảm ơn bạn đã đặt hàng tại <strong>Giỏ hàng Tết Việt</strong>! Đơn hàng của bạn đang được xử lý và sẽ được giao đến:</p>
                        <p><strong>' . htmlspecialchars($formData['Address']) . '</strong></p>
                        <p>Chúng tôi sẽ liên hệ qua số điện thoại: <strong>' . htmlspecialchars($formData['Phone']) . '</strong>.</p>
                        <br><p>Trân trọng,<br><strong>Giỏ hàng Tết Việt</strong></p>';

                    $mail->send();
                } catch (Exception $e) {
                    echo "❌ Không thể gửi email: {$mail->ErrorInfo}";
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
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/payment.css">
</head>
<body>

<div class="container my-5">
    <div class="pm-header">
        <h2>THANH TOÁN</h2>
        <div>Trang chủ / <span>Thanh Toán</span></div>
    </div>

    <form method="POST" novalidate>
        <div class="row g-4">
            <!-- ====== CỘT TRÁI: THÔNG TIN KHÁCH HÀNG ====== -->
            <div class="col-lg-7">
                <div class="card p-4">
                    <div class="mb-4">
                        <p class="section-header"><i class="bi bi-geo-alt-fill me-2"></i>THÔNG TIN KHÁCH HÀNG</p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" class="form-control" name="fullname"
                                    value="<?= htmlspecialchars($formData['fullname'] ?: ($user['Name'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email"
                                    value="<?= htmlspecialchars($formData['email'] ?: ($user['Email'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Điện thoại</label>
                                <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                    name="phone" value="<?= htmlspecialchars($formData['phone'] ?: ($user['Phone'] ?? '')) ?>" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Địa chỉ chi tiết</label>
                                <input type="text" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                    name="address" value="<?= htmlspecialchars($formData['address'] ?: ($user['Address'] ?? '')) ?>" required>
                                <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback"><?= $errors['address'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="section-header"><i class="bi bi-credit-card-fill me-2"></i>HÌNH THỨC THANH TOÁN</p>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" value="COD" checked>
                            <label class="form-check-label">Thanh toán khi nhận hàng (COD)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="BANK">
                            <label class="form-check-label">Chuyển khoản ngân hàng</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== CỘT PHẢI: TÓM TẮT ĐƠN HÀNG ====== -->
            <div class="col-lg-5">
                <div class="card summary-card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-cart3 me-2"></i>THÔNG TIN ĐƠN HÀNG</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalPrice = 0;
                        foreach ($cartItems as $item):
                            $price = $item['price'] ?? 0;
                            $quantity = $item['quantity'] ?? 1;
                            $totalPrice += $price * $quantity;
                            $img = $item['images'] ?? $item['image1'] ?? "../images/no-image.jpg";
                            $name = $item['name'] ?? 'Sản phẩm không tên';
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($img) ?>" alt="Ảnh" style="width:60px;height:60px;object-fit:cover" class="me-3">
                                <div>
                                    <p class="mb-0 small fw-semibold"><?= htmlspecialchars($name) ?></p>
                                    <p class="mb-0 small text-muted">x <?= $quantity ?></p>
                                </div>
                            </div>
                            <p class="mb-0 small text-danger fw-bold"><?= number_format($price * $quantity, 0, ',', '.') ?>đ</p>
                        </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Tổng cộng:</span>
                            <span class="text-danger"><?= number_format($totalPrice, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea class="form-control" name="note" rows="2"><?= htmlspecialchars($formData['note']) ?></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <?php if ($success): ?>
                            <div class="alert alert-success mb-2"><?= $success ?></div>
                        <?php elseif (!empty($errors)): ?>
                            <div class="alert alert-danger mb-2">⚠️ Vui lòng kiểm tra lại thông tin!</div>
                        <?php endif; ?>
                        <button type="submit" name="checkout" class="btn btn-primary w-100 fw-bold py-2">Thanh Toán</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
