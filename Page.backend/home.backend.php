<?php
include __DIR__ . "/../config/db.php";
session_start();

// ===== KIỂM TRA ĐĂNG NHẬP =====
if (!isset($_SESSION['ID_user'])) {
    echo json_encode(['success' => false, 'message' => '⚠️ Vui lòng đăng nhập trước khi thêm sản phẩm!']);
    exit;
}

$User_ID = $_SESSION['ID_user'];

// ================== XỬ LÝ POST: THÊM VÀO GIỎ HÀNG ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'orderNow') {

    $product_id = intval($_POST['product_id']);
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => '❌ Sản phẩm không hợp lệ!']);
        exit;
    }

    // --- LẤY THÔNG TIN SẢN PHẨM ---
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => '❌ Không tìm thấy sản phẩm!']);
        exit;
    }

    // --- KHỞI TẠO GIỎ HÀNG ---
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // --- MẶC ĐỊNH MỖI LẦN BẤM LÀ 1 SẢN PHẨM ---
    $quantity_add = 1;

    // --- XỬ LÝ SESSION ---
    if (!isset($_SESSION['cart'][$product_id])) {
        // 👉 Nếu là sản phẩm MỚI, thêm vào giỏ
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id_product'],
            'name' => $product['products_name'],
            'price' => $product['price'],
            'images' => $product['images'],
            'image1' => $product['image1'],
            'quantity' => $quantity_add
        ];
        $increase_cart_icon = true; // tăng icon vì là sản phẩm mới
    } else {
        // 👉 Nếu sản phẩm đã có trong giỏ
        $_SESSION['cart'][$product_id]['quantity'] += $quantity_add;
        $increase_cart_icon = false; // không tăng icon vì đã tồn tại
    }

    // --- TÍNH LẠI TỔNG TIỀN ---
    $quantity = $_SESSION['cart'][$product_id]['quantity'];
    $totalamount = $product['price'] * $quantity;

    // --- CẬP NHẬT / THÊM TRONG BẢNG ORDERS ---
    $check = $conn->prepare("SELECT * FROM orders WHERE User_ID = :uid AND Product_ID = :pid");
    $check->execute(['uid' => $User_ID, 'pid' => $product_id]);

    if ($check->rowCount() > 0) {
        // 👉 Cập nhật số lượng và tổng tiền
        $update = $conn->prepare("
            UPDATE orders 
            SET quantity = quantity + :add_qty, 
                totalamount = totalamount + :add_total,
                order_date = NOW()
            WHERE User_ID = :uid AND Product_ID = :pid
        ");
        $update->execute([
            'add_qty' => $quantity_add,
            'add_total' => $product['price'] * $quantity_add,
            'uid' => $User_ID,
            'pid' => $product_id
        ]);
    } else {
        // 👉 Thêm mới
        $insert = $conn->prepare("
            INSERT INTO orders (quantity, totalamount, User_ID, Product_ID, order_date, payment_method)
            VALUES (:qty, :total, :uid, :pid, NOW(), 'COD')
        ");
        $insert->execute([
            'qty' => $quantity_add,
            'total' => $product['price'] * $quantity_add,
            'uid' => $User_ID,
            'pid' => $product_id
        ]);
    }

    // --- TÍNH LẠI ICON GIỎ HÀNG ---
    $cart_count = $increase_cart_icon 
        ? array_sum(array_column($_SESSION['cart'], 'quantity'))
        : count($_SESSION['cart']); // nếu chỉ đặt lại sp cũ -> icon giữ nguyên

    // --- PHẢN HỒI ---
    echo json_encode([
        'success' => true,
        'message' => '🛒 Đã thêm sản phẩm vào đơn hàng!',
        'cart_count' => $cart_count
    ]);
    exit;
}
?>
