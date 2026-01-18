<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../config/db.php";

/* ==== KIỂM TRA ADMIN ==== */
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ==== XÓA ĐƠN HÀNG ==== */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM orders WHERE orders_id = ?");
        $result = $stmt->execute([$id]);
        if ($result) {
            $_SESSION['success'] = "✓ Xóa đơn hàng thành công!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "✗ Lỗi: " . $e->getMessage();
    }
    header("Location: index.php?page=oders");
    exit;
}

/* ==== SỬA ĐƠN HÀNG ==== */
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id       = (int)$_POST['id'];
    $quantity = (int)$_POST['quantity'];
    $total    = (float)$_POST['totalamount'];

    if ($id > 0 && $quantity > 0 && $total > 0) {
        try {
            $stmt = $conn->prepare("
                UPDATE orders 
                SET quantity = ?, totalamount = ?
                WHERE orders_id = ?
            ");
            $result = $stmt->execute([$quantity, $total, $id]);
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success'] = "✓ Cập nhật đơn hàng thành công!";
            } else {
                $_SESSION['error'] = "✗ Không tìm thấy đơn hàng!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "✗ Lỗi: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "✗ Vui lòng điền đầy đủ thông tin!";
    }

    header("Location: index.php?page=oders");
    exit;
}

/* ==== THÊM ĐƠN HÀNG ==== */
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $user_id = (int)$_POST['user_id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $total = (float)$_POST['totalamount'];

    if ($user_id > 0 && $product_id > 0 && $quantity > 0 && $total > 0) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO orders (User_ID, Product_ID, quantity, totalamount, order_date)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$user_id, $product_id, $quantity, $total]);
            if ($result) {
                $_SESSION['success'] = "✓ Thêm đơn hàng thành công!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "✗ Lỗi: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "✗ Vui lòng điền đầy đủ thông tin!";
    }

    header("Location: index.php?page=oders");
    exit;
}

/* ==== TÌM KIẾM ==== */
$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $conn->prepare("
        SELECT o.*, u.username AS customer_name, p.products_name AS product_name
        FROM orders o
        LEFT JOIN users u ON o.User_ID = u.ID_user
        LEFT JOIN products p ON o.Product_ID = p.id_product
        WHERE u.username LIKE ? OR p.products_name LIKE ?
        ORDER BY o.orders_id DESC
    ");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("
        SELECT o.*, u.username AS customer_name, p.products_name AS product_name
        FROM orders o
        LEFT JOIN users u ON o.User_ID = u.ID_user
        LEFT JOIN products p ON o.Product_ID = p.id_product
        ORDER BY o.orders_id DESC
    ");
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==== THÔNG BÁO ==== */
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<h1 class="text-center mb-4" style="color: #b91c1c; font-size: 2.5rem; font-weight: bold; padding-bottom: 20px; border-bottom: 3px solid #b91c1c;">Quản Lý Đơn Hàng</h1>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <button class="btn" style="background-color: #e11818; color: white; padding: 10px 20px; font-weight: bold;" data-bs-toggle="modal" data-bs-target="#addModal">Thêm đơn hàng</button>
    <form method="GET" action="index.php" class="d-flex">
        <input type="hidden" name="page" value="oders">
        <input name="search" class="form-control me-2" placeholder=" Khách hàng / sản phẩm"
               value="<?= htmlspecialchars($search) ?>" style="max-width: 400px;">
        <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
        <?php if ($search): ?>
            <a href="index.php?page=oders" class="btn btn-outline-secondary ms-2">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<table class="table table-bordered text-center align-middle">
<thead>
<tr style="border-bottom: 2px solid #b91c1c;">
    <th style="color: #e11818; font-weight: bold; border-bottom: 2px solid #b91c1c;">ID</th>
    <th style="color: #e11818; font-weight: bold; border-bottom: 2px solid #b91c1c;">Khách hàng</th>
    <th style="color: #e11818; font-weight: bold; border-bottom: 2px solid #b91c1c;">Sản phẩm</th>
    <th style="color: #e11818; font-weight: bold; border-bottom: 2px solid #b91c1c;">Số lượng</th>
    <th style="color: #e11818 ; font-weight: bold; border-bottom: 2px solid #b91c1c;">Tổng tiền</th>
    <th style="color: #e11818; font-weight: bold; border-bottom: 2px solid #b91c1c;">Ngày đặt</th>
    <th style="color: #e11818; font-weight: bold; border-bottom: 2px solid #b91c1c;">Hành động</th>
</tr>
</thead>
<tbody>
<?php if ($orders): foreach ($orders as $o): ?>
<tr>
    <td><?= $o['orders_id'] ?></td>
    <td><?= htmlspecialchars($o['customer_name'] ?? 'Ẩn danh') ?></td>
    <td><?= htmlspecialchars($o['product_name'] ?? 'Đã xóa') ?></td>
    <td><?= $o['quantity'] ?></td>
    <td class="text-danger fw-bold">
        <?= number_format($o['totalamount'], 0, ',', '.') ?>₫
    </td>
    <td><?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></td>
    <td>
        <button class="btn btn-warning btn-sm editBtn"
            data-id="<?= $o['orders_id'] ?>"
            data-qty="<?= $o['quantity'] ?>"
            data-total="<?= $o['totalamount'] ?>">✏️</button>

        <a href="index.php?page=oders&delete=<?= $o['orders_id'] ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Xóa đơn hàng này?')">🗑️</a>
    </td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" class="text-center text-muted">Không có đơn hàng</td></tr>
<?php endif; ?>
</tbody>
</table>

<!-- MODAL SỬA -->
<div class="modal fade" id="editModal">
<div class="modal-dialog">
<form method="POST" class="modal-content">
<div class="modal-header" style="background-color: #b91c1c; color: white;">
    <h5>Sửa đơn hàng</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" id="edit_id">

    <label>Số lượng</label>
    <input type="number" name="quantity" id="edit_qty" class="form-control mb-2" min="1" required>

    <label>Tổng tiền</label>
    <input type="number" name="totalamount" id="edit_total" class="form-control" min="0" step="0.01" required>
</div>
<div class="modal-footer">
    <button type="submit" class="btn" style="background-color: #b91c1c; color: white;"> Cập nhật</button>
</div>
</form>
</div>
</div>

<!-- MODAL THÊM -->
<div class="modal fade" id="addModal">
<div class="modal-dialog">
<form method="POST" class="modal-content">
<div class="modal-header" style="background-color: #e11818; color: white;">
    <h5> Thêm đơn hàng </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <input type="hidden" name="action" value="add">
    
    <label>Khách hàng</label>
    <select name="user_id" id="add_user" class="form-control mb-2" required>
        <option value="">-- Chọn khách hàng --</option>
        <?php 
        $users = $conn->query("SELECT ID_user, username FROM users ORDER BY username");
        foreach ($users as $u): 
        ?>
            <option value="<?= $u['ID_user'] ?>"><?= htmlspecialchars($u['username']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Sản phẩm</label>
    <select name="product_id" id="add_product" class="form-control mb-2" required>
        <option value="">-- Chọn sản phẩm --</option>
        <?php 
        $products = $conn->query("SELECT id_product, products_name, price FROM products ORDER BY products_name");
        foreach ($products as $p): 
        ?>
            <option value="<?= $p['id_product'] ?>" data-price="<?= $p['price'] ?>">
                <?= htmlspecialchars($p['products_name']) ?> - <?= number_format($p['price'], 0, ',', '.') ?>₫
            </option>
        <?php endforeach; ?>
    </select>

    <label>Số lượng</label>
    <input type="number" name="quantity" id="add_qty" class="form-control mb-2" min="1" value="1" required>

    <label>Tổng tiền</label>
    <input type="number" name="totalamount" id="add_total" class="form-control" min="0" step="0.01" required>
</div>
<div class="modal-footer">
    <button type="submit" class="btn" style="background-color: #b91c1c; color: white;">➕ Thêm</button>
</div>
</form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_qty').value = this.dataset.qty;
        document.getElementById('edit_total').value = this.dataset.total;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});

// Tính tổng tiền khi chọn sản phẩm hoặc nhập số lượng
document.getElementById('add_product')?.addEventListener('change', function() {
    const price = parseFloat(this.options[this.selectedIndex].dataset.price) || 0;
    const qty = parseInt(document.getElementById('add_qty').value) || 1;
    document.getElementById('add_total').value = (price * qty).toFixed(0);
});

document.getElementById('add_qty')?.addEventListener('input', function() {
    const price = parseFloat(document.getElementById('add_product').options[document.getElementById('add_product').selectedIndex].dataset.price) || 0;
    const qty = parseInt(this.value) || 1;
    document.getElementById('add_total').value = (price * qty).toFixed(0);
});
</script>
