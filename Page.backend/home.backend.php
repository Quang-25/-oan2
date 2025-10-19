<?php
include __DIR__ . "/../config/db.php";

session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../config/db.php";


// ================== XỬ LÝ POST: THÊM VÀO GIỎ HÀNG ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'orderNow') {
    $product_id = intval($_POST['product_id']);
    $quantity = 1;

    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => '❌ Không tìm thấy sản phẩm!']);
        exit;
    }

    // Cập nhật giỏ hàng trong session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id_product'],
            'name' => $product['products_name'],
            'price' => $product['price'],
            'images' => $product['images'],
            'image1' => $product['image1'],
            'quantity' => $quantity
        ];
    }

    // Đếm tổng số lượng sản phẩm trong giỏ
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));

    echo json_encode([
        'success' => true,
        'message' => '🛒 Đã thêm sản phẩm vào giỏ hàng!',
        'cart_count' => $cart_count
    ]);
    exit;
}

// Thực thi
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


