<?php
include __DIR__ . "/../config/db.php";

// Lấy danh mục duy nhất từ bảng
$categories = $conn->query("SELECT DISTINCT category FROM products")->fetchAll(PDO::FETCH_COLUMN);

// Khởi tạo mảng rỗng để tránh lỗi
$products = [];

// Nhận tham số lọc
$category = isset($_GET['category']) ? $_GET['category'] : '';
$priceRange = isset($_GET['price']) ? $_GET['price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// Câu SQL cơ bản
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// Lọc theo danh mục
if ($category != '') {
  $sql .= " AND category = :cat";
  $params['cat'] = $category;
}

// Lọc theo giá
switch ($priceRange) {
  case '300-700':
    $sql .= " AND price BETWEEN 300000 AND 700000";
    break;
  case '700-1000':
    $sql .= " AND price BETWEEN 700000 AND 1000000";
    break;
  case '1000-2000':
    $sql .= " AND price BETWEEN 1000000 AND 2000000";
    break;
}

// Sắp xếp
switch ($sort) {
  case 'price_asc':
    $sql .= " ORDER BY price ASC";
    break;
  case 'price_desc':
    $sql .= " ORDER BY price DESC";
    break;
  default:
    $sql .= " ORDER BY id_product ASC";
    break;
}

// Thực thi truy vấn
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
