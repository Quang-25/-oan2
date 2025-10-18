<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../config/db.php";

// 🔹 Lấy danh mục sản phẩm (để hiện sidebar)
$categories = $conn->query("SELECT DISTINCT category FROM products")->fetchAll(PDO::FETCH_COLUMN);

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

// ================== XỬ LÝ LỌC & HIỂN THỊ SẢN PHẨM ==================
$category = $_GET['category'] ?? '';
$priceRange = $_GET['price'] ?? '';
$sort = $_GET['sort'] ?? 'default';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// Lọc theo danh mục
if (!empty($category)) {
    $sql .= " AND category = :cat";
    $params['cat'] = $category;
}

// Lọc theo khoảng giá
switch ($priceRange) {
    case '300-700':
        $sql .= " AND price BETWEEN 300000 AND 800000";
        break;
    case '700-1000':
        $sql .= " AND price BETWEEN 700000 AND 2000000";
        break;
    case '1000-2000':
        $sql .= " AND price BETWEEN 1000000 AND 3000000";
        break;
}

// Sắp xếp
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    default:
        $sql .= " ORDER BY id_product ASC";
        break;
}

// Thực thi
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
