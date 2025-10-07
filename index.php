<?php
// Kết nối database
include __DIR__ . "/config/db.php";


// Điều hướng trang
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch($page) {
    case 'cart':
        include "Page/Cart.php";
        break;
    case 'login':
        include "Page/login.php";
        break;
    case 'payment':
        include "Page/payment.php";
        break;
    case 'product':
        include "Page/Product.php";
        break;
    case 'register':
        include "Page/register.php";
        break;
    default:
        include "Page/Home.php";
        break;
}

// Gọi footer

?>
