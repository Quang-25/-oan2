<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ==== KIỂM TRA ADMIN ==== */
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ==== DUYỆT ĐƠN HÀNG ==== */
if (isset($_GET['approve'])) {
    $order_id = (int)$_GET['approve'];
    
    try {
        // Lấy thông tin đơn hàng
        $stmt = $conn->prepare("
            SELECT o.*, p.totalquantity, u.email, u.username, p.products_name
            FROM orders o
            LEFT JOIN products p ON o.Product_ID = p.id_product
            LEFT JOIN users u ON o.User_ID = u.ID_user
            WHERE o.orders_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            // Kiểm tra nếu đơn hàng đang pending mới được duyệt
            if ($order['status'] !== 'pending') {
                $_SESSION['error'] = "✗ Đơn hàng này đã được xử lý rồi!";
            } else {
                // Kiểm tra tồn kho
                $current_inventory = (int)$order['totalquantity'];
                $needed = (int)$order['quantity'];
                
                if ($current_inventory < $needed) {
                    $_SESSION['error'] = "✗ Tồn kho không đủ! Chỉ còn " . $current_inventory;
                } else {
                    // Trừ kho (chỉ khi duyệt đơn)
                    $stmt = $conn->prepare("
                        UPDATE products 
                        SET totalquantity = totalquantity - ?,
                            quantitySold = quantitySold + ?
                        WHERE id_product = ?
                    ");
                    $stmt->execute([$needed, $needed, $order['Product_ID']]);
                    
                    // Cập nhật status từ pending → approved
                    $stmt = $conn->prepare("
                        UPDATE orders 
                        SET status = 'approved'
                        WHERE orders_id = ?
                    ");
                    $stmt->execute([$order_id]);
                    
                    // Gửi email
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
                            $mail->addAddress($order['email'], $order['username']);
                            $mail->isHTML(true);
                            $mail->Subject = "Xác nhận thanh toán & duyệt đơn hàng #$order_id - Giỏ Hàng Tết Việt";
                            $mail->Body = "
                                <h3>Xin chào {$order['username']}</h3>

                                <p>Chúng tôi đã <strong>xác nhận thanh toán</strong> cho đơn hàng 
                                 của bạn tại <strong>Giỏ Hàng Tết Việt</strong>.</p>
                                <p>Đơn hàng của bạn đã được duyệt và hiện đang trong quá trình chuẩn bị giao hàng.</p>
                                <p><strong>Thông tin đơn hàng:</strong></p>
                                <ul style='list-style: none; padding: 0;'>
                                    <li><strong>Sản phẩm:</strong> {$order['products_name']}</li>
                                    <li><strong>Số lượng:</strong> $needed</li>
                                    <li><strong>Tổng tiền đã thanh toán:</strong> " . number_format($order['totalamount']) . " đ</li>
                                    <li><strong>Ngày đặt:</strong> " . date('d/m/Y H:i', strtotime($order['order_date'])) . "</li>
                                    <li><strong>Phương thức thanh toán:</strong> {$order['payment_method']}</li>
                                </ul>

                                <p>Chúng tôi sẽ sớm liên hệ với bạn khi đơn hàng được bàn giao cho đơn vị vận chuyển.</p>

                                <br>
                                <p>Trân trọng,<br>
                                <strong>Đội ngũ Giỏ Hàng Tết Việt</strong></p>
                            ";
                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Lỗi gửi email duyệt: " . $mail->ErrorInfo);
                        }
                    }
                    
                    $_SESSION['success'] = "✓ Duyệt đơn hàng thành công! Kho đã được trừ.";
                }
            }
        } else {
            $_SESSION['error'] = "✗ Không tìm thấy đơn hàng!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "✗ Lỗi: " . $e->getMessage();
    }
    
    header("Location: index.php?page=oders");
    exit;
}

/* ==== HUỶ ĐƠN HÀNG ==== */
if (isset($_GET['reject'])) {
    $order_id = (int)$_GET['reject'];
    
    try {
        $stmt = $conn->prepare("
            SELECT o.*, u.email, u.username 
            FROM orders o
            LEFT JOIN users u ON o.User_ID = u.ID_user
            WHERE o.orders_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cập nhật status
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'cancelled'
            WHERE orders_id = ?
        ");
        $stmt->execute([$order_id]);
        
        // Gửi email
        if ($order && $order['email']) {
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
                $mail->addAddress($order['email'], $order['username']);
                $mail->isHTML(true);
                $mail->Subject = ' Đơn hàng #' . $order_id . ' bị huỷ';
                $mail->Body = "
                    <h3>Xin chào {$order['username']}</h3>
                    <p>Đơn hàng <strong>#$order[products_name]</strong> của bạn đã bị huỷ.</p>
                    <p>Lý do huỷ  do:</p>
                    <ul>
                        <li>Sản phẩm hết hàng</li>
                        <li>Không đủ điều kiện thanh toán</li>
                        <li>Các vấn đề khác về giao hàng</li>
                    </ul>
                    <p>Vui lòng <strong>liên hệ với chúng tôi</strong> để biết thêm chi tiết.</p>
                    <p>Số điện thoại hỗ trợ: <strong>1900 9477</strong></p>
                    <br><p>Xin lỗi vì sự bất tiện này!<br>Đội ngũ hỗ trợ khách hàng</p>";
                $mail->send();
            } catch (Exception $e) {
                error_log("Lỗi gửi email huỷ: " . $mail->ErrorInfo);
            }
        }
        
        $_SESSION['success'] = "✓ Huỷ đơn hàng thành công!";
    } catch (Exception $e) {
        $_SESSION['error'] = "✗ Lỗi: " . $e->getMessage();
    }
    
    header("Location: index.php?page=oders");
    exit;
}

/* ==== XÓA ĐƠN HÀNG ==== */
if (isset($_GET['delete'])) {
    $order_id = (int)$_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM orders WHERE orders_id = ?");
        $stmt->execute([$order_id]);
        $_SESSION['success'] = "✓ Xóa đơn hàng thành công!";
    } catch (Exception $e) {
        $_SESSION['error'] = "✗ Lỗi: " . $e->getMessage();
    }
    
    header("Location: index.php?page=oders");
    exit;
}

/* ==== TÌM KIẾM ==== */
$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $conn->prepare("
        SELECT o.*, u.username AS customer_name, p.products_name AS product_name
        FROM orders o
        LEFT JOIN users u ON o.User_ID = u.ID_user
        LEFT JOIN products p ON o.Product_ID = p.id_product
        WHERE u.username LIKE ? OR p.products_name LIKE ?
        ORDER BY o.orders_id DESC
    ");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("
        SELECT o.*, u.username AS customer_name, p.products_name AS product_name
        FROM orders o
        LEFT JOIN users u ON o.User_ID = u.ID_user
        LEFT JOIN products p ON o.Product_ID = p.id_product
        ORDER BY o.orders_id DESC
    ");
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<h1 class="text-center mb-4" style="color: #b91c1c; font-size: 2.5rem; font-weight: bold; padding-bottom: 20px; border-bottom: 3px solid #b91c1c;">Quản Lý Đơn Hàng</h1>

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

<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" action="index.php" class="d-flex">
        <input type="hidden" name="page" value="oders">
        <input name="search" class="form-control me-2" placeholder="Tìm khách hàng / sản phẩm"
               value="<?= htmlspecialchars($search) ?>" style="max-width: 400px;">
        <button type="submit" class="btn btn-primary">🔍 Tìm Kiếm</button>
        <?php if ($search): ?>
            <a href="index.php?page=oders" class="btn btn-outline-secondary ms-2">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<div style="overflow-x: auto;">
<table class="table table-bordered text-center align-middle">
<thead>
<tr style="background: #b91c1c; color: white;">
    <th style="border: 1px solid #b91c1c;">ID</th>
    <th style="border: 1px solid #b91c1c;">Khách hàng</th>
    <th style="border: 1px solid #b91c1c;">Sản phẩm</th>
    <th style="border: 1px solid #b91c1c;">Số lượng</th>
    <th style="border: 1px solid #b91c1c;">Tổng tiền</th>
    <th style="border: 1px solid #b91c1c;">Ngày đặt</th>
    <th style="border: 1px solid #b91c1c;">Thanh toán</th>
    <th style="border: 1px solid #b91c1c;">Trạng thái</th>
    <th style="border: 1px solid #b91c1c;">Hành động</th>
</tr>
</thead>
<tbody>
<?php if ($orders): 
    foreach ($orders as $o): 
        $status = $o['status'] ?? 'pending';
        $badge_class = ($status === 'approved') ? 'bg-success' : (($status === 'cancelled') ? 'bg-danger' : 'bg-warning text-dark');
        $status_text = ($status === 'approved') ? '✓ Đã duyệt' : (($status === 'cancelled') ? '✗ Đã huỷ' : '⏳ Chờ duyệt');
?>
<tr>
    <td><strong><?= $o['orders_id'] ?></strong></td>
    <td><?= htmlspecialchars($o['customer_name'] ?? 'Ẩn danh') ?></td>
    <td><?= htmlspecialchars($o['product_name'] ?? 'Đã xóa') ?></td>
    <td><span class="badge bg-info"><?= $o['quantity'] ?></span></td>
    <td class="text-danger fw-bold"><?= number_format($o['totalamount'], 0, ',', '.') ?>đ</td>
    <td><?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></td>
    <td><?= htmlspecialchars($o['payment_method'] ?? 'N/A') ?></td>
    <td><span class="badge <?= $badge_class ?>"><?= $status_text ?></span></td>
    <td style="white-space: nowrap;">
        <?php if ($status === 'pending'): ?>
            <a href="index.php?page=oders&approve=<?= $o['orders_id'] ?>"
               class="btn btn-success btn-sm"
               onclick="return confirm('Duyệt đơn hàng này? Kho sẽ được trừ.')">✓ Duyệt</a>
            <a href="index.php?page=oders&reject=<?= $o['orders_id'] ?>"
               class="btn btn-warning btn-sm"
               onclick="return confirm('Huỷ đơn hàng này?')">✕ Huỷ</a>
        <?php endif; ?>
        <a href="index.php?page=oders&delete=<?= $o['orders_id'] ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Xóa đơn hàng này?')">🗑️ Xóa</a>
    </td>
</tr>
<?php 
    endforeach;
else: 
?>
<tr>
    <td colspan="9" class="text-center text-muted py-4">📭 Không có đơn hàng nào</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>

<style>
.table-bordered th, .table-bordered td {
    border-color: #ddd;
}
.table-bordered thead th {
    background: #b91c1c !important;
    color: white !important;
    font-weight: bold;
}
.btn-sm {
    padding: 5px 10px;
    font-size: 0.85rem;
}
</style>

<script>
// Tự động đóng alert sau 3 giây
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
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
