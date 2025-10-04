<?php
require __DIR__ . "/../config/db.php";
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
  <div>
    
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
  </script>
</body>
</html>
