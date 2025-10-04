<?php
include __DIR__ . "/../config/db.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trang chủ</title>
  <!-- CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>

<body>
  <!-- Gọi Header -->
  <?php include "include/Header.php"; ?>

  <!-- Slider -->
  <div class="swiper mySwiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <img src="https://demo037030.web30s.vn/datafiles/32835/upload/images/slide-2.jpg?t=1607657601" class="w-100" alt="slide 1">
      </div>
      <div class="swiper-slide">
        <img src="https://demo037030.web30s.vn/datafiles/32835/upload/images/slide-1.jpg?t=1752894235" class="w-100" alt="slide 2">
      </div>
      <div class="swiper-slide">
        <img src="https://demo037030.web30s.vn/datafiles/32835/upload/images/img-slide-3.jpg?t=1752894235" class="w-100" alt="slide 3">
      </div>
    </div>
    <div class="swiper-pagination"></div>
  </div>
  <div class="featured-products py-4">
    <div class="container">
      <div class="section-title d-flex justify-content-between align-items-center mb-3">
        <h2 class="name-featured">SẢN PHẨM Nổi Bật</h2>
        <div class="nav-buttons">
          <button class="btn btn-outline-dark btn-sm swiper-prev"><i class="bi bi-chevron-left"></i></button>
          <button class="btn btn-outline-dark btn-sm swiper-next"><i class="bi bi-chevron-right"></i></button>
        </div>
      </div>


      <!-- Swiper -->
      <div class="swiper mySwiperProducts">
        <div class="swiper-wrapper">
          <?php
          $sql = "SELECT * FROM products LIMIT 5";
          $stmt = $conn->query($sql);

          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          ?>
            <div class="swiper-slide">
              <div class="card h-100 border shadow-sm">

                <!-- Hình sản phẩm -->
                <div class="product-image position-relative">
                  <img src="<?php echo $row['images']; ?>"
                    class="product-img"
                    alt="<?php echo $row['products_name']; ?>">

                  <!-- Overlay icon -->
                  <div class="icon-overlay d-flex justify-content-center align-items-center">
                    <a href="ProductDetail.php?id=<?php echo $row['id_product']; ?>" class="icon-btn"><i class="bi bi-eye"></i></a>
                    <a href="Cart.php?add=<?php echo $row['id_product']; ?>" class="icon-btn"><i class="bi bi-cart-plus"></i></a>
                    <a href="#" class="icon-btn"><i class="bi bi-heart"></i></a>
                    <a href="#" class="icon-btn"><i class="bi bi-arrow-repeat"></i></a>
                  </div>
                </div>

                <!-- Nội dung -->
                <div class="card-body text-center">
                  <h6 class="card-title"><?php echo $row['products_name']; ?></h6>
                  <p class="text-danger fw-bold mb-0">
                    <?php echo number_format($row['price'], 0, ',', '.'); ?> đ
                  </p>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>


      <!-- JS -->
      <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <script>
        var swiper = new Swiper(".mySwiper", {
          loop: true,
          autoplay: {
            delay: 3000,
            disableOnInteraction: false,
          },
          pagination: {
            el: ".swiper-pagination",
            clickable: true,
          },
          effect: "fade",
          speed: 1000
        });
        var swiper = new Swiper(".mySwiperProducts", {
          slidesPerView: 4,
          spaceBetween: 20,
          loop: true,
          navigation: {
            nextEl: ".swiper-next",
            prevEl: ".swiper-prev",
          },
          breakpoints: {
            0: {
              slidesPerView: 1
            },
            576: {
              slidesPerView: 2
            },
            768: {
              slidesPerView: 3
            },
            992: {
              slidesPerView: 4
            }
          }
        });
      </script>
</body>

</html>