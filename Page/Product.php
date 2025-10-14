<?php
include __DIR__ . "/../config/db.php";
$products = [];
$categories = [];
$category = $_GET['category'] ?? '';
$priceRange = $_GET['price'] ?? '';
$sort = $_GET['sort'] ?? 'default';
include __DIR__ . "/../Page.backend/Product.backend.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Danh sách sản phẩm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  
  <link rel="stylesheet" href="../css/product.css">
  
    
</head>
<body>
<div class="container-fluid mt-4">
  <div class="row">
    <!-- SIDEBAR -->
    <div class="col-md-3">
      <div class="sidebar">
        <h5>🎁 Danh mục sản phẩm</h5>
        <?php foreach ($categories as $cat): ?>
          <a href="?category=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a>
        <?php endforeach; ?>
        <hr>
        <h5>💰 Khoảng giá</h5>
        <a href="?price=300-700">Từ 300K đến 700K</a>
        <a href="?price=700-1000">Từ 700K đến 1 Triệu</a>
        <a href="?price=1000-2000">Từ 1 Triệu đến 2 Triệu</a>
        <hr>
        <a class="btn btn-outline-danger w-100" href="../Page/Product.php">🔄 Xem tất cả</a>
      </div>
    </div>

    <!-- DANH SÁCH SẢN PHẨM -->
    <div class="col-md-9">
      <div class="filter-bar d-flex justify-content-end align-items-center">
        <form method="get" class="d-flex align-items-center gap-2">
          <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
          <input type="hidden" name="price" value="<?= htmlspecialchars($priceRange) ?>">
          <label class="fw-semibold">Sắp xếp:</label>
          <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()">
            <option value="default" <?= $sort=='default'?'selected':'' ?>>Mặc định</option>
            <option value="price_asc" <?= $sort=='price_asc'?'selected':'' ?>>Giá tăng dần</option>
            <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>Giá giảm dần</option>
          </select>
        </form>
      </div>

      <div class="row g-3">
        <?php if (count($products) > 0): ?>
          <?php foreach ($products as $row): ?>
            <div class="col-md-3 col-sm-6">
              <div class="product">
                <div class="product-images">
                  <?php if (!empty($row['images'])): ?>
                    <img src="<?= htmlspecialchars($row['images']) ?>" alt="">
                  <?php endif; ?>

                  <?php if (!empty($row['image1'])): ?>
                    <img src="<?= htmlspecialchars($row['image1']) ?>" alt="">
                  <?php endif; ?>

                  <?php if (!empty($row['image2'])): ?>
                    <img src="<?= htmlspecialchars($row['image2']) ?>" alt="">
                  <?php endif; ?>
                </div>

                <h6><?= htmlspecialchars($row['products_name']) ?></h6>
                <p class="price"><?= number_format($row['price'], 0, ',', '.') ?> đ</p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center text-muted mt-4">Không có sản phẩm nào trong mục này.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
         
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
