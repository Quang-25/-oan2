<?php
$host = "webbanhang.mysql.database.azure.com";
$dbname = "webbanhang";
$user_db = "Quang25";
$password = "Cohoi2512@";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;port=3306;charset=utf8",
        $user_db,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Kết nối thành công!";
} catch (PDOException $ex) {
    echo "❌ Kết nối thất bại: " . $ex->getMessage();
}
?>
