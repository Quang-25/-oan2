<?php
include __DIR__ . "/../Page.backend/payment.login.php";   
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
 <?php include(__DIR__ . "/../include/Header.php"); ?>
<div class="container my-5">
    <div class="pm-header mb-4">
        <h2>THANH TOÁN</h2>
        <div>Trang chủ / <span>Thanh Toán</span></div>
    </div>

    <form method="POST" novalidate>
        <div class="row g-4">
            <!-- ====== THÔNG TIN KHÁCH HÀNG ====== -->
            <div class="col-lg-7">
                <div class="card p-4">
                    <p class="section-header"><i class="bi bi-person-fill me-2"></i>THÔNG TIN KHÁCH HÀNG</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" name="fullname"
                                   value="<?= htmlspecialchars($formData['fullname']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   name="email" value="<?= htmlspecialchars($formData['email']) ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Điện thoại</label>
                            <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                   name="phone" value="<?= htmlspecialchars($formData['phone']) ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                   name="address" value="<?= htmlspecialchars($formData['address']) ?>" required>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= $errors['address'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="section-header"><i class="bi bi-credit-card-fill me-2"></i>Phương THỨC THANH TOÁN</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="Thanh Toán Khi Nhận Hàng" checked>
                        <label class="form-check-label">Thanh toán khi nhận hàng (COD)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="Chuyển Khoản Ngân Hàng">
                        <label class="form-check-label">Chuyển khoản ngân hàng</label>
                    </div>
                </div>
            </div>

            <!-- ====== TÓM TẮT ĐƠN HÀNG ====== -->
            <div class="col-lg-5">
                <div class="card summary-card">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-cart3 me-2"></i>THÔNG TIN ĐƠN HÀNG</h5>
                    </div>
                    <div class="card-body">
                        <?php $totalPrice = 0; ?>
                        <?php foreach ($cartItems as $item): 
                            $price = $item['price'];
                            $quantity = $item['quantity'];
                            // 🔹 Ưu tiên ảnh theo thứ tự: image → images → image1 → no-image
                            $img = $item['image'] ?? ($item['images'] ?? ($item['image1'] ?? '../images/no-image.jpg'));
                            $totalPrice += $price * $quantity;
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($img) ?>" alt="Ảnh sản phẩm"
                                     style="width:60px;height:60px;object-fit:cover" class="me-3">
                                <div>
                                    <p class="mb-0 small fw-semibold"><?= htmlspecialchars($item['name']) ?></p>
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
<?php include(__DIR__ . "/../include/Footer.php"); ?>
</body>
</html>
