<?php
include __DIR__ . '/../config/db.php';

$category = 'gioqua';

$stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
$stmt->execute([$category]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ quà - Cửa hàng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>🎁 Sản phẩm Giỏ quà</h1>

    <div class="filter">
        <form method="GET">
            <select name="sort">
                <option value="">-- Sắp xếp --</option>
                <option value="price_asc">Giá tăng dần</option>
                <option value="price_desc">Giá giảm dần</option>
            </select>
            <button type="submit">Lọc</button>
        </form>
    </div>

    <div class="product-list">
        <?php
        // Xử lý lọc sản phẩm
        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
            if ($sort == 'price_asc') {
                usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
            } elseif ($sort == 'price_desc') {
                usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
            }
        }

        foreach ($products as $item): ?>
            <div class="product-item">
                <img src="../assets/images/<?php echo $item['images']; ?>" alt="">
                <h3><?php echo htmlspecialchars($item['products_name']); ?></h3>
                <p><?php echo number_format($item['price']); ?> đ</p>
                <p><?php echo htmlspecialchars($item['status']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
