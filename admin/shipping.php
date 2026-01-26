<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ==== KIỂM TRA & TẠO CỘT NẾU CHƯA CÓ ==== */
try {
    $checkColumns = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_status'");
    if ($checkColumns->rowCount() === 0) {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_status VARCHAR(50) DEFAULT 'pending'");
    }
    
    $checkColumns = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_date'");
    if ($checkColumns->rowCount() === 0) {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_date DATETIME");
    }
    
    $checkColumns = $conn->query("SHOW COLUMNS FROM orders LIKE 'return_date'");
    if ($checkColumns->rowCount() === 0) {
        $conn->exec("ALTER TABLE orders ADD COLUMN return_date DATETIME");
    }
    
    $checkColumns = $conn->query("SHOW COLUMNS FROM orders LIKE 'expected_delivery_date'");
    if ($checkColumns->rowCount() === 0) {
        $conn->exec("ALTER TABLE orders ADD COLUMN expected_delivery_date DATETIME");
    }
} catch (Exception $e) {
    error_log("Lỗi tạo cột: " . $e->getMessage());
}

/* ==== KIỂM TRA ADMIN ==== */
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$success = '';
$error = '';

/* ==== XÁC NHẬN ĐÃ GIAO HÀNG THÀNH CÔNG ==== */
if (isset($_GET['confirm_delivery'])) {
    $order_id = (int)$_GET['confirm_delivery'];
    
    try {
        $stmt = $conn->prepare("
            SELECT o.*, u.email, u.username, u.Name 
            FROM orders o
            LEFT JOIN users u ON o.User_ID = u.ID_user
            WHERE o.orders_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['status'] === 'approved') {
            // Cập nhật trạng thái giao vận thành 'delivered'
            $stmt = $conn->prepare("
                UPDATE orders 
                SET delivery_status = 'delivered', delivery_date = NOW()
                WHERE orders_id = ?
            ");
            $stmt->execute([$order_id]);
            
            // Gửi email xác nhận giao hàng thành công
            if ($order['email']) {
                require_once __DIR__ . "/../vendor/autoload.php";
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'Cohoi2512@gmail.com';
                    $mail->Password   = 'higt jgrf aavo qnhg';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('Cohoi2512@gmail.com', 'Giỏ Hàng Tết Việt');
                    $mail->addAddress($order['email'], $order['Name']);
                    $mail->isHTML(true);
                    $mail->Subject = "✓ Xác nhận giao hàng thành công - Đơn hàng #{$order['orders_id']}";
                    $mail->Body = "
                        <h3>Xin chào {$order['Name']}</h3>
                        <p>Cảm ơn bạn đã mua hàng tại <strong>Giỏ Hàng Tết Việt</strong>!</p>
                        <p><strong style='color: #22c55e;'>✓ Đơn hàng của bạn đã được giao thành công!</strong></p>
                        <p><strong>Chi tiết đơn hàng:</strong></p>
                        <ul>
                            <li>Mã đơn: <strong>#" . $order['orders_id'] . "</strong></li>
                            <li>Số tiền: <strong>" . number_format($order['totalamount'], 0, ',', '.') . "đ</strong></li>
                            <li>Ngày giao: <strong>" . date('d/m/Y H:i') . "</strong></li>
                        </ul>
                        <p>Nếu có bất kỳ vấn đề gì, vui lòng liên hệ với chúng tôi!</p>
                        <br>
                        <p>Trân trọng,<br><strong>Đội ngũ Giỏ Hàng Tết Việt</strong></p>
                    ";
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Lỗi gửi email: " . $mail->ErrorInfo);
                }
            }
            
            $success = "✓ Xác nhận giao hàng thành công!";
        } else {
            $error = "✗ Chỉ có thể xác nhận giao hàng cho đơn đã duyệt!";
        }
    } catch (Exception $e) {
        $error = "✗ Lỗi: " . $e->getMessage();
    }
}

/* ==== HOÀN HÀNG & LƯU KHO ==== */
if (isset($_GET['return_order'])) {
    $order_id = (int)$_GET['return_order'];
    
    try {
        // Lấy thông tin đơn hàng
        $stmt = $conn->prepare("
            SELECT o.*, p.id_product, u.email, u.Name 
            FROM orders o
            LEFT JOIN products p ON o.Product_ID = p.id_product
            LEFT JOIN users u ON o.User_ID = u.ID_user
            WHERE o.orders_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['delivery_status'] === 'delivered') {
            // Hoàn lại số lượng vào kho
            $stmt = $conn->prepare("
                UPDATE products 
                SET totalquantity = totalquantity + ?,
                    quantitySold = quantitySold - ?
                WHERE id_product = ?
            ");
            $stmt->execute([$order['quantity'], $order['quantity'], $order['Product_ID']]);
            
            // Cập nhật trạng thái đơn hàng thành 'returned'
            $stmt = $conn->prepare("
                UPDATE orders 
                SET delivery_status = 'returned', return_date = NOW()
                WHERE orders_id = ?
            ");
            $stmt->execute([$order_id]);
            
            // Gửi email xác nhận hoàn hàng
            if ($order['email']) {
                require_once __DIR__ . "/../vendor/autoload.php";
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'Cohoi2512@gmail.com';
                    $mail->Password   = 'higt jgrf aavo qnhg';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('Cohoi2512@gmail.com', 'Giỏ Hàng Tết Việt');
                    $mail->addAddress($order['email'], $order['Name']);
                    $mail->isHTML(true);
                    $mail->Subject = "✓ Xác nhận hoàn hàng - Đơn hàng #{$order['orders_id']}";
                    $mail->Body = "
                        <h3>Xin chào {$order['Name']}</h3>
                        <p>Chúng tôi đã xác nhận <strong>hoàn hàng</strong> cho đơn hàng của bạn!</p>
                        <p><strong style='color: #22c55e;'>✓ Số tiền sẽ được hoàn lại trong 3-5 ngày làm việc.</strong></p>
                        <p><strong>Chi tiết:</strong></p>
                        <ul>
                            <li>Mã đơn: <strong>#" . $order['orders_id'] . "</strong></li>
                            <li>Số tiền hoàn: <strong>" . number_format($order['totalamount'], 0, ',', '.') . "đ</strong></li>
                        </ul>
                        <br>
                        <p>Cảm ơn sự thông cảm của bạn!<br><strong>Đội ngũ Giỏ Hàng Tết Việt</strong></p>
                    ";
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Lỗi gửi email: " . $mail->ErrorInfo);
                }
            }
            
            $success = "✓ Hoàn hàng thành công! Kho đã được cập nhật.";
        } else {
            $error = "✗ Chỉ có thể hoàn hàng cho đơn đã giao thành công!";
        }
    } catch (Exception $e) {
        $error = "✗ Lỗi: " . $e->getMessage();
    }
}

/* ==== HẸN NGÀY ĐÃ BỎ ==== */

/* ==== LẤY DANH SÁCH ĐƠN HÀNG ĐÃ DUYỆT (CHỜ GIAO) ==== */
$stmt = $conn->query("
    SELECT o.*, u.username, u.Name, u.Email, u.Phone, u.Address, p.products_name
    FROM orders o
    LEFT JOIN users u ON o.User_ID = u.ID_user
    LEFT JOIN products p ON o.Product_ID = p.id_product
    WHERE o.status = 'approved'
    ORDER BY o.order_date DESC
");
$shipping_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giao Vận</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        h1 { color: #b91c1c; font-weight: bold; margin-bottom: 30px; }
        .card { border-left: 5px solid #dc2626; }
        .delivery-status { padding: 5px 10px; border-radius: 5px; font-weight: bold; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-returned { background: #fee2e2; color: #7f1d1d; }
        table { font-size: 0.9rem; }
        .action-btns { display: flex; gap: 5px; flex-wrap: wrap; }
        .modal-header { background: #b91c1c; color: white; }
    </style>
</head>
<body>
<div class="container my-5">
    <h1><i class="bi bi-truck"></i> Quản Lý Giao Vận</h1>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-danger">
                <tr>
                    <th style="width: 10%;">Mã ĐH</th>
                    <th style="width: 15%;">Khách Hàng</th>
                    <th style="width: 15%;">Địa Chỉ / SĐT</th>
                    <th style="width: 12%;">Sản Phẩm</th>
                    <th style="width: 6%;">SL</th>
                    <th style="width: 10%;">Tiền</th>
                    <th style="width: 12%;">Trạng Thái</th>
                    <th style="width: 20%;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($shipping_orders): ?>
                    <?php foreach ($shipping_orders as $order):
                        $delivery_status = $order['delivery_status'] ?? 'pending';
                        $status_class = ($delivery_status === 'delivered') ? 'status-delivered' : 
                                       (($delivery_status === 'returned') ? 'status-returned' : 'status-pending');
                        $status_text = ($delivery_status === 'delivered') ? '✓ Đã Giao' :
                                      (($delivery_status === 'returned') ? '↩️ Đã Hoàn' : '🚚 Chờ Giao');
                    ?>
                    <tr>
                        <td><strong>#<?= $order['orders_id'] ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($order['Name'] ?? 'N/A') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($order['username'] ?? '') ?></small>
                        </td>
                        <td>
                            <small><?= htmlspecialchars($order['Address'] ?? 'N/A') ?></small><br>
                            <strong>☎️</strong> <?= htmlspecialchars($order['Phone'] ?? 'N/A') ?><br>
                            <strong>📧</strong> <?= htmlspecialchars($order['Email'] ?? 'N/A') ?>
                        </td>
                        <td><small><?= htmlspecialchars($order['products_name'] ?? 'Đã xóa') ?></small></td>
                        <td><span class="badge bg-info"><?= $order['quantity'] ?></span></td>
                        <td class="text-danger fw-bold"><small><?= number_format($order['totalamount'], 0, ',', '.') ?>đ</small></td>
                        <td><span class="delivery-status <?= $status_class ?>"><?= $status_text ?></span></td>
                        <td>
                            <div class="action-btns">
                                <?php if ($delivery_status === 'pending'): ?>
                                    <a href="index.php?page=shipping&confirm_delivery=<?= $order['orders_id'] ?>"
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Xác nhận đã giao hàng?')">
                                       <i class="bi bi-check-circle"></i> Giao
                                    </a>
                                <?php elseif ($delivery_status === 'delivered'): ?>
                                    <a href="index.php?page=shipping&return_order=<?= $order['orders_id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Xác nhận hoàn hàng? Kho sẽ được cập nhật.')">
                                       ↩️ Hoàn Hàng
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Hoàn xong</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            📭 Không có đơn hàng nào chờ giao
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tự động đóng alert sau 3 giây
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-success, .alert-danger');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-in-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 3000);
    });
});
</script>
</body>
</html>
