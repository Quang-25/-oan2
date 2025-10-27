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

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Tài khoản của tôi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.wrapper {
   display: flex;
   width: 100%;
   margin: 0 auto;
   background: white;
   min-height: 80vh; 
}
.sidebar {
   width: 20%;
   min-width: 250px;
   background: #d32f2f;
   color: white;
   padding: 25px 0;
}
.sidebar h3 {
   text-align: center;
   font-weight: 700;
   margin-bottom: 30px;
}
.sidebar a {
   display: block;
   color: white;
   text-decoration: none;
   padding: 12px 25px;
   font-weight: 500;
   transition: background 0.2s;
}
.sidebar a:hover, .sidebar a.active {
   background: #dc2626;
}
.content {
   flex: 1;
   padding: 30px;
}
h2 {
   color: #b91c1c;
   font-weight: 700;
   margin-bottom: 20px;
}
.msg {
   color: #b91c1c;
   font-weight: 500;
   animation: fadeOut 2s ease-in 3s forwards;
}
@keyframes fadeOut {
   to { opacity: 0; visibility: hidden; }
}
.table th {
   background: #dc2626;
   color: white;
}
.table td {
   vertical-align: middle;
}
.btn-danger {
   background: #b91c1c;
   border: none;
}
.btn-danger:hover {
   background: #dc2626;
}
</style>
</head>
<body>
<?php include(__DIR__ . "/../include/Header.php"); ?>

<div class="wrapper">
   <div class="sidebar">
       <h3>Tài khoản</h3>
       <a href="#info" class="active">Thông tin cá nhân</a>
       <a href="#orders">Đơn hàng đã mua</a>
   </div>

   <div class="content">
       <section id="info">
           <h2>Thông tin cá nhân</h2>
           <?php if ($msg): ?>
               <p class="msg"><?= htmlspecialchars($msg) ?></p>
           <?php endif; ?>

           <form method="POST">
               <div class="mb-3">
                   <label class="form-label">Họ tên</label>
                   <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['Name']) ?>">
               </div>
               <div class="mb-3">
                   <label class="form-label">Email</label>
                   <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['Email']) ?>">
               </div>
               <div class="mb-3">
                   <label class="form-label">Địa chỉ</label>
                   <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['Address']) ?>">
               </div>
               <div class="mb-3">
                   <label class="form-label">Số điện thoại</label>
                   <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['Phone']) ?>">
               </div>
               <button type="submit" name="update_info" class="btn btn-danger"> Cập nhật</button>
           </form>
       </section>

       <hr class="my-4">

       <section id="orders">
           <h2>Lịch sử mua hàng</h2>
           <table class="table table-bordered">
               <thead>
                   <tr>
                       <th>Mã đơn</th>
                       <th>Ngày đặt</th>
                       <th>Tổng tiền</th>
                       <th>Trạng thái</th>
                   </tr>
               </thead>
               <tbody>
                   <?php if (count($orders) > 0): ?>
                       <?php foreach ($orders as $order): ?>
                           <tr>
                               <td>#<?= $order['orders_id'] ?></td>
                               <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                               <td><?= number_format($order['totalamount'], 0, ',', '.') ?>₫</td>
                               <td>
                                   <?php
                                   switch ($order['payment_method']) {
                                       case 'cod': echo "Thanh toán khi nhận hàng"; break;
                                       case 'online': echo "Chuyển khoản ngân hàng"; break;
                                       default: echo htmlspecialchars($order['payment_method']);
                                   }
                                   ?>
                               </td>
                           </tr>
                       <?php endforeach; ?>
                   <?php else: ?>
                       <tr><td colspan="4" class="text-center text-muted">Chưa có đơn hàng nào.</td></tr>
                   <?php endif; ?>
               </tbody>
           </table>
       </section>
   </div>
</div>

<?php include(__DIR__ . "/../include/Footer.php"); ?>
</body>
</html>
