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

    //  Kiểm tra tồn kho
    if ($product['quantitySold'] >= $product['totalquantity']) {
        echo json_encode(['success' => false, 'message' => '❌ Sản phẩm đã hết hàng, không thể thêm vào giỏ!']);
        exit;
    }

    // 🔹 Chọn ảnh hợp lệ
    $imagePath = $product['images'] ?: ($product['image1'] ?: ($product['image2'] ?: '../images/no-image.jpg'));

    // ==============================
    // PHẦN 1: Lưu vào SESSION
    // ==============================
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

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

    //  Lưu vào DATABASE (bảng user_carts)
    //Kiểm tra xem sản phẩm này đã có trong giỏ của user chưa
    $check = $conn->prepare("SELECT * FROM user_carts WHERE User_ID = :uid AND Product_ID = :pid");
    $check->execute(['uid' => $User_ID, 'pid' => $product_id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    // Tính tổng tiền của sản phẩm này
    $price = $product['price'];
    $totalToAdd = $price * $quantity_to_add;

    if ($existing) {
        // Nếu đã có → cập nhật số lượng và tổng tiền
        $newQty = $existing['quantity'] + $quantity_to_add;
        $newTotal = $existing['totalamount'] + $totalToAdd;

        $update = $conn->prepare("
            UPDATE user_carts 
            SET quantity = :qty, totalamount = :total 
            WHERE User_ID = :uid AND Product_ID = :pid
        ");
        $update->execute([
            'qty'   => $newQty,
            'total' => $newTotal,
            'uid'   => $User_ID,
            'pid'   => $product_id
        ]);
    } else {
        // Nếu chưa có → thêm mới
        $insert = $conn->prepare("
            INSERT INTO user_carts (quantity, totalamount, User_ID, Product_ID, order_date)
            VALUES (:qty, :total, :uid, :pid, NOW())
        ");
        $insert->execute([
            'qty'   => $quantity_to_add,
            'total' => $totalToAdd,
            'uid'   => $User_ID,
            'pid'   => $product_id
        ]);
    }

    // Đếm số loại sản phẩm trong session
    $cart_count = count($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'message' => '🛒 Đã thêm sản phẩm vào giỏ hàng!',
        'cart_count' => $cart_count
    ]);
    exit;
}
?>
