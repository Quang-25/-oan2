<?php
include __DIR__ . "/../config/db.php";
session_start();

// ===== KIỂM TRA ĐĂNG NHẬP =====
if (!isset($_SESSION['ID_user'])) {
    echo json_encode(['success' => false, 'message' => '⚠️ Vui lòng đăng nhập trước khi thêm sản phẩm!']);
    exit;
}

$User_ID = $_SESSION['ID_user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'orderNow') {
    $product_id = intval($_POST['product_id']);
    $quantity = 1;

    // 🔹 Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => '❌ Không tìm thấy sản phẩm!']);
        exit;
    }
  if ($product['quantitySold'] >= $product['totalquantity']) {
        echo json_encode(['success' => false, 'message' => '❌ Sản phẩm đã hết hàng, không thể thêm vào giỏ!']);
        exit;
    }
    // 🔹 Chọn ảnh hợp lệ
    $imagePath = $product['images'] ?: ($product['image1'] ?: ($product['image2'] ?: '../images/no-image.jpg'));

    // 🔹 Khởi tạo giỏ hàng
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // 🔹 Thêm hoặc cập nhật sản phẩm
    if (isset($_SESSION['cart'][$product_id])) {
        // Nếu sản phẩm đã tồn tại, chỉ tăng số lượng
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // Nếu sản phẩm mới, thêm mới vào giỏ
        $_SESSION['cart'][$product_id] = [
            'id'       => $product['id_product'],
            'name'     => $product['products_name'],
            'price'    => $product['price'],
            'image'    => $imagePath,
            'quantity' => $quantity
        ];
    }

    // ✅ Chỉ đếm số LOẠI sản phẩm (không tính tổng quantity)
    $cart_count = count($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'message' => '🛒 Đã thêm sản phẩm vào giỏ hàng!',
        'cart_count' => $cart_count
    ]);
    exit;
}

?>
