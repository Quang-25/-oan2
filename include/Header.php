<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Giỏ Quà Tết Việt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- icon -->
  <link rel="stylesheet" href="../css/header.css">
</head>
<!-- Top Bar -->
<div class="topbar py-1">
  <div class="container d-flex justify-content-between align-items-center flex-wrap">

    <!-- Bên trái -->
    <div class="d-flex align-items-center flex-wrap">
      <span class="me-3">
        <i class="bi-house-door-fill"></i>
        Số 16, Phố Hữu Nghị, Phường Tùng Thiện, Thành Phố Hà Nội
      </span>
      <span>
        <i class="bi bi-envelope-fill"></i>
        Đại Học Công Nghiệp Việt Hung
      </span>
    </div>

    <!-- Bên phải -->
    <div class="d-flex align-items-center flex-wrap mt-1 mt-md-0">
      <a href="../Page/login.php" class="top-link me-3">Đăng nhập</a>
      <a href="../Page/register.php" class="top-link me-3">Đăng ký</a>
      <a href="../Page/logout.php" class="top-link me-3">Đăng xuất</a>
      <a href="#" class="top-link me-3">Yêu thích</a>
      <img src="https://cf.shopee.vn/file/1b135b8d27f4fa192b801d2e576cbb67" alt="VN" class="me-2 rounded">
      <img src="https://media.viu.edu.vn/Media/2_TSVIU/FolderFunc/202203/Images/logo-20220317084549-e.png" alt="Viu" class="rounded">
    </div>
  </div>
</div>
<div class="container py-3">
  <div class="row align-items-center">
    <div class="col-md-3">
      <img src="https://demo037030.web30s.vn/datafiles/32835/upload/images/logo_gift.png?t=1752894235" alt="Logo" class="img-fluid">
    </div>
    <div class="col-md-4">
  <!-- Form tìm kiếm -->
  <form method="GET" action="../Page/Product.php">
    <div class="input-group">
      <input
        type="text"
        name="keyword"
        value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>"
        class="form-control"
        placeholder="Nhập từ khóa...">
      <button class="btn btn-outline-secondary" type="submit">
        <i class="bi bi-search"></i>
      </button>
    </div>
  </form>
      <?php // đảm bảo đã kết nối PDO
      $keyword = '';
      if (isset($_GET['keyword'])) {
        $keyword = trim($_GET['keyword']); // loại bỏ khoảng trắng đầu/cuối
      }

      try {
        if ($keyword != '') {
          $sql = "SELECT * FROM products WHERE products_name LIKE :keyword";
          $stmt = $conn->prepare($sql);
          $stmt->bindValue(':keyword', '%' . $keyword . '%');
        } else {
          $sql = "SELECT * FROM products";
          $stmt = $conn->prepare($sql);
        }

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
        echo "Lỗi truy vấn: " . $e->getMessage();
      }
      ?>
    </div>

    <!-- Dịch vụ -->
    <div class="col-md-3 text-center">
      <div class="d-flex justify-content-around">
        <div>
          <i class="bi bi-basket service-icon"></i><br>
          <small>Mua sắm dễ dàng</small>
        </div>
        <div>
          <i class="bi bi-truck service-icon"></i><br>
          <small>Giao hàng nhanh</small>
        </div>
        
          <div>
            <a href="../Page/Cart.php" class="position-relative text-decoration-none text-dark">
            <i class="bi bi-cart service-icon"></i><br>
            <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
           </span>
          </a>
            <small>Giỏ hàng</small>
          </div>
        </a>
      </div>
    </div>

    <!-- Hotline -->
    <div class="col-md-2 text-end">
      <small>Hotline miễn phí</small><br>
      <span class="hotline">1900 9477</span>
    </div>
  </div>
</div>

<!-- Menu dưới -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid justify-content-center">
    <ul class="navbar-nav">

      <li class="nav-item"><a class="nav-link" href="../Page/Home.php">TRANG CHỦ</a></li>
      <li class="nav-item"><a class="nav-link" href="#">GIỚI THIỆU</a></li>

      <!-- GIỎ QUÀ -->
      <!-- GIỎ QUÀ -->
<li class="nav-item dropdown">
  <div class="d-flex align-items-center">
    <a class="nav-link" href="../Page/Product.php">GIỎ QUÀ</a>
    <a class="nav-link dropdown-toggle ps-1" href="#" data-bs-toggle="dropdown"></a>
  </div>
</li>

<!-- HỘP QUÀ -->
<li class="nav-item dropdown">
  <div class="d-flex align-items-center">
    <a class="nav-link" href="../Page/Product.php">HỘP QUÀ</a>
    <a class="nav-link dropdown-toggle ps-1" href="#" data-bs-toggle="dropdown"></a>
  </div>
</li>

<!-- TÚI QUÀ -->
<li class="nav-item dropdown">
  <div class="d-flex align-items-center">
    <a class="nav-link" href="../Page/Product.php">TÚI QUÀ</a>
    <a class="nav-link dropdown-toggle ps-1" href="#" data-bs-toggle="dropdown"></a>
  </div>
</li>
      <li class="nav-item"><a class="nav-link" href="#">KHUYẾN MÃI</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">DỊCH VỤ</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">Gói quà và giỏ quà theo yêu cầu</a></li>
          <li><a class="dropdown-item" href="#">Thiết kế giỏ quà tết theo yêu cầu, ý tưởng khách hàng</a></li>
          <li><a class="dropdown-item" href="#">Vận chuyển giao quà tết tận nhà</a></li>
        </ul>
      </li>

      <!-- TIN TỨC -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">TIN TỨC</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">Tin thị trường</a></li>
          <li><a class="dropdown-item" href="#">Mẹo chọn quà</a></li>
        </ul>
      </li>

      <li class="nav-item"><a class="nav-link" href="#">THƯ VIỆN ẢNH</a></li>
      <li class="nav-item"><a class="nav-link" href="#">VIDEO</a></li>
      <li class="nav-item"><a class="nav-link" href="#">LIÊN HỆ</a></li>
    </ul>
  </div>
</nav>
</nav>
</html>



<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>