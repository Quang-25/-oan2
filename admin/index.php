<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
$admin = $_SESSION['admin'];

// Lấy trang cần load qua query string, mặc định là Dashboard.php
$page = isset($_GET['page']) ? $_GET['page'] : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #fef2f2; margin: 0; }
        .main-wrapper { display: flex; height: 100vh; }
        .sidebar { width: 260px; background: #b91c1c; color: white; padding: 20px; display: flex; flex-direction: column; }
        .sidebar-nav a { color: #fff; display: block; padding: 10px 14px; border-radius: 8px; margin: 8px 0; text-decoration: none; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: #ef4444; }
        .content { flex: 1; padding: 20px; background: #fff; overflow-y: auto; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>🛠 Trang Admin</h4>
            <p>Xin chào, <b><?= htmlspecialchars($admin['Name']) ?></b></p>
        </div>
        <nav class="sidebar-nav">
            <a href="?page=Dashboard" class="<?= $page == 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="?page=products" class="<?= $page == 'products' ? 'active' : '' ?>"><i class="fa-solid fa-box"></i> Sản phẩm</a>
            <a href="?page=statistics" class="<?= $page == 'statistics' ? 'active' : '' ?>"><i class="fa-solid fa-pie-chart"></i> Thống Kê</a>
            <a href="?page=User" class="<?= $page == 'User' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Người dùng</a>
            <a href="?page=oders" class="<?= $page == 'oders' ? 'active' : '' ?>"><i class="fa-solid fa-cart-shopping"></i> Đơn hàng</a>
        </nav>
        <div class="sidebar-footer mt-auto">
            <a href="logout.php" class="btn btn-light w-100"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
        </div>
    </div>

    <main class="content">
        <?php
        // Hiển thị trang nội dung tương ứng
        $file = $page . ".php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<p>❌ Trang không tồn tại.</p>";
        }
        ?>
    </main>
</div>

</body>
</html>
