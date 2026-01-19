<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include __DIR__ . "/../config/db.php";

if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

$stmt = $conn->query("SELECT id_product, products_name, totalquantity, quantitySold FROM products ORDER BY id_product DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalInventory = 0;
$totalSold = 0;

foreach ($products as $product) {
  $totalInventory += (int)$product['totalquantity'];
  $totalSold += (int)$product['quantitySold'];
}

$totalInstock = $totalInventory - $totalSold;
$topChartProducts = array_slice($products, 0, 10);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thống Kê Sản Phẩm</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>


.container-fluid{max-width:1400px;padding:0 30px}

/* HEADER */
.header{text-align:center;color:#fff;margin-bottom:40px;text-shadow:2px 2px 4px rgba(0,0,0,.2)}
.header h1{font-size:2.4rem;font-weight:700;margin-bottom:10px}
.header p{opacity:.9;font-size:1.1rem}

/* STATS */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
  gap:20px;
  margin-bottom:40px
}
.stat-card{
  background:#fff;
  border-radius:15px;
  padding:30px;
  text-align:center;
  box-shadow:0 10px 30px rgba(0,0,0,.15);
  transition:transform .3s ease;
}
.stat-card:hover{transform:translateY(-5px)}
.stat-card .icon{
  font-size:2.5rem;
  margin-bottom:10px;
  color:#D90429;
}
.stat-card .number{font-size:2.2rem;font-weight:700;color:#333}

/* CHARTS – FIX FULL */
.charts-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:30px;
  margin-bottom:40px;
  align-items:stretch;
}

.chart-container{
  background:#fff;
  border-radius:15px;
  padding:30px;
  box-shadow:0 10px 30px rgba(0,0,0,.15);
  display:flex;
  flex-direction:column;
  border-top:5px solid #667eea;
}

.chart-container h3{
  font-weight:700;
  margin-bottom:20px;
  color:#333;
}

.chart-container h3 i{
  color:#D90429;
}

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

/* Doughnut cao hơn chút */
.chart-container:first-child .chart-wrapper{
  min-height:460px;
}

/* TABLE */
.table-container{
  background:#fff;
  border-radius:15px;
  padding:30px;
  box-shadow:0 10px 30px rgba(0,0,0,.15);
  border-top:5px solid #764ba2;
}

.table-container h3{
  color:#333;
  margin-bottom:20px;
  font-weight:700;
}

.table thead{
  background:linear-gradient(135deg,#667eea,#764ba2);
  color:#fff;
}

.table tbody tr:hover{
  background:#f8f9fa;
}

/* RESPONSIVE */
@media(max-width:992px){
  .charts-grid{grid-template-columns:1fr}
}
</style>
</head>

<body>
<div class="container-fluid">

<div class="header">
  <h1><i class="bi bi-graph-up" style="color: #b91c1c;"></i> <span style="color: #b91c1c;">Thống Kê Chi Tiết Sản Phẩm</span></h1>
  <p style="color: #b91c1c;">Tổng quan tồn kho & bán hàng</p>
</div>

<!-- STATS -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="icon text-primary"><i class="bi bi-box"></i></div>
    <div>Tổng tồn kho</div>
    <div class="number"><?=number_format($totalInventory)?></div>
  </div>
  <div class="stat-card">
    <div class="icon text-success"><i class="bi bi-cart-check"></i></div>
    <div>Đã bán</div>
    <div class="number"><?=number_format($totalSold)?></div>
  </div>
  <div class="stat-card">
    <div class="icon text-warning"><i class="bi bi-bag"></i></div>
    <div>Còn lại</div>
    <div class="number"><?=number_format($totalInstock)?></div>
  </div>
  <div class="stat-card">
    <div class="icon text-info"><i class="bi bi-collection"></i></div>
    <div>Sản phẩm</div>
    <div class="number"><?=count($products)?></div>
  </div>
</div>

<!-- CHARTS -->
<div class="charts-grid">
  <div class="chart-container">
    <h3><i class="bi bi-pie-chart"></i> Tồn kho vs Đã bán</h3>
    <div class="chart-wrapper">
      <canvas id="pieChart"></canvas>
    </div>
  </div>

  <div class="chart-container">
    <h3><i class="bi bi-bar-chart"></i> Top 10 sản phẩm</h3>
    <div class="chart-wrapper">
      <canvas id="barChart"></canvas>
    </div>
  </div>
</div>

<!-- TABLE -->
<div class="table-container">
  <h3><i class="bi bi-table"></i> Chi tiết sản phẩm</h3>
  <table class="table table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th><th>Tên</th><th>Tồn</th><th>Bán</th><th>Còn</th><th>Tỷ lệ</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($products as $p):
      $inv=(int)$p['totalquantity'];
      $sold=(int)$p['quantitySold'];
      $ratio=$inv>0?($sold/$inv)*100:0;
    ?>
      <tr>
        <td><?=$p['id_product']?></td>
        <td><?=htmlspecialchars($p['products_name'])?></td>
        <td><?=$inv?></td>
        <td><?=$sold?></td>
        <td><?=$inv-$sold?></td>
        <td>
          <div class="progress" style="height: 25px; background: #e0e0e0;">
            <div class="progress-bar" role="progressbar" style="width: <?=round($ratio,1)?>%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem;">
              <?=round($ratio,1)?>%
            </div>
          </div>
        </td>
      </tr>
    <?php endforeach;?>
    </tbody>
  </table>
</div>

</div>

<script>
// PIE
new Chart(document.getElementById('pieChart'),{
  type:'doughnut',
  data:{
    labels:['Còn lại','Đã bán'],
    datasets:[{
      data:[<?=$totalInstock?>,<?=$totalSold?>],
      backgroundColor:['#0d6efd','#198754'],
      borderWidth:3
    }]
  },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    plugins:{legend:{position:'bottom'}}
  }
});

// BAR
new Chart(document.getElementById('barChart'),{
  type:'bar',
  data:{
    labels:<?=json_encode(array_map(fn($p)=>mb_substr($p['products_name'],0,12),$topChartProducts))?>,
    datasets:[
      {label:'Tồn kho',data:<?=json_encode(array_map(fn($p)=>(int)$p['totalquantity'],$topChartProducts))?>,backgroundColor:'#0d6efd'},
      {label:'Đã bán',data:<?=json_encode(array_map(fn($p)=>(int)$p['quantitySold'],$topChartProducts))?>,backgroundColor:'#198754'}
    ]
  },
  options:{
    indexAxis:'y',
    responsive:true,
    maintainAspectRatio:false,
    scales:{x:{beginAtZero:true}}
  }
});
</script>
</body>
</html>
