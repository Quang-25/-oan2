<?php
session_start();
include("../config/db.php");

// ===============================
// 🛒 THÊM SẢN PHẨM VÀO GIỎ HÀNG
// ===============================
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id_product = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        // ✅ Ưu tiên chọn ảnh hiển thị
        $imagePath = $product['images'] ?: ($product['image1'] ?: ($product['image2'] ?: '../images/no-image.jpg'));

        // === Lưu trong session ===
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $product['id_product'],
                'name' => $product['products_name'],
                'price' => $product['price'],
                'image' => $imagePath,
                'quantity' => 1
            ];
        }

        // === Lưu tạm vào bảng cart (nếu đã đăng nhập) ===
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $stmtCheck = $conn->prepare("SELECT * FROM cart WHERE user_id = :uid AND product_id = :pid");
            $stmtCheck->execute(['uid' => $user_id, 'pid' => $id]);
            $cartItem = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($cartItem) {
                $stmtUpdate = $conn->prepare("UPDATE cart 
                    SET quantity = quantity + 1, subtotal = price * (quantity + 1)
                    WHERE user_id = :uid AND product_id = :pid");
                $stmtUpdate->execute(['uid' => $user_id, 'pid' => $id]);
            } else {
                $stmtInsert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, price, subtotal, image)
                    VALUES (:uid, :pid, 1, :price, :subtotal, :image)");
                $stmtInsert->execute([
                    'uid' => $user_id,
                    'pid' => $id,
                    'price' => $product['price'],
                    'subtotal' => $product['price'],
                    'image' => $imagePath
                ]);
            }
        }

        $_SESSION['message'] = "✅ Đã thêm sản phẩm <b>{$product['products_name']}</b> vào giỏ hàng!";
    }
    header("Location: Cart.php");
    exit();
}

// ===============================
// ❌ XOÁ SẢN PHẨM
// ===============================
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
        $_SESSION['message'] = "✅ Đã xóa sản phẩm khỏi giỏ hàng!";

        // Xóa trong bảng cart nếu có user đăng nhập
        if (isset($_SESSION['user_id'])) {
            $stmtDel = $conn->prepare("DELETE FROM cart WHERE user_id = :uid AND product_id = :pid");
            $stmtDel->execute(['uid' => $_SESSION['user_id'], 'pid' => $remove_id]);
        }
    }
    header("Location: Cart.php");
    exit();
}

// ===============================
// 🔁 CẬP NHẬT SỐ LƯỢNG (AJAX)
// ===============================
if (isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $id = intval($_POST['id']);
    $qty = max(1, intval($_POST['qty']));

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] = $qty;
        $subtotal = $_SESSION['cart'][$id]['price'] * $qty;

        // Cập nhật trong DB nếu có user đăng nhập
        if (isset($_SESSION['user_id'])) {
            $stmtUpd = $conn->prepare("UPDATE cart 
                SET quantity = :qty, subtotal = :subtotal 
                WHERE user_id = :uid AND product_id = :pid");
            $stmtUpd->execute([
                'qty' => $qty,
                'subtotal' => $subtotal,
                'uid' => $_SESSION['user_id'],
                'pid' => $id
            ]);
        }

        // Tính tổng
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

// ===============================
// 🔄 ĐỒNG BỘ LẠI GIỎ HÀNG TỪ DATABASE (khi login)
// ===============================
if (isset($_SESSION['user_id']) && (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0)) {
    $stmt = $conn->prepare("SELECT c.*, p.products_name 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id_product
                            WHERE c.user_id = :uid");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['cart'] = [];
    foreach ($cartItems as $item) {
        $_SESSION['cart'][$item['product_id']] = [
            'id' => $item['product_id'],
            'name' => $item['products_name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => $item['image']
        ];
    }
}

// ===============================
// 💰 TÍNH TỔNG TIỀN
// ===============================
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
.cart-header {
    background-color: #e6e6e6;
    padding: 40px 0;
    text-align: center;
}
.cart-header h2 {
    color: #b30000;
    font-weight: 700;
    margin: 0;
}
.cart-header div {
    margin-top: 5px;
    font-size: 15px;
}
.cart-header span {
    color: #c50000;
}
.product-img2 {
    width: 130px;
    height: 130px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}
.price, .subtotal {
    color: #b30000;
    font-weight: 600;
}
a.btn-red2 {
    background-color: #b30000;
    color: #fff;
    border: none;
    font-weight: 600;
    padding: 8px 20px;
}
a.btn-red2:hover {
    background-color: #900000;
}
.btn-outline-danger i {
    font-size: 18px;
}
.total-row {
    border-top: 2px solid #b30000;
    margin-top: 30px;
    padding-top: 15px;
    text-align: right;
    font-size: 18px;
    font-weight: 600;
}
.alert-success {
    background-color: #eaffea;
    color: #155724;
    border: none;
}
.input-group-sm .btn {
    padding: 0 8px;
}
.text-muted {
    font-size: 16px;
}
</style>
</head>
<body>

<?php include(__DIR__ . "/../include/Header.php"); ?>

<div class="cart-header">
    <h2>GIỎ HÀNG</h2>
    <div>Trang chủ / <span>Giỏ hàng</span></div>
</div>

<div class="container my-4">

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-success text-center" id="autoAlert">
    <?= $_SESSION['message']; unset($_SESSION['message']); ?>
</div>
<script>
setTimeout(() => {
    const alert = document.getElementById('autoAlert');
    if (alert) alert.remove();
}, 1200);
</script>
<?php endif; ?>

<?php if (!empty($_SESSION['cart'])): ?>

<?php foreach ($_SESSION['cart'] as $item): 
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;

    // ✅ Chọn ảnh hiển thị đúng (images → image1 → image2)
    $imgPath = $item['image'] ?? '../images/no-image.jpg';
    if (empty(trim($imgPath))) $imgPath = "../images/no-image.jpg";
?>

<div class="row align-items-center border-bottom py-3">
    <div class="col-md-2 text-center">
        <img src="<?= htmlspecialchars($imgPath); ?>" class="product-img2" alt="Ảnh sản phẩm" onerror="this.src='../images/no-image.jpg'">
    </div>
    <div class="col-md-4 fw-semibold"><?= htmlspecialchars($item['name']); ?></div>
    <div class="col-md-2 price"><?= number_format($item['price'], 0, ',', '.'); ?> đ</div>
    <div class="col-md-2">
        <div class="input-group input-group-sm justify-content-center">
            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(<?= $item['id']; ?>, -1)">−</button>
            <input type="number" class="form-control text-center" id="qty_<?= $item['id']; ?>" value="<?= $item['quantity']; ?>" min="1" style="width:50px;">
            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(<?= $item['id']; ?>, 1)">+</button>
        </div>
    </div>
    <div class="col-md-1 subtotal" id="subtotal_<?= $item['id']; ?>"><?= number_format($subtotal,0,',','.'); ?> đ</div>
    <div class="col-md-1 text-center">
        <a href="Cart.php?remove=<?= $item['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></a>
    </div>
</div>

<?php endforeach; ?>

<div class="total-row">
    Tổng tiền: <span id="total"><?= number_format($total, 0, ',', '.'); ?> đ</span>
</div>

<div class="text-end mt-3">
    <a href="../Page/Product.php" class="btn btn-red2 me-2">XEM THÊM SẢN PHẨM</a>
    <a href="../Page/payment.php" class="btn btn-red2">THANH TOÁN</a>
</div>

<?php else: ?>
<div class="text-center py-5 text-muted">
    🛒 Giỏ hàng trống!<br><br>
    <a href="../index.php" class="btn btn-red2">XEM SẢN PHẨM</a>
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
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
