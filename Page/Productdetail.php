<?php
include __DIR__ . "/../config/db.php";

// 1. Lấy ID sản phẩm từ URL và kiểm tra
$product_id = $_GET['id'] ?? null;
$product = null;

if ($product_id) {
    try {
        // 2. Truy vấn an toàn bằng Prepared Statement
        $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = :id");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Giá định dạng số
            $product['price'] = (float)$product['price'];

            // 🔹 Xử lý chọn ảnh chính giống Home
            $product['imagePath'] = $product['images']
                ?: ($product['image1']
                    ?: ($product['image2']
                        ?: '../images/no-image.jpg'));
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $product = null;
    }
}

// Nếu không tìm thấy sản phẩm
if (!$product) {
    header("Location: Product.php");
    exit();
}

// Gọi Header
include(__DIR__ . "/../include/Header.php");
?>
<div class="detail-header">
    <h2>Chi Tiết Sản Phẩm</h2>
    <div>Trang chủ / <span>Chi Tiết Sản Phẩm</span></div>
</div>
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
        .detail-header {
            background-color: #e6e6e6;
            padding: 40px 0;
            text-align: center;
        }

        .detail-header h2 {
            color: #b30000;
            font-weight: 700;
            margin: 0;
        }

        .detail-header div {
            margin-top: 5px;
            font-size: 15px;
        }

        .detail-header span {
            color: #c50000;
        }

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
            max-height: 380px;
            width: 100%;
            object-fit: contain;
            border-radius: 10px;
        }

        .quantity-control button {
            width: 40px;
        }

        .quantity-control input {
            width: 60px;
            text-align: center;
        }

        .h1+p.h3 {
            font-size: 22px;
            color: #d10000;
            font-weight: 700;
            margin-bottom: 15px;
        }

        /* --- Nhãn thông tin (Tình trạng, Danh mục, Số lượng) --- */
        .fw-bold {
            font-size: 15px;
            color: #333;
        }


        #desc {
            font-size: 15px;
            line-height: 1.7;
            color: #444;
        }

        /* --- Nút thêm vào giỏ hàng --- */
        #addToCartBtn {
            font-size: 15px;
            padding: 10px 18px;
            border-radius: 8px;
        }

        /* --- Nhãn “Số lượng” và input --- */
        .quantity-control button {
            font-size: 16px;
        }

        .quantity-control input {
            font-size: 15px;
        }

        /* --- Thẻ tab mô tả --- */
        .nav-tabs .nav-link {
            font-size: 15px;
            padding: 8px 16px;
        }
    </style>
</head>

<body>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-6">
                <!-- Ảnh chính -->
                <div class="main-image-container mb-3 border rounded shadow-sm">
                    <img id="mainProductImage"
                        src="<?php echo htmlspecialchars($product['imagePath']); ?>"
                        class="img-fluid w-100 main-image p-3"
                        alt="<?php echo htmlspecialchars($product['products_name']); ?>">
                </div>

                <!-- Bộ sưu tập ảnh nhỏ -->
                <div class="row product-gallery">
                    <?php if (!empty($product['images'])): ?>
                        <div class="col-3">
                            <img src="<?php echo htmlspecialchars($product['images']); ?>"
                                class="img-fluid rounded thumbnail"
                                data-src="<?php echo htmlspecialchars($product['images']); ?>"
                                alt="Ảnh 1">
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['image1'])): ?>
                        <div class="col-3">
                            <img src="<?php echo htmlspecialchars($product['image1']); ?>"
                                class="img-fluid rounded thumbnail"
                                data-src="<?php echo htmlspecialchars($product['image1']); ?>"
                                alt="Ảnh 2">
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['image2'])): ?>
                        <div class="col-3">
                            <img src="<?php echo htmlspecialchars($product['image2']); ?>"
                                class="img-fluid rounded thumbnail"
                                data-src="<?php echo htmlspecialchars($product['image2']); ?>"
                                alt="Ảnh 3">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <h1 class="mb-3" style="font-size: 26px; font-weight: 600; color: #222;">
                    <?php echo htmlspecialchars($product['products_name']); ?>
                </h1>
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
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.getElementById('mainProductImage');
            document.querySelectorAll('.thumbnail').forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    mainImage.src = this.dataset.src;
                });
            });

            // ------------------ CHỨC NĂNG TĂNG GIẢM ------------------
            const quantityInput = document.getElementById('quantityInput');
            const btnPlus = document.getElementById('btnPlus');
            const btnMinus = document.getElementById('btnMinus');
            const maxQuantity = parseInt(quantityInput.getAttribute('max'));

            btnPlus.addEventListener('click', () => {
                let val = parseInt(quantityInput.value);
                if (val < maxQuantity) quantityInput.value = val + 1;
            });

            btnMinus.addEventListener('click', () => {
                let val = parseInt(quantityInput.value);
                if (val > 1) quantityInput.value = val - 1;
            });

            // ------------------ XỬ LÝ AJAX THÊM GIỎ ------------------
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
                            quantity: quantity
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (data.cart_count !== undefined) {
                                document.querySelectorAll('#cart-count').forEach(el => el.textContent = data.cart_count);
                            }

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

                            popup.querySelector('#continueShopping').addEventListener('click', () => {
                                popup.remove();
                                overlay.remove();
                            });

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