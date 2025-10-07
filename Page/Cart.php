<?php
session_start();
include __DIR__ . "/../config/db.php";

// Thêm sản phẩm vào giỏ
if (isset($_GET['add'])) {
    $id = $_GET['add'];
    // Kiểm tra sản phẩm đã có trong giỏ chưa
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 1;
    } else {
        $_SESSION['cart'][$id]++;
    }
    header("Location: Cart.php");
    exit;
}

// Xóa sản phẩm khỏi giỏ
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: Cart.php");
    exit;
}

// Cập nhật số lượng
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = max(1, intval($qty)); // số lượng >=1
        $_SESSION['cart'][$id] = $qty;
    }
    header("Location: Cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . "/../include/Header.php"; ?>

<div class="container py-5">
    <h2 class="mb-4">Giỏ hàng của bạn</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
    <form method="post">
        <table class="table table-bordered align-middle text-center">
            <thead>
                <tr>
                    <th>Hình</th>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $id => $qty):
                    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = ?");
                    $stmt->execute([$id]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$product) continue;
                    $subtotal = $product['price'] * $qty;
                    $total += $subtotal;
                ?>
                <tr>
                    <td><img src="<?php echo $product['images']; ?>" width="80" alt=""></td>
                    <td><?php echo $product['products_name']; ?></td>
                    <td><?php echo number_format($product['price'],0,',','.'); ?> đ</td>
                    <td>
                        <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $qty; ?>" min="1" class="form-control w-50 mx-auto">
                    </td>
                    <td><?php echo number_format($subtotal,0,',','.'); ?> đ</td>
                    <td>
<a href="Cart.php?remove=<?php echo $id; ?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="submit" name="update_cart" class="btn btn-primary">Cập nhật giỏ</button>
            <h4>Tổng: <?php echo number_format($total,0,',','.'); ?> đ</h4>
        </div>

        <div class="mt-3 text-end">
            <a href="Checkout.php" class="btn btn-success">Thanh toán</a>
        </div>
    </form>
    <?php else: ?>
        <p>Giỏ hàng của bạn đang trống. <a href="index.php">Mua ngay</a></p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>