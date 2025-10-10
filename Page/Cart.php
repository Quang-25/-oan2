<?php
session_start();
include("../config/db.php");

// ===== Thêm sản phẩm vào giỏ hàng =====
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id_product = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += 1;
        } else {
            $image = $product['image1'] ?? 'no-image.jpg';
            $_SESSION['cart'][$id] = [
                'id' => $product['id_product'],
                'name' => $product['products_name'],
                'price' => $product['price'],
                'image' => $image,
                'quantity' => 1
            ];
        }
        $_SESSION['message'] = "✅ Đã thêm sản phẩm <b>{$product['products_name']}</b> vào giỏ hàng!";
    }
    header("Location: Cart.php");
    exit();
}

// ===== Xóa sản phẩm =====
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
        $_SESSION['message'] = "✅ Đã xóa sản phẩm khỏi giỏ hàng!";
    }
    header("Location: Cart.php");
    exit();
}

// ===== Cập nhật số lượng bằng AJAX =====
if (isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $id = intval($_POST['id']);
    $qty = intval($_POST['qty']);
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] = max(1, $qty);

        $subtotal = $_SESSION['cart'][$id]['price'] * $_SESSION['cart'][$id]['quantity'];
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        echo json_encode([
            'subtotal' => number_format($subtotal, 0, ',', '.') . ' đ',
            'total' => number_format($total, 0, ',', '.') . ' đ'
        ]);
    }
    exit;
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Giỏ hàng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f7f7f7; font-family: 'Roboto', sans-serif; }
.cart-header { background-color: #e5e5e5; padding: 30px 0; text-align: center; }
.cart-header h2 { color: #c50000; font-weight: 700; margin: 0; }
.product-img { width: 100px; border-radius: 8px; }
.price, .subtotal { color: #c50000; font-weight: 600; }
.btn-red { background-color: #c50000; color: #fff; border: none; }
.btn-red:hover { background-color: #a00000; }
.alert-success { background-color: #d4edda; border: none; color: #155724; }
</style>
</head>
<body>

<div class="cart-header">
    <h2>GIỎ HÀNG</h2>
    <div>Trang chủ / <span class="text-danger">Giỏ hàng</span></div>
</div>

<div class="container my-4">

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert" id="autoAlert">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <script>
      // Tự ẩn thông báo sau 2 giây
      setTimeout(() => {
        const alert = document.getElementById('autoAlert');
        if (alert) {
          const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
          bsAlert.close();
        }
      }, 1000);
    </script>
<?php endif; ?>

<?php if (!empty($_SESSION['cart'])): ?>
    <form method="post" id="cartForm">
        <div class="border-bottom border-danger mb-3"></div>

        <?php foreach ($_SESSION['cart'] as $item): 
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;

            // Fix str_contains cho PHP 8.1+
            $imgPath = '';
            if (!empty($item['image']) && is_string($item['image'])) {
                $imgPath = (strpos($item['image'], 'http') !== false) ? $item['image'] : '../images/' . $item['image'];
            } else {
                $imgPath = '../images/no-image.jpg';
            }
        ?>
        <div class="row align-items-center mb-3" id="item_<?= $item['id']; ?>">
            <div class="col-md-2 text-center">
                <img src="<?= htmlspecialchars($imgPath); ?>" class="product-img" onerror="this.src='../images/no-image.jpg'">
            </div>
            <div class="col-md-4 fw-semibold"><?= htmlspecialchars($item['name']); ?></div>
            <div class="col-md-2 price"><?= number_format($item['price'],0,',','.'); ?> đ</div>
            <div class="col-md-2">
                <div class="input-group input-group-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="changeQty(<?= $item['id']; ?>, -1)">−</button>
                    <input type="number" class="form-control text-center" id="qty_<?= $item['id']; ?>" value="<?= $item['quantity']; ?>" min="1">
                    <button type="button" class="btn btn-outline-secondary" onclick="changeQty(<?= $item['id']; ?>, 1)">+</button>
                </div>
            </div>
            <div class="col-md-1 subtotal" id="subtotal_<?= $item['id']; ?>"><?= number_format($subtotal,0,',','.'); ?> đ</div>
            <div class="col-md-1 text-center">
                <a href="Cart.php?remove=<?= $item['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></a>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="border-top border-danger mt-4 pt-3 text-end">
            <strong>Tổng tiền: </strong> <span id="total"><?= number_format($total,0,',','.'); ?> đ</span>
        </div>

        <div class="text-end mt-4">
            <a href="../index.php" class="btn btn-red me-2">XEM THÊM SẢN PHẨM</a>
            <a href="Checkout.php" class="btn btn-red">THANH TOÁN</a>
        </div>
    </form>

<?php else: ?>
    <div class="text-center py-5 text-muted">🛒 Giỏ hàng trống!</div>
    <div class="text-center">
        <a href="../index.php" class="btn btn-red">XEM SẢN PHẨM</a>
    </div>
<?php endif; ?>
</div>

<script>
function changeQty(id, delta) {
    const qtyInput = document.getElementById('qty_' + id);
    let qty = parseInt(qtyInput.value) + delta;
    if (qty < 1) qty = 1;
    qtyInput.value = qty;

    const formData = new FormData();
    formData.append('action', 'update_qty');
    formData.append('id', id);
    formData.append('qty', qty);

    fetch('Cart.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        document.getElementById('subtotal_' + id).innerHTML = data.subtotal;
        document.getElementById('total').innerHTML = data.total;
    });
}

// Lắng nghe thay đổi số lượng trực tiếp
document.querySelectorAll('input[id^="qty_"]').forEach(input => {
    input.addEventListener('change', () => {
        const id = input.id.replace('qty_', '');
        changeQty(id, 0);
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
