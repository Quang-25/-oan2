<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../config/db.php";

// Lấy ID user nếu đã đăng nhập
$User_ID = $_SESSION['ID_user'] ?? null;

// ==== Lấy danh mục sản phẩm ====
$categories = $conn->query("SELECT DISTINCT category FROM products")->fetchAll(PDO::FETCH_COLUMN);

// ==== Xử lý thêm sản phẩm vào giỏ hàng ====
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

    // Tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // --- Xác định xem đây là SP mới hay đã có trong giỏ ---
    $is_new_product = !isset($_SESSION['cart'][$product_id]);

    // --- Nếu đã có -> chỉ tăng số lượng, KHÔNG tăng count icon ---
    if (!$is_new_product) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // --- Nếu chưa có -> thêm mới ---
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id_product'],
            'name' => $product['products_name'],
            'price' => $product['price'],
            'images' => $product['images'],
            'image1' => $product['image1'],
            'quantity' => $quantity
        ];
    }

    // --- Cập nhật tổng tiền ---
    $current_qty = $_SESSION['cart'][$product_id]['quantity'];
    $totalamount = $product['price'] * $current_qty;

    // --- Kiểm tra orders trong DB ---
    if ($User_ID) {
        $check = $conn->prepare("SELECT * FROM orders WHERE User_ID = :uid AND Product_ID = :pid");
        $check->execute(['uid' => $User_ID, 'pid' => $product_id]);

        if ($check->rowCount() > 0) {
            // Cập nhật nếu đã có
            $update = $conn->prepare("
                UPDATE orders 
                SET quantity = :qty, totalamount = :total, order_date = NOW()
                WHERE User_ID = :uid AND Product_ID = :pid
            ");
            $update->execute([
                'qty' => $current_qty,
                'total' => $totalamount,
                'uid' => $User_ID,
                'pid' => $product_id
            ]);
        } else {
            // Thêm mới nếu chưa có
            $insert = $conn->prepare("
                INSERT INTO orders (quantity, totalamount, User_ID, Product_ID, order_date, payment_method)
                VALUES (:qty, :total, :uid, :pid, NOW(), 'COD')
            ");
            $insert->execute([
                'qty' => $current_qty,
                'total' => $totalamount,
                'uid' => $User_ID,
                'pid' => $product_id
            ]);
        }
    }

    // --- Cập nhật số sản phẩm hiển thị ở icon ---
    // Nếu là sản phẩm mới -> +1, nếu cũ -> giữ nguyên
    $cart_count = count($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'message' => '🛒 Đã thêm sản phẩm vào giỏ hàng!',
        'cart_count' => $cart_count
    ]);
    exit;
}

// ================== XỬ LÝ LỌC / TÌM KIẾM / SẮP XẾP ==================
$category = $_GET['category'] ?? '';
$priceRange = $_GET['price'] ?? '';
$sort = $_GET['sort'] ?? 'default';
$keyword = trim($_GET['keyword'] ?? '');

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

// Tìm kiếm theo từ khóa
if (!empty($keyword)) {
    $sql .= " AND products_name LIKE :keyword";
    $params['keyword'] = '%' . $keyword . '%';
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

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
