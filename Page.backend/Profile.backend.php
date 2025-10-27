<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['ID_user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['ID_user'];
$msg = "";

// ======= Cập nhật thông tin cá nhân =======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if ($name && $email) {
        $stmt = $conn->prepare("UPDATE users SET Name=?, Email=?, Address=?, Phone=? WHERE ID_user=?");
        $stmt->execute([$name, $email, $address, $phone, $userId]);
        $_SESSION['name'] = $name;
        $_SESSION['msg'] = "✅ Cập nhật thông tin thành công!";
    } else {
        $_SESSION['msg'] = "⚠️ Vui lòng nhập đầy đủ thông tin!";
    }

    // Redirect để tránh trình duyệt hỏi lại khi refresh
    header("Location: Profile.php");
    exit;
}

// ======= Hiển thị thông báo sau khi redirect =======
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// ======= Lấy thông tin người dùng =======
$stmt = $conn->prepare("SELECT * FROM users WHERE ID_user = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ======= Lấy lịch sử đơn hàng =======
$orderQuery = $conn->prepare("
    SELECT orders_id, quantity, totalamount, User_ID, Product_ID, order_date, payment_method
    FROM orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC
");
$orderQuery->execute([$userId]);
$orders = $orderQuery->fetchAll(PDO::FETCH_ASSOC);
?>