<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include __DIR__ . "/../config/db.php";

// ==== KIỂM TRA QUYỀN ADMIN ====
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

// ===== THỐNG KÊ =====
$totalProducts   = (int)$conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalInventory  = (int)$conn->query("SELECT SUM(totalquantity) FROM products")->fetchColumn();
$totalSold       = (int)$conn->query("SELECT SUM(quantitySold) FROM products")->fetchColumn();
$totalRemaining  = $totalInventory - $totalSold;

// Đơn hàng
$totalOrders = (int)$conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Người dùng
$totalUsers = (int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Top bán chạy
$topProducts = $conn->query("
  SELECT products_name, quantitySold, price
  FROM products
  WHERE quantitySold > 0
  ORDER BY quantitySold DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Tồn kho cao
$inventoryProducts = $conn->query("
  SELECT products_name, totalquantity, quantitySold
  FROM products
  ORDER BY totalquantity DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Biểu đồ
$chartProducts = $conn->query("
  SELECT products_name, totalquantity, quantitySold
  FROM products
  ORDER BY id_product DESC
  LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{background:#f8f9fa}

/* ===== DASHBOARD ===== */
.dashboard-container{padding:30px}
.dashboard-header{margin-bottom:40px}
.dashboard-header h1{font-weight:700}

/* ===== STATS ===== */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
  gap:20px;
  margin-bottom:40px
}
.stat-card{
  background:#fff;
  border-radius:12px;
  padding:25px;
  box-shadow:0 2px 8px rgba(0,0,0,.08);
  border-left:5px solid #0d6efd
}
.stat-card .icon{font-size:2rem}
.stat-card:nth-child(1) .icon{color:#0d6efd}
.stat-card:nth-child(2) .icon{color:#198754}
.stat-card:nth-child(3) .icon{color:#ffc107}
.stat-card:nth-child(4) .icon{color:#0dcaf0}
.stat-card .label{font-size:.9rem;color:#999}
.stat-card .number{font-size:2rem;font-weight:700}

/* ===== CHARTS FIX FULL ===== */
.charts-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:25px;
  margin-bottom:40px
}

.chart-container{
  background:#fff;
  border-radius:12px;
  padding:25px;
  box-shadow:0 2px 8px rgba(0,0,0,.08);
  display:flex;
  flex-direction:column;
}

.chart-container h3{font-weight:700;margin-bottom:15px}

.chart-wrapper{
  position:relative;
  width:100%;
  flex:1;
  min-height:420px;
}

.chart-wrapper canvas{
  width:100%!important;
  height:100%!important;
}

/* Doughnut đẹp hơn */
.chart-container:first-child .chart-wrapper{
  min-height:460px;
}

/* ===== TABLE ===== */
.tables-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:25px;
  margin-bottom:40px
}
.table-container{
  background:#fff;
  border-radius:12px;
  padding:25px;
  box-shadow:0 2px 8px rgba(0,0,0,.08)
}
.table-container h3 i{color:#D90429}
.table-custom th, .table-custom td{padding:12px}

/* ===== RESPONSIVE ===== */
@media(max-width:1200px){
  .charts-grid,.tables-grid{grid-template-columns:1fr}
}
</style>
</head>

<body>
<div class="dashboard-container">

<div class="dashboard-header">
  <h1><i class="bi bi-graph-up"></i> Dashboard Thống Kê</h1>
  <p>Tổng quan hệ thống bán hàng</p>
</div>

<!-- STATS -->
<div class="stats-grid">
  <div class="stat-card"><div class="icon"><i class="bi bi-box"></i></div><div class="label">Sản phẩm</div><div class="number"><?=number_format($totalProducts)?></div></div>
  <div class="stat-card"><div class="icon"><i class="bi bi-box-seam"></i></div><div class="label">Tồn kho</div><div class="number"><?=number_format($totalInventory)?></div></div>
  <div class="stat-card"><div class="icon"><i class="bi bi-cart-check"></i></div><div class="label">Đã bán</div><div class="number"><?=number_format($totalSold)?></div></div>
  <div class="stat-card"><div class="icon"><i class="bi bi-people"></i></div><div class="label">Khách hàng</div><div class="number"><?=number_format($totalUsers)?></div></div>
</div>

<!-- CHARTS -->
<div class="charts-grid">
  <div class="chart-container">
    <h3><i class="bi bi-pie-chart"></i> Hàng còn lại vs Đã bán</h3>
    <div class="chart-wrapper"><canvas id="inventoryChart"></canvas></div>
  </div>

  <div class="chart-container">
    <h3><i class="bi bi-bar-chart"></i> Top 10 sản phẩm</h3>
    <div class="chart-wrapper"><canvas id="topProductsChart"></canvas></div>
  </div>
</div>

<!-- TABLES -->
<div class="tables-grid">
  <div class="table-container">
    <h3><i class="bi bi-fire"></i> Sản Phẩm Bán Chạy</h3>
    <table class="table-custom table table-hover">
      <thead class="table-light">
        <tr>
          <th>Sản Phẩm</th>
          <th>Đã Bán</th>
          <th>Giá</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topProducts as $product): ?>
          <tr>
            <td><strong><?= htmlspecialchars(substr($product['products_name'], 0, 25)) ?></strong></td>
            <td><span class="badge bg-success"><?= number_format($product['quantitySold']) ?></span></td>
            <td><?= number_format($product['price'], 0, ',', '.') ?>đ</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="table-container">
    <h3><i class="bi bi-box2-heart"></i> Tồn Kho Cao</h3>
    <table class="table-custom table table-hover">
      <thead class="table-light">
        <tr>
          <th>Sản Phẩm</th>
          <th>Tồn Kho</th>
          <th>Tỉ Lệ</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inventoryProducts as $product): 
          $inventory = intval($product['totalquantity']);
          $sold = intval($product['quantitySold']);
          $ratio = $inventory > 0 ? ($sold / $inventory) * 100 : 0;
        ?>
          <tr>
            <td><strong><?= htmlspecialchars(substr($product['products_name'], 0, 25)) ?></strong></td>
            <td><span class="badge bg-info text-white"><?= number_format($inventory) ?></span></td>
            <td>
              <div class="progress" style="height: 35px; background: #e0e0e0; border-radius: 8px;">
                <div class="progress-bar bg-danger" style="width: <?= $ratio ?>%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1rem; min-width: 70px; border-radius: 8px;">
                  <?= round($ratio, 1) ?>%
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- QUICK LINKS -->
<div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08)">
  <h3 style="margin-bottom:20px"><i class="bi bi-lightning-fill"></i> Truy Cập Nhanh</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px">
    <a href="?page=products" class="btn btn-outline-primary">
      <i class="bi bi-box2"></i> Quản Lý Sản Phẩm
    </a>
    <a href="?page=statistics" class="btn btn-outline-success">
      <i class="bi bi-pie-chart"></i> Thống Kê Chi Tiết
    </a>
    <a href="?page=oders" class="btn btn-outline-warning">
      <i class="bi bi-cart-check"></i> Đơn Hàng
    </a>
    <a href="?page=User" class="btn btn-outline-info">
      <i class="bi bi-people"></i> Người Dùng
    </a>
  </div>
</div>

</div>

<script>
// ===== DOUGHNUT =====
new Chart(document.getElementById('inventoryChart'),{
  type:'doughnut',
  data:{
    labels:['Đã bán','Còn lại'],
    datasets:[{
      data:[<?=$totalSold?>,<?=$totalRemaining?>],
      backgroundColor:['#198754','#0d6efd'],
      borderWidth:2
    }]
  },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    plugins:{
      legend:{position:'bottom'},
      tooltip:{
        callbacks:{
          label:function(context){
            let label = context.label || '';
            if(label) label += ': ';
            let total = context.dataset.data.reduce((a,b)=>a+b,0);
            let percentage = ((context.parsed / total) * 100).toFixed(1);
            label += context.parsed + ' (' + percentage + '%)';
            return label;
          }
        }
      }
    }
  }
});

// ===== BAR =====
const dataBar = <?=json_encode(array_map(fn($p)=>[
  'name'=>mb_substr($p['products_name'],0,15),
  'inventory'=>(int)$p['totalquantity'],
  'sold'=>(int)$p['quantitySold']
],$chartProducts))?>;

new Chart(document.getElementById('topProductsChart'),{
  type:'bar',
  data:{
    labels:dataBar.map(i=>i.name),
    datasets:[
      {label:'Tồn kho',data:dataBar.map(i=>i.inventory),backgroundColor:'#0d6efd'},
      {label:'Đã bán',data:dataBar.map(i=>i.sold),backgroundColor:'#198754'}
    ]
  },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    scales:{y:{beginAtZero:true}},
    plugins:{
      tooltip:{
        callbacks:{
          label:function(context){
            return context.dataset.label + ': ' + context.parsed.y;
          }
        }
      }
    }
  }
});
</script>

</body>
</html>
