<?php
include __DIR__ . "/../config/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy ID user nếu đã đăng nhập
$User_ID = $_SESSION['ID_user'] ?? null;

// ==== Lấy danh mục sản phẩm ====
$categories = $conn->query("SELECT DISTINCT category FROM products")->fetchAll(PDO::FETCH_COLUMN);

// ==== Xử lý thêm sản phẩm vào giỏ hàng ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'orderNow') {
    $product_id = intval($_POST['product_id']);
    $quantity_to_add = intval($_POST['quantity'] ?? 1);

    if ($quantity_to_add <= 0) {
        echo json_encode(['success' => false, 'message' => '❌ Số lượng không hợp lệ!']);
        exit;
    }

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

    // 🔹 Khởi tạo giỏ hàng trong session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // 🔹 Thêm hoặc cập nhật sản phẩm trong SESSION
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity_to_add;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id'       => $product['id_product'],
            'name'     => $product['products_name'],
            'price'    => $product['price'],
            'image'    => $imagePath,
            'quantity' => $quantity_to_add
        ];
    }

    // ✅ Nếu user đã đăng nhập → lưu tạm giỏ hàng vào bảng `user_carts`
    if ($User_ID) {
        try {
            // Kiểm tra sản phẩm đã có trong bảng user_carts chưa
            $check = $conn->prepare("SELECT quantity FROM user_carts WHERE user_id = :user_id AND product_id = :product_id");
            $check->execute([
                ':user_id' => $User_ID,
                ':product_id' => $product_id
            ]);

            if ($check->rowCount() > 0) {
                // Nếu đã có thì cập nhật số lượng
                $update = $conn->prepare("UPDATE user_carts 
                                          SET quantity = quantity + :qty 
                                          WHERE user_id = :user_id AND product_id = :product_id");
                $update->execute([
                    ':qty' => $quantity_to_add,
                    ':user_id' => $User_ID,
                    ':product_id' => $product_id
                ]);
            } else {
                // Nếu chưa có thì thêm mới
                $insert = $conn->prepare("INSERT INTO user_carts (user_id, product_id, quantity)
                                          VALUES (:user_id, :product_id, :quantity)");
                $insert->execute([
                    ':user_id' => $User_ID,
                    ':product_id' => $product_id,
                    ':quantity' => $quantity_to_add
                ]);
            }
        } catch (Exception $e) {
            error_log("Lỗi lưu giỏ hàng tạm: " . $e->getMessage());
        }
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
