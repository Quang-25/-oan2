<?php
session_start();
require_once __DIR__ . "/../config/db.php"; // kết nối PDO $con

?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Giỏ quà</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include __DIR__ . "/../include/Header.php"; ?>
<div class="container mt-4">
  <div class="row">
    <!-- Cột trái: sidebar -->
    <div class="col-md-3">
      <?php include('../include/sidebar.php'); ?>
    </div>

    <!-- Cột phải: danh sách sản phẩm -->
    <div class="col-md-9">
      <h2 class="mb-4">Giỏ quà</h2>
      <div class="row">
        <?php
        // ✅ Dùng PDO
        try {
          $stmt = $con->prepare("SELECT * FROM products WHERE category = 'Giỏ quà'");
          $stmt->execute();
          $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if (count($products) > 0) {
            foreach ($products as $row) {
              echo '
              <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                  <img src="../images/' . htmlspecialchars($row['images']) . '" class="card-img-top" alt="' . htmlspecialchars($row['products_name']) . '">
                  <div class="card-body text-center">
                    <h5 class="card-title">' . htmlspecialchars($row['products_name']) . '</h5>
                    <p class="text-danger fw-bold">' . number_format($row['price'], 0, ',', '.') . ' đ</p>
                    <a href="#" class="btn btn-outline-danger">Thêm vào giỏ</a>
                  </div>
                </div>
              </div>';
            }
          } else {
            echo "<p class='text-center'>Hiện chưa có sản phẩm trong danh mục này.</p>";
          }
        } catch (PDOException $e) {
          echo "<p class='text-danger'>Lỗi truy vấn: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
      </div>
    </div>
  </div>
</div>

  <?php include __DIR__ . "/../include/Footer.php"; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
