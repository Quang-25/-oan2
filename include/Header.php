<?php
    require __DIR__ . "/../config/db.php";

     
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Giỏ Quà Tết Việt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- icon -->
  <link rel="stylesheet" href="../css/style.css">
</head>
<div class="container py-3">
  <div class="row align-items-center">
    <div class="col-md-3">
      <img src="https://demo037030.web30s.vn/datafiles/32835/upload/images/logo_gift.png?t=1752894235" alt="Logo" class="img-fluid">
    </div>
    <div class="col-md-4">
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Nhập từ khóa...">
        <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
      </div>
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
         <a href="../Page/Cart.php" class="text-decoration-none text-dark">
        <div>
          <i class="bi bi-cart service-icon"></i><br>
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

      <li class="nav-item"><a class="nav-link" href="#">TRANG CHỦ</a></li>
      <li class="nav-item"><a class="nav-link" href="#">GIỚI THIỆU</a></li>

      <!-- GIỎ QUÀ -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">GIỎ QUÀ</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">Từ 1 Triệu đến 2 Triệu</a></li>
          <li><a class="dropdown-item" href="#">Từ 700K đến 1 Triệu</a></li>
          <li><a class="dropdown-item" href="#">Từ 300K đến 700K</a></li>
        </ul>
      </li>

      <!-- HỘP QUÀ -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">HỘP QUÀ</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">Từ 1 Triệu đến 2 Triệu</a></li>
          <li><a class="dropdown-item" href="#">Từ 700k đến 1 Triệu</a></li>
          <li><a class="dropdown-item" href="#">Từ 300k đến 700k</a></li>
        </ul>
      </li>

      
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">TÚI QUÀ TẾT</a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">Từ 1 Triệu đến 2 Triệu</a></li>
          <li><a class="dropdown-item" href="#">Từ 700k đến 1 Triệu</a></li>
          <li><a class="dropdown-item" href="#">Từ 300k đến 700k</a></li>
        </ul>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

