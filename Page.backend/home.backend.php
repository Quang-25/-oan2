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

    // 🔹 Kiểm tra đơn hàng tồn tại chưa
    $check = $conn->prepare("SELECT * FROM orders WHERE User_ID = :user_id AND Product_ID = :product_id");
    $check->execute([
        ':user_id' => $ID_user,
        ':product_id' => $product_id
    ]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // 🔁 Nếu có rồi thì cập nhật đơn
        $newQuantity = $existing['quantity'] + $quantity;
        $newTotal = $existing['totalamount'] + $totalamount;

        $update = $conn->prepare("
            UPDATE orders 
            SET quantity = :quantity, totalamount = :totalamount, order_date = :order_date
            WHERE orders_id = :id
        ");
        $update->execute([
            ':quantity' => $newQuantity,
            ':totalamount' => $newTotal,
            ':order_date' => $order_date,
            ':id' => $existing['orders_id']
        ]);

        $order_id = $existing['orders_id'];
    } else {
        // 🆕 Tạo đơn hàng mới
        $insert = $conn->prepare("
            INSERT INTO orders (quantity, totalamount, User_ID, Product_ID, order_date)
            VALUES (:quantity, :totalamount, :User_ID, :Product_ID, :order_date)
        ");
        $insert->execute([
            ':quantity' => $quantity,
            ':totalamount' => $totalamount,
            ':User_ID' => $ID_user,
            ':Product_ID' => $product_id,
            ':order_date' => $order_date
        ]);

        $order_id = $conn->lastInsertId();

    }

    // 🔹 Thêm chi tiết đơn hàng
    $subtotal = $price * $quantity;
    $checkDetail = $conn->prepare("SELECT * FROM order_details WHERE orders_id = :orders_id AND Product_ID = :product_id");
    $checkDetail->execute([
        ':orders_id' => $order_id,
        ':product_id' => $product_id
    ]);
    $detailExist = $checkDetail->fetch(PDO::FETCH_ASSOC);

    if ($detailExist) {
        // Cập nhật chi tiết sản phẩm trong order_details
        $newDetailQuantity = $detailExist['quantity'] + $quantity;
        $newSubtotal = $detailExist['subtotal'] + $totalamount;

        $updateDetail = $conn->prepare("UPDATE order_details 
                                        SET quantity = :quantity, subtotal = :subtotal 
                                        WHERE detail_id = :id");
        $updateDetail->execute([
            ':quantity' => $newDetailQuantity,
            ':subtotal' => $newSubtotal,
            ':id' => $detailExist['detail_id']
        ]);
    } else {
    $insertDetail = $conn->prepare("
        INSERT INTO order_details (orders_id, Product_ID, price, quantity, subtotal)
        VALUES (:orders_id, :Product_ID, :price, :quantity, :subtotal)
    ");
    $insertDetail->execute([
        ':orders_id' => $order_id,
        ':Product_ID' => $product_id,
        ':price' => $price,
        ':quantity' => $quantity,
        ':subtotal' => $subtotal
    ]);
    }
    // 🔹 Cập nhật giỏ hàng trong session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id_product'],
            'name' => $product['products_name'],
            'price' => $price,
            'image' => $product['images'] ?? $product['image1'],
            'quantity' => $quantity
        ];
    }

    echo json_encode(['success' => true, 'message' => '🛒 Đặt hàng thành công!']);
    exit;

    }
?>
