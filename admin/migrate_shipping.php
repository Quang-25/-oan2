<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../config/db.php";

try {
    // Kiểm tra và thêm các cột cần thiết vào bảng orders
    $checks = [
        'delivery_status' => "ALTER TABLE orders ADD COLUMN delivery_status VARCHAR(50) DEFAULT 'pending' AFTER status",
        'delivery_date' => "ALTER TABLE orders ADD COLUMN delivery_date DATETIME AFTER delivery_status"
    ];
    
    foreach ($checks as $column => $query) {
        try {
            $conn->exec($query);
            echo "✓ Thêm cột '$column' thành công<br>";
        } catch (Exception $e) {
            // Cột có thể đã tồn tại, bỏ qua
            echo "ℹ️ Cột '$column' đã tồn tại hoặc lỗi: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><strong style='color: green;'>✓ Cập nhật database thành công!</strong><br>";
    echo "<a href='index.php'>← Quay lại Dashboard</a>";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
