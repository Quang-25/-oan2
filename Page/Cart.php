<?php
session_start();
include("../config/db.php");

// ==========================================
// 🛒 THÊM SẢN PHẨM VÀO GIỎ HÀNG
// ==========================================
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        $imagePath = $product['images'] ?: ($product['image1'] ?: ($product['image2'] ?: '../images/no-image.jpg'));

        // ✅ Cập nhật giỏ hàng trong SESSION
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $product['id_product'],
                'name' => $product['products_name'],
                'price' => $product['price'],
                'image' => $imagePath,
                'quantity' => 1
            ];
        }

        // ✅ Cập nhật user_carts trong DB (nếu user đã đăng nhập)
        if (isset($_SESSION['ID_user'])) {
            $user_id = $_SESSION['ID_user'];
            $price = $product['price'];

            // Kiểm tra xem sản phẩm đã có trong giỏ chưa
            $check = $conn->prepare("SELECT * FROM user_carts WHERE User_ID = :uid AND Product_ID = :pid");
            $check->execute(['uid' => $user_id, 'pid' => $id]);

            if ($check->rowCount() > 0) {
                // Nếu có → tăng số lượng + tổng tiền
                $update = $conn->prepare("
                    UPDATE user_carts 
                    SET quantity = quantity + 1, 
                        totalamount = totalamount + :price
                    WHERE User_ID = :uid AND Product_ID = :pid
                ");
                $update->execute([
                    'price' => $price,
                    'uid' => $user_id,
                    'pid' => $id
                ]);
            } else {
                // Nếu chưa có → thêm mới
                $insert = $conn->prepare("
                    INSERT INTO user_carts (User_ID, Product_ID, quantity, totalamount, added_date)
                    VALUES (:uid, :pid, 1, :total, NOW())
                ");
                $insert->execute([
                    'uid' => $user_id,
                    'pid' => $id,
                    'total' => $price
                ]);
            }
        }

        $_SESSION['message'] = "✅ Đã thêm <b>{$product['products_name']}</b> vào giỏ hàng!";
    }

    header("Location: Cart.php");
    exit();
}

// ==========================================
// ❌ XÓA SẢN PHẨM KHỎI GIỎ
// ==========================================
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);

    // ✅ Xóa trong SESSION
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
        $_SESSION['message'] = "✅ Đã xóa sản phẩm khỏi giỏ hàng!";
    }

    // ✅ Xóa trong DB (nếu có user đăng nhập)
    if (isset($_SESSION['ID_user'])) {
        $stmtDel = $conn->prepare("
            DELETE FROM user_carts 
            WHERE User_ID = :uid AND Product_ID = :pid
        ");
        $stmtDel->execute([
            'uid' => $_SESSION['ID_user'],
            'pid' => $remove_id
        ]);
    }

    header("Location: Cart.php");
    exit();
}

// ==========================================
// 🔁 CẬP NHẬT SỐ LƯỢNG (AJAX)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $id = intval($_POST['id']);
    $qty = max(1, intval($_POST['qty']));

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] = $qty;
        $subtotal = $_SESSION['cart'][$id]['price'] * $qty;

        // ✅ Cập nhật DB nếu có user đăng nhập
        if (isset($_SESSION['ID_user'])) {
            $user_id = $_SESSION['ID_user'];
            $total = $_SESSION['cart'][$id]['price'] * $qty;

            $stmtUpd = $conn->prepare("
                UPDATE user_carts 
                SET quantity = :qty, totalamount = :total
                WHERE User_ID = :uid AND Product_ID = :pid
            ");
            $stmtUpd->execute([
                'qty' => $qty,
                'total' => $total,
                'uid' => $user_id,
                'pid' => $id
            ]);
        }

        // ✅ Tính lại tổng tiền giỏ hàng
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

// ==========================================
// 🔄 ĐỒNG BỘ LẠI GIỎ HÀNG SAU KHI LOGIN
// ==========================================
if (isset($_SESSION['ID_user']) && (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0)) {
    $stmt = $conn->prepare("
        SELECT uc.Product_ID, uc.quantity, p.products_name, p.price, p.images, p.image1, p.image2
        FROM user_carts uc
        JOIN products p ON uc.Product_ID = p.id_product
        WHERE uc.User_ID = :uid
    ");
    $stmt->execute(['uid' => $_SESSION['ID_user']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['cart'] = [];
    foreach ($cartItems as $item) {
        $img = $item['images'] ?: ($item['image1'] ?: ($item['image2'] ?: '../images/no-image.jpg'));
        $_SESSION['cart'][$item['Product_ID']] = [
            'id' => $item['Product_ID'],
            'name' => $item['products_name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => $img
        ];
    }
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

        .price,
        .subtotal {
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
                <?= $_SESSION['message'];
                unset($_SESSION['message']); ?>
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
                    <div class="col-md-1 subtotal" id="subtotal_<?= $item['id']; ?>"><?= number_format($subtotal, 0, ',', '.'); ?> đ</div>
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
                <a href="../Page/Product.php" class="btn btn-red2">XEM SẢN PHẨM</a>
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

            fetch('Cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById('subtotal_' + id).innerHTML = data.subtotal;
                    document.getElementById('total').innerHTML = data.total;
                });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php include(__DIR__ . "/../include/Footer.php"); ?>
</body>

</html>