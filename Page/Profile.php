<?php

include __DIR__ . '/../config/db.php';
include __DIR__ . "/../Page.backend/Profile.backend.php";
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tài khoản của tôi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/Profile.css">
</head>

<body>
    <?php include(__DIR__ . "/../include/Header.php"); ?>
    <div class="wrapper">
        <div class="sidebar">
            <a href="#info" class="active">Thông tin cá nhân</a>
            <a href="#orders">Đơn hàng đã mua</a>
        </div>

        <div class="content">
            <section id="info">
                <h2 style="text-align: center;">Thông tin cá nhân</h2>
                <?php if ($msg): ?>
                    <p class="msg"><?= htmlspecialchars($msg) ?></p>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['Name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['Email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['Address']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['Phone']) ?>">
                    </div>
                    <div class="text-center">
                        <button type="submit" name="update_info" class="btn btn-danger">Cập nhật</button>
                    </div>
                </form>
            </section>

            <hr class="my-4">

            <section id="orders">
                <h3 style="text-align: center;">Lịch Sử Mua Hàng</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Phương thức thanh toán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['orders_id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                                    <td><?= number_format($order['totalamount'], 0, ',', '.') ?>₫</td>
                                    <td>
                                        <?php
                                        switch ($order['payment_method']) {
                                            case 'cod':
                                                echo "Thanh toán khi nhận hàng";
                                                break;
                                            case 'online':
                                                echo "Chuyển khoản ngân hàng";
                                                break;
                                            default:
                                                echo htmlspecialchars($order['payment_method']);
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Chưa có đơn hàng nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <?php include(__DIR__ . "/../include/Footer.php"); ?>
</body>

</html>