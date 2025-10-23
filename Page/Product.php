<?php
include __DIR__ . "/../config/db.php";

$products = [];
$categories = [];
$category = $_GET['category'] ?? '';
$priceRange = $_GET['price'] ?? '';
$sort = $_GET['sort'] ?? 'default';
include(__DIR__ . "/../include/Header.php"); 
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
  <div class="sp-header">
    <h2>SẢN PHẨM</h2>
    <div>Trang chủ / <span>Sản Phẩm</span></div>
</div>
<div class="container-fluid mt-4">
  <div class="row">
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

    <!-- ========== DANH SÁCH SẢN PHẨM ========== -->
    <div class="col-md-9">
      <div class="filter-bar d-flex justify-content-start align-items-center">
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
              <div class="product2">
                <div class="product-images2">
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

                <div class="product-icons2">
                  <a href="Productdetail.php?id=<?php echo $row['id_product']; ?>" class="icon-btn"><i class="bi bi-eye"></i></a>
                  <a href="#" class="add-to-cart" data-id="<?= htmlspecialchars($row['id_product']) ?>">
                  <i class="bi bi-cart-plus"></i>
                  </a>
                  <a href="#"><i class="bi bi-heart"></i></a>
             </a>
            <a href="#"><i class="bi bi-arrow-repeat"></i></a>
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
<script>
document.querySelectorAll('.add-to-cart').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const productId = this.dataset.id;

    fetch('../Page.backend/Product.backend.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'orderNow',
        product_id: productId
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {

        // ✅ Nếu backend có trả cart_count -> cập nhật số giỏ hàng
        if (data.cart_count !== undefined) {
          document.querySelectorAll('#cart-count').forEach(el => {
            el.textContent = data.cart_count;
            el.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
          });
        }

        // 🟢 Tạo popup thông báo
        const overlay = document.createElement('div');
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 bg-dark opacity-50';
        overlay.style.zIndex = '1050';

        const popup = document.createElement('div');
        popup.className = 'position-fixed top-50 start-50 translate-middle bg-white border rounded shadow-lg p-4 text-center';
        popup.style.zIndex = '1055';
        popup.style.minWidth = '300px';
        popup.innerHTML = `
          <h5 class="text-success mb-3">${data.message || '🛒 Thêm vào giỏ hàng thành công!'}</h5>
          <p>Bạn có muốn tiếp tục mua sắm không?</p>
          <div class="d-flex justify-content-center gap-3 mt-3">
            <button id="continueShopping" class="btn btn-outline-secondary">Tiếp tục</button>
            <button id="goToCart" class="btn btn-danger">Xem giỏ hàng</button>
          </div>
        `;

        // Gắn popup và nền mờ vào body
        document.body.appendChild(overlay);
        document.body.appendChild(popup);

        // 👉 Xử lý nút "Tiếp tục mua sắm"
        popup.querySelector('#continueShopping').addEventListener('click', () => {
          popup.remove();
          overlay.remove();
        });

        // 👉 Xử lý nút "Xem giỏ hàng"
        popup.querySelector('#goToCart').addEventListener('click', () => {
          window.location.href = '../Page/Cart.php';
        });

      } else {
        alert(data.message || 'Có lỗi xảy ra khi thêm sản phẩm.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('❌ Lỗi kết nối server.');
    });
  });
});

</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
