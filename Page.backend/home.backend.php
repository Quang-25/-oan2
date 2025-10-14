<?php
include __DIR__ . "/../config/db.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['action']) 
    && $_POST['action'] === 'orderNow') {

    $product_id = intval($_POST['product_id']);
    $ID_user = $_SESSION['ID_user'] ?? 1;
    $quantity = 1;
    $order_date = date('Y-m-d H:i:s');

    // 🔹 Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => '❌ Không tìm thấy sản phẩm!']);
        exit;
    }

    $price = $product['price'];
    $totalamount = $price * $quantity;

    // ❌ Bỏ phần lưu vào bảng orders và order_details (chỉ dùng session)

    // 🔹 Cập nhật giỏ hàng trong session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        // Nếu sản phẩm đã có trong giỏ thì tăng số lượng
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // Nếu chưa có thì thêm mới
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id_product'],
            'name' => $product['products_name'],
            'price' => $price,
            // 🔹 Giữ cả 2 ảnh (images + image1)
            'images' => $product['images'],
            'image1' => $product['image1'],
            'quantity' => $quantity
        ];
    }

    echo json_encode(['success' => true, 'message' => '🛒 Đặt hàng thành công!']);
    exit;
}
?>
