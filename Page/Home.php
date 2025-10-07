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
                    <a href="Page/Cart.php?id=<?php echo $row['id_product']; ?>" class="icon-btn"><i class="bi bi-cart-plus"></i></a>
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
      <div class= "banner2">
        <img src="https://demo037030.web30s.vn/datafiles/32835/upload/images/img-banner-1.jpg?t=1752894235" class="banner-2" alt="banner">
      </div>
      <h2 class="nameproduct">Sản phẩm mới</h2>
     <div class="row">
<?php
    // Lấy sản phẩm mới nhất
    $sql = "SELECT * FROM products ORDER BY id_product DESC LIMIT 8";
    $stmt = $conn->query($sql);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
?>
    <div class="col-md-3 mb-4">
        <div class="product-card">
            <div class="product-img">
                <img src="<?php echo htmlspecialchars($row['image1']); ?>" 
                     alt="<?php echo htmlspecialchars($row['products_name']); ?>" 
                >
                <div class="product-actions">
                    <a href="Product.php?id=<?php  echo $row['id_product']; ?>"><i class="bi bi-eye"></i></a>
                    <a href="Page/Cart.php?id=<?php echo $row['id_product']; ?>"><i class="bi bi-cart"></i></a>
                    <a href="#"><i class="bi bi-heart"></i></a>
                    <a href="#"><i class="bi bi-arrow-repeat"></i></a>
                </div>
            </div>
            <div class="product-info text-center">
                <h6><?php echo $row['products_name']; ?></h6>
                <p class="price text-danger fw-bold">
                    <?php echo number_format($row['price'],0,',','.'); ?> đ
                </p>
            </div>
          </div>
    </div>
<?php } ?>
</div> 
    </div>
    <section class="py-4" style="background-color: #d32f2f; color: white;">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-3">
        <i class="bi bi-truck display-5 mb-2"></i>
        <h6 class="fw-bold">MIỄN PHÍ GIAO HÀNG</h6>
        <p>Hóa đơn từ 1 triệu trở lên</p>
      </div>
      <div class="col-md-3 mb-3">
        <i class="bi bi-credit-card display-5 mb-2"></i>
        <h6 class="fw-bold">THANH TOÁN</h6>
        <p>Hình thức đa dạng, thuận tiện</p>
      </div>
      <div class="col-md-3 mb-3">
        <i class="bi bi-gift display-5 mb-2"></i>
        <h6 class="fw-bold">CHẤT LƯỢNG</h6>
        <p>100% từ nhà cung cấp uy tín</p>
      </div>
      <div class="col-md-3 mb-3">
        <i class="bi bi-wallet2 display-5 mb-2"></i>
        <h6 class="fw-bold">GIÁ CẢ HỢP LÝ</h6>
        <p>Cam kết đúng giá thị trường</p>
      </div>
    </div>
  </div>
</section>
   <div class="news-section container my-5">
  <h3 class="text-center mb-4 fw-bold text-danger">
    TIN TỨC SỰ KIỆN
  </h3>
  <div class="row g-4">
    <!-- Tin 1 -->
    <div class="col-md-3">
      <div class="news-card shadow-sm h-100">
        <img src="http://nld.mediacdn.vn/thumb_w/698/2020/12/3/ch-1606990235879905251373.jpg"
             class="news-img" alt="tin tức 1">
        <div class="news-body">
          <p class="news-meta text-muted mb-2">
            <i class="bi bi-calendar3 text-danger"></i> 11/12/2020 &nbsp;&nbsp;
            <i class="bi bi-eye text-danger"></i> 162
          </p>
          <h6 class="news-title fw-bold">Vừa phòng dịch vừa chuẩn bị hàng Tết</h6>
          <p class="news-desc small text-muted mb-0">
            Saigon Co.op tăng cường nguồn hàng dự trữ trên toàn hệ thống, chuẩn bị chu đáo các kịch bản cung ứng và ưu đãi...
          </p>
        </div>
      </div>
    </div>

    <!-- Tin 2 -->
    <div class="col-md-3">
      <div class="news-card shadow-sm h-100">
        <img src="https://cdn.nhandan.vn/images/7f491ee6c6b660425d7c9ab03f1ec4473dc1d95f7c5255d1cd927efff1b924d1013e12fe4c7c0d801c6e3969e7f3399902477dacb7745cc4f5f9ad33cbcbec23/a1-1607418973395.jpeg"
             class="news-img" alt="tin tức 2">
        <div class="news-body">
          <p class="news-meta text-muted mb-2">
            <i class="bi bi-calendar3 text-danger"></i> 11/12/2020 &nbsp;&nbsp;
            <i class="bi bi-eye text-danger"></i> 222
          </p>
          <h6 class="news-title fw-bold">Saigon Co.op dự trữ lượng lớn hàng hóa phục vụ Tết</h6>
          <p class="news-desc small text-muted mb-0">
            Liên hiệp HTX Thương mại TP.HCM cho biết đã chuẩn bị sẵn sàng hàng hóa thiết yếu phục vụ mùa Tết...
          </p>
        </div>
      </div>
    </div>

    <!-- Tin 3 -->
    <div class="col-md-3">
      <div class="news-card shadow-sm h-100">
        <img src="http://nld.mediacdn.vn/thumb_w/698/2020/12/10/cha-16075945009551486610887.jpg"
             class="news-img" alt="tin tức 3">
        <div class="news-body">
          <p class="news-meta text-muted mb-2">
            <i class="bi bi-calendar3 text-danger"></i> 02/12/2020 &nbsp;&nbsp;
            <i class="bi bi-eye text-danger"></i> 259
          </p>
          <h6 class="news-title fw-bold">Thị trường Tết thời Covid-19 có gì khác?</h6>
          <p class="news-desc small text-muted mb-0">
            Co.opmart, Co.opXtra, HTVCo.op... đang cố gắng giữ giá bình ổn và tung nhiều chương trình khuyến mãi hấp dẫn.
          </p>
        </div>
      </div>
    </div>

    <!-- Tin 4 -->
    <div class="col-md-3">
      <div class="news-card shadow-sm h-100">
        <img src="https://cafebiz.cafebizcdn.vn/thumb_w/640/pr/2020/1607067642371-0-0-893-1429-crop-1607067648655-63742698431308.jpg"
             class="news-img" alt="tin tức 4">
        <div class="news-body">
          <p class="news-meta text-muted mb-2">
            <i class="bi bi-calendar3 text-danger"></i> 02/12/2020 &nbsp;&nbsp;
            <i class="bi bi-eye text-danger"></i> 180
          </p>
          <h6 class="news-title fw-bold">Độc đáo giỏ quà Tết được thiết kế riêng cho doanh nghiệp</h6>
          <p class="news-desc small text-muted mb-0">
            Người tiêu dùng có xu hướng chọn giỏ quà Tết thiết kế riêng, sang trọng và mang phong cách cá nhân hóa.
          </p>
        </div>
      </div>
    </div>
  </div>
   
</div>

 
      <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <script>
        var swiper = new Swiper(".mySwiper",{
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