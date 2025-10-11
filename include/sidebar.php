<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<div class="col-md-3 col-12">
  <!-- Danh mục sản phẩm -->
  <div class="card mb-3 shadow-sm border-0">
    <div class="card-header bg-danger text-white fw-bold">
      <i class="bi bi-gift-fill me-2"></i> DANH MỤC SẢN PHẨM
    </div>
    <div class="card-body p-2">
      <div class="mb-2">
        <h6 class="fw-bold text-danger mb-1"><i class="bi bi-box-seam me-1"></i> HỘP QUÀ</h6>
        <ul class="list-unstyled small ms-3 mb-0">
          <li><a href="#" class="text-decoration-none text-dark">Từ 1 Triệu đến 2 Triệu</a></li>
          <li><a href="#" class="text-decoration-none text-dark">Từ 700K đến 1 Triệu</a></li>
          <li><a href="#" class="text-decoration-none text-dark">Từ 300K đến 700K</a></li>
        </ul>
      </div>

      <div class="mb-2">
        <h6 class="fw-bold text-danger mb-1"><i class="bi bi-basket2-fill me-1"></i> GIỎ QUÀ</h6>
        <ul class="list-unstyled small ms-3 mb-0">
          <li><a href="" class="text-decoration-none text-dark">Từ 1 Triệu đến 2 Triệu</a></li>
          <li><a href="#" class="text-decoration-none text-dark">Từ 700K đến 1 Triệu</a></li>
          <li><a href="#" class="text-decoration-none text-dark">Từ 300K đến 700K</a></li>
        </ul>
      </div>

      <div>
        <h6 class="fw-bold text-danger mb-1"><i class="bi bi-bag-heart-fill me-1"></i> TÚI QUÀ TẾT</h6>
        <ul class="list-unstyled small ms-3 mb-0">
          <li><a href="#" class="text-decoration-none text-dark">Từ 1 Triệu đến 2 Triệu</a></li>
          <li><a href="#" class="text-decoration-none text-dark">Từ 700K đến 1 Triệu</a></li>
          <li><a href="#" class="text-decoration-none text-dark">Từ 300K đến 700K</a></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Lọc thương hiệu -->
  <div class="card mb-3 shadow-sm border-0">
    <div class="card-header bg-danger text-white fw-bold">
      LỌC THƯƠNG HIỆU
    </div>
    <div class="card-body p-2">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="brand1">
        <label class="form-check-label small" for="brand1">
          Giỏ Quà Tết Việt
        </label>
      </div>
    </div>
  </div>

  <!-- Lọc sản phẩm -->
  <div class="card shadow-sm border-0">
    <div class="card-header bg-danger text-white fw-bold">
      LỌC SẢN PHẨM
    </div>
    <div class="card-body p-2">
      <h6 class="fw-bold small text-uppercase mb-2">Lọc giá</h6>
      <input type="range" class="form-range" min="0" max="5000000" step="100000" value="0" id="priceRange" oninput="updatePriceLabel(this.value)">
      <div class="d-flex justify-content-between small text-muted">
        <span>0đ</span>
        <span id="priceValue">0đ</span>
      </div>

      <hr>
      <h6 class="fw-bold small text-uppercase mb-2">Màu giỏ quà</h6>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="blue">
        <label class="form-check-label small" for="blue">Xanh dương</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="red">
        <label class="form-check-label small" for="red">Đỏ</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="brown">
        <label class="form-check-label small" for="brown">Nâu đỏ</label>
      </div>
    </div>
  </div>
</div>

<!-- JS cập nhật giá trị range -->
<script>
  function updatePriceLabel(value) {
    document.getElementById('priceValue').innerText = new Intl.NumberFormat('vi-VN').format(value) + 'đ';
  }
</script>
