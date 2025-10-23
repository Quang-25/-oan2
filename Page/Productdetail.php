<?php
include __DIR__ . "/../config/db.php";
session_start();
// 1. Lấy ID sản phẩm từ URL và kiểm tra
$product_id = $_GET['id'] ?? null;
$product = null;


if ($product_id) {
    try {
        // 2. Chuẩn bị truy vấn (Sử dụng Prepared Statements để bảo mật)
        $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = :id");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Chuyển giá về dạng float để xử lý
        if ($product) {
            $product['price'] = (float)$product['price'];
        }

    } catch (PDOException $e) {
        // Xử lý lỗi CSDL
        error_log("Database error: " . $e->getMessage());
        $product = null; // Đảm bảo sản phẩm rỗng nếu có lỗi
    }
}

// Nếu không tìm thấy ID hoặc sản phẩm, chuyển hướng (hoặc hiển thị lỗi)
if (!$product) {
    // Chuyển hướng người dùng về trang danh sách sản phẩm hoặc trang lỗi 404
    header("Location: Product.php");
    exit();
}

// Gọi Header
include(__DIR__ . "/../include/Header.php");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($product['products_name']); ?> - Chi tiết sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css"> 
    <style>
        /* CSS tùy chỉnh cho trang chi tiết */
        .product-gallery img {
            cursor: pointer;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            object-fit: contain;
            transition: all 0.3s;
        }
        .product-gallery img:hover {
            border-color: #dc3545;
        }
        .main-image {
            max-height: 500px;
            object-fit: contain;
        }
        .quantity-control button {
            width: 40px;
        }
        .quantity-control input {
            width: 60px;
            text-align: center;
        }
    </style>
</head>

<body>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-6">
                <div class="main-image-container mb-3 border rounded shadow-sm">
                    <img id="mainProductImage" src="<?php echo htmlspecialchars($product['images'] ?? $product['image1'] ?? ''); ?>" 
                         class="img-fluid w-100 main-image p-3" 
                         alt="<?php echo htmlspecialchars($product['products_name']); ?>">
                </div>
                
                <div class="row product-gallery">
                    <?php if (!empty($product['images'])): ?>
                    <div class="col-3">
<img src="<?php echo htmlspecialchars($product['images']); ?>" 
                             class="img-fluid rounded thumbnail" 
                             data-src="<?php echo htmlspecialchars($product['images']); ?>" 
                             alt="Thumbnail 1">
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['image1'])): ?>
                    <div class="col-3">
                        <img src="<?php echo htmlspecialchars($product['image1']); ?>" 
                             class="img-fluid rounded thumbnail" 
                             data-src="<?php echo htmlspecialchars($product['image1']); ?>" 
                             alt="Thumbnail 2">
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($product['image2'])): ?>
                    <div class="col-3">
                        <img src="<?php echo htmlspecialchars($product['image2']); ?>" 
                             class="img-fluid rounded thumbnail" 
                             data-src="<?php echo htmlspecialchars($product['image2']); ?>" 
                             alt="Thumbnail 3">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['products_name']); ?></h1>
                
                <p class="h3 text-danger fw-bold mb-4">
                    <?php echo number_format($product['price'], 0, ',', '.'); ?> đ
                </p>

                <div class="mb-4">
                    <p class="fw-bold">Tình trạng: 
                        <span class="text-success"><?php echo htmlspecialchars($product['status']); ?></span>
                    </p>
                    <p class="fw-bold">Danh mục: 
                        <span><?php echo htmlspecialchars($product['category']); ?></span>
                    </p>
                    <p class="fw-bold">Số lượng còn: 
                        <span><?php echo number_format($product['totalquantity'] - $product['quantitySold']); ?></span>
                    </p>
                </div>
                
                <p class="mb-4 text-muted">
                    <?php echo nl2br(htmlspecialchars(substr($product['descs'], 0, 200))) . '...'; ?>
                </p>

                <div class="d-flex align-items-center mb-4">
                    <label class="me-3 fw-bold">Số lượng:</label>
                    <div class="input-group quantity-control me-4" style="width: 130px;">
                        <button class="btn btn-outline-secondary" type="button" id="btnMinus">-</button>
                        <input type="number" id="quantityInput" class="form-control" value="1" min="1" max="<?php echo $product['totalquantity'] - $product['quantitySold']; ?>">
<button class="btn btn-outline-secondary" type="button" id="btnPlus">+</button>
                    </div>
                    <button class="btn btn-danger btn-lg" id="addToCartBtn" data-id="<?php echo $product['id_product']; ?>">
                        <i class="bi bi-cart-plus me-2"></i> Thêm vào giỏ hàng
                    </button>
                </div>

                <hr>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab" aria-controls="desc" aria-selected="true">Mô tả chi tiết</button>
                    </li>
                </ul>
                <div class="tab-content p-3 border border-top-0" id="myTabContent">
                    <div class="tab-pane fade show active" id="desc" role="tabpanel" aria-labelledby="desc-tab">
                        <p><?php echo nl2br(htmlspecialchars($product['descs'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include(__DIR__ . "/../include/Footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ------------------ CHỨC NĂNG THAY ĐỔI HÌNH ẢNH ------------------
            const mainImage = document.getElementById('mainProductImage');
            document.querySelectorAll('.thumbnail').forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    mainImage.src = this.dataset.src;
                });
            });

            // ------------------ CHỨC NĂNG TĂNG GIẢM SỐ LƯỢNG ------------------
            const quantityInput = document.getElementById('quantityInput');
            const btnPlus = document.getElementById('btnPlus');
            const btnMinus = document.getElementById('btnMinus');
            const maxQuantity = parseInt(quantityInput.getAttribute('max'));

            btnPlus.addEventListener('click', () => {
                let currentVal = parseInt(quantityInput.value);
                if (currentVal < maxQuantity) {
                    quantityInput.value = currentVal + 1;
                }
            });

            btnMinus.addEventListener('click', () => {
                let currentVal = parseInt(quantityInput.value);
                if (currentVal > 1) {
                    quantityInput.value = currentVal - 1;
                }
            });

            // Đảm bảo giá trị nhập tay không vượt quá giới hạn
            quantityInput.addEventListener('change', () => {
                let currentVal = parseInt(quantityInput.value);
                if (isNaN(currentVal) || currentVal < 1) {
quantityInput.value = 1;
                } else if (currentVal > maxQuantity) {
                    quantityInput.value = maxQuantity;
                }
            });

            // ------------------ XỬ LÝ THÊM VÀO GIỎ HÀNG (AJAX) ------------------
            document.getElementById('addToCartBtn').addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.id;
                const quantity = quantityInput.value;

                fetch('../Page.backend/Product.backend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'orderNow',
                        product_id: productId,
                        quantity: quantity // Gửi thêm số lượng
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        
                        // Cập nhật số lượng giỏ hàng
                        if (data.cart_count !== undefined) {
                            document.querySelectorAll('#cart-count').forEach(el => {
                                el.textContent = data.cart_count;
                            });
                        }

                        // Hiển thị popup thông báo
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
                        
                        document.body.appendChild(overlay);
                        document.body.appendChild(popup);

                        // Xử lý nút "Tiếp tục mua sắm"
                        popup.querySelector('#continueShopping').addEventListener('click', () => {popup.remove();
                            overlay.remove();
                        });
                        
                        // Xử lý nút "Xem giỏ hàng"
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
</body>

</html>
