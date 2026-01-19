<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include __DIR__ . "/../config/db.php";


// ==== KIỂM TRA QUYỀN ADMIN ====
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}
$msg = "";
// ==== XÓA SẢN PHẨM ====
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare("DELETE FROM products WHERE id_product = ?");
  $stmt->execute([$id]);
  header("Location: index.php?page=products");
  exit;
}
// ==== THÊM SẢN PHẨM ====
if (isset($_POST['action']) && $_POST['action'] === 'add') {
  $name = trim($_POST['products_name']);
  $price = floatval($_POST['price']);
  $totalquantity = intval($_POST['totalquantity']);
  $quantitySold = intval($_POST['quantitySold'] ?? 0);
  $desc = trim($_POST['desc']);
  $category = trim($_POST['category'] ?? '');
  $status = $_POST['status'] ?? 'Còn hàng';
  $images = $_POST['images'] ?? '';
  $image1 = $_POST['image1'] ?? '';
  $image2 = $_POST['image2'] ?? '';

  $stmt = $conn->prepare("INSERT INTO products 
    (products_name, price, totalquantity, quantitySold, descs, category, images, image1, image2, status, dates)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

  $stmt->execute([$name, $price, $totalquantity, $quantitySold, $desc, $category, $images, $image1, $image2, $status]);

  header("Location: index.php?page=products");
  exit;
}

// ==== SỬA SẢN PHẨM ====
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
  $id = $_POST['id'];
  $name = trim($_POST['products_name']);
  $price = floatval($_POST['price']);
  $quantity = intval($_POST['totalquantity']);
  $quantitySold = intval($_POST['quantitySold']);
  $desc = trim($_POST['desc']);
  $category = $_POST['category'] ?? 'Túi quà';
  $status = $_POST['status'] ?? 'Còn hàng';
  $images = $_POST['images'] ?? '';
  $image1 = $_POST['image1'] ?? '';
  $image2 = $_POST['image2'] ?? '';

  $stmt = $conn->prepare("UPDATE products 
        SET products_name=?, price=?, totalquantity=?, quantitySold=?,descs=?,category=?,images=?, image1=?, image2=?, status=? 
        WHERE id_product=?");
  $stmt->execute([$name, $price, $quantity, $quantitySold, $desc, $category, $images, $image1, $image2, $status, $id]);
  header("Location: index.php?page=products");
  exit;
}

// ==== TÌM KIẾM ====
$search = $_GET['search'] ?? '';
if ($search) {
  $stmt = $conn->prepare("SELECT * FROM products WHERE products_name LIKE ? ORDER BY id_product DESC");
  $stmt->execute(['%' . $search . '%']);
} else {
  $stmt = $conn->query("SELECT * FROM products ORDER BY id_product DESC");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);

function getImageUrl($p)
{
  foreach (['images', 'image1', 'image2'] as $field) {
    if (!empty($p[$field])) {
      $img = $p[$field];
      if (preg_match('/^https?:\/\//', $img)) {
        return $img;
      }
      return "../uploads/" . $img;
    }
  }
  return "../images/no-image.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Sản Phẩm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .container {
      max-width: 1200px;
      margin-top: 40px;
    }

    h1 {
      color: #b91c1c;
      font-weight: bold;
    }

    .table th {
      background: #dc2626;
      color: white;
    }

    .img.thumb {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 6px;
    }

    .btn-custom {
      background: #b91c1c;
      color: white;
      border: none;
    }

    .btn-custom:hover {
      background: #dc2626;
    }

    .modal-header {
      background: #b91c1c;
      color: white;
    }
  </style>

  <h1 class="mb-4 text-center text-danger fw-bold">Quản Lý Sản Phẩm</h1>

  <?php if ($msg): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="d-flex justify-content-between mb-3">
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addModal"> Thêm sản phẩm</button>
    <form method="GET" action="index.php" class="d-flex">
      <input type="hidden" name="page" value="products">
      <input type="text" name="search" class="form-control me-2" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-secondary"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
      <thead>
        <tr class="bg-danger text-white">
          <th>ID</th>
          <th>Ảnh</th>
          <th>Tên sản phẩm</th>
          <th>Giá</th>
          <th>Tồn kho</th>
          <th>Đã bán</th>
          <th>Mô tả</th>
          <th>Loại</th>
          <th>Trạng thái</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($products): foreach ($products as $p): $img = getImageUrl($p); ?>
            <tr>
              <td><?= $p['id_product'] ?></td>
              <td><img src="<?= htmlspecialchars($img) ?>" width="70" height="70" class="rounded border"></td>
              <td><?= htmlspecialchars($p['products_name']) ?></td>
              <td><?= number_format($p['price'], 0, ',', '.') ?>₫</td>
              <td><?= $p['totalquantity'] ?></td>
              <td><?= $p['quantitySold'] ?></td>
              <td><?= htmlspecialchars($p['descs']) ?></td>
              <td><?= htmlspecialchars($p['category']) ?></td>
              <td>
                <?php if ($p['status'] === 'Còn hàng'): ?>
                  <span class="badge bg-success">Còn hàng</span>
                <?php else: ?>
                  <span class="badge bg-danger">Hết hàng</span>
                <?php endif; ?>
              </td>
              <td>
                <button class="btn btn-warning btn-sm editBtn"
                  data-id="<?= $p['id_product'] ?>"
                  data-name="<?= htmlspecialchars($p['products_name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-qty="<?= $p['totalquantity'] ?>"
                  data-sold="<?= $p['quantitySold'] ?>"
                  data-desc="<?= htmlspecialchars($p['descs']) ?>"
                  data-category="<?= htmlspecialchars($p['category']) ?>"
                  data-status="<?= htmlspecialchars($p['status']) ?>"
                  data-img="<?= htmlspecialchars($p['images']) ?>"
                  data-img1="<?= htmlspecialchars($p['image1']) ?>"
                  data-img2="<?= htmlspecialchars($p['image2']) ?>">✏️</button>
                <a href="index.php?page=products&delete=<?= $p['id_product'] ?>"
                  class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này?')">🗑️</a>
              </td>
            </tr>
          <?php endforeach;
        else: ?>
          <tr>
            <td colspan="9" class="text-muted">Không có sản phẩm nào.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>


  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Thêm sản phẩm</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          < class="row g-3">
            <div class="col-md-6"><label>Tên sản phẩm</label><input name="products_name" class="form-control" required></div>
            <div class="col-md-3"><label>Giá</label><input type="number" name="price" class="form-control" required></div>
            <div class="col-md-3"><label>Số lượng</label><input type="number" name="totalquantity" class="form-control" required></div>
            <div class="col-md-3"><label>Số lượng đã bán</label><input type="number" name="quantitySold" class="form-control" value="0" require></div>
            <div class="col-12"><label>Mô tả</label><textarea name="desc" class="form-control"></textarea></div>
            <div class="col-12"><label>Loại</label><input type="text" name="category" class="form-control"></div>
            <div class="col-md-4"><label>Ảnh chính</label><input type="text" name="images" class="form-control"></div>
            <div class="col-md-4"><label>Ảnh phụ 1</label><input type="text" name="image1" class="form-control"></div>
            <div class="col-md-4"><label>Ảnh phụ 2</label><input type="text" name="image2" class="form-control"></div>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-custom">💾 Lưu</button></div>
      </form>
    </div>
  </div>

  <!-- Modal sửa -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">✏️ Sửa sản phẩm</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" id="edit_id">

          <div class="row g-3">
            <div class="col-md-6">
              <label>Tên sản phẩm</label>
              <input name="products_name" id="edit_name" class="form-control" required>
            </div>

            <div class="col-md-3">
              <label>Giá</label>
              <input type="number" name="price" id="edit_price" class="form-control" required>
            </div>

            <div class="col-md-3">
              <label>Số lượng</label>
              <input type="number" name="totalquantity" id="edit_qty" class="form-control" required>
            </div>

            <div class="col-md-3">
              <label>Số lượng đã bán</label>
              <input type="number" name="quantitySold" id="edit_sold" class="form-control" required>
            </div>

            <div class="col-12">
              <label>Mô tả</label>
              <textarea name="desc" id="edit_desc" class="form-control"></textarea>
            </div>

            <div class="col-12">
              <label>Loại</label>
              <input type="text" name="category" id="edit_category" class="form-control">
            </div>
            <div class="col-12">
              <label>Ảnh hiện tại</label><br>
              <img id="edit_preview" src="" width="90" class="border mb-2 me-2">
              <img id="edit_preview1" src="" width="90" class="border mb-2 me-2">
              <img id="edit_preview2" src="" width="90" class="border mb-2">
            </div>

            <div class="col-md-4">
              <label>Ảnh chính</label>
              <input type="text" name="images" id="edit_img" class="form-control">
            </div>

            <div class="col-md-4">
              <label>Ảnh phụ 1</label>
              <input type="text" name="image1" id="edit_img1" class="form-control">
            </div>

            <div class="col-md-4">
              <label>Ảnh phụ 2</label>
              <input type="text" name="image2" id="edit_img2" class="form-control">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-custom">💾 Cập nhật</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.editBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_name').value = btn.dataset.name;
        document.getElementById('edit_price').value = btn.dataset.price;
        document.getElementById('edit_qty').value = btn.dataset.qty;
        document.getElementById('edit_sold').value = btn.dataset.sold;
        document.getElementById('edit_desc').value = btn.dataset.desc;
        document.getElementById('edit_category').value = btn.dataset.category;
        document.getElementById('edit_img').value = btn.dataset.img;
        document.getElementById('edit_img1').value = btn.dataset.img1;
        document.getElementById('edit_img2').value = btn.dataset.img2;

        const img = btn.dataset.img ? btn.dataset.img.match(/^https?:\/\//) ? btn.dataset.img : '../uploads/' + btn.dataset.img : '../images/no-image.jpg';
        const img1 = btn.dataset.img1 ? btn.dataset.img1.match(/^https?:\/\//) ? btn.dataset.img1 : '../uploads/' + btn.dataset.img1 : '../images/no-image.jpg';
        const img2 = btn.dataset.img2 ? btn.dataset.img2.match(/^https?:\/\//) ? btn.dataset.img2 : '../uploads/' + btn.dataset.img2 : '../images/no-image.jpg';

        document.getElementById('edit_preview').src = img;
        document.getElementById('edit_preview1').src = img1;
        document.getElementById('edit_preview2').src = img2;

        new bootstrap.Modal(document.getElementById('editModal')).show();
      });
    });
  </script>

  </body>

</html>