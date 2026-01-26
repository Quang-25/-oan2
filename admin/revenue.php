<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include __DIR__ . "/../config/db.php";

// ==== KIỂM TRA QUYỀN ADMIN ====
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

// ===== THỐNG KÊ DOANH THU =====
// Tổng doanh thu
$totalRevenue = (int)$conn->query("SELECT SUM(totalamount) FROM orders WHERE status = 'approved'")->fetchColumn() ?? 0;

// Doanh thu theo trạng thái
$revenueByStatus = $conn->query("
  SELECT status, SUM(totalamount) as total_amount
  FROM orders
  GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Top sản phẩm bán chạy nhất (theo doanh thu)
$topRevenueProducts = $conn->query("
  SELECT p.products_name, p.id_product, SUM(o.totalamount) as total_revenue, SUM(o.quantity) as total_quantity
  FROM orders o
  JOIN products p ON o.Product_ID = p.id_product
  WHERE o.status = 'approved'
  GROUP BY o.Product_ID
  ORDER BY total_revenue DESC
  LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Doanh thu theo tháng (6 tháng gần nhất)
$revenueByMonth = $conn->query("
  SELECT 
    DATE_FORMAT(order_date, '%Y-%m') as month,
    SUM(totalamount) as total_amount
  FROM orders
  WHERE status = 'approved'
  GROUP BY DATE_FORMAT(order_date, '%Y-%m')
  ORDER BY month DESC
  LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Lợi nhuận theo tháng (tính theo doanh thu * 70% - giả định chi phí 30%)
$profitByMonth = $conn->query("
  SELECT 
    DATE_FORMAT(order_date, '%Y-%m') as month,
    SUM(totalamount) as total_amount,
    ROUND(SUM(totalamount) * 0.7, 0) as profit_amount
  FROM orders
  WHERE status = 'approved'
  GROUP BY DATE_FORMAT(order_date, '%Y-%m')
  ORDER BY month DESC
  LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Thống kê đơn hàng
$approvedOrders = (int)$conn->query("SELECT COUNT(*) FROM orders WHERE status = 'approved'")->fetchColumn();
$cancelledOrders = (int)$conn->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn();
$returnedOrders = (int)$conn->query("SELECT COUNT(*) FROM orders WHERE delivery_status = 'returned'")->fetchColumn();
$returnedAmount = (int)$conn->query("SELECT SUM(totalamount) FROM orders WHERE delivery_status = 'returned'")->fetchColumn() ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thống Kê Doanh Thu</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { background: #f8f9fa; }

.container { max-width: 1200px; margin-top: 30px; }

h1 { color: #b91c1c; font-weight: bold; margin-bottom: 30px; }

/* Stats Cards */
.stat-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,.1);
  margin-bottom: 20px;
  border-left: 5px solid #dc2626;
}

.stat-card .icon { font-size: 2.5rem; color: #dc2626; }
.stat-card .label { font-size: 0.9rem; color: #666; }
.stat-card .number { font-size: 2rem; font-weight: bold; color: #333; }

/* Charts */
.chart-container {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,.1);
  margin-bottom: 30px;
}

.chart-container h3 { color: #b91c1c; font-weight: bold; margin-bottom: 20px; }

.chart-wrapper {
  position: relative;
  height: 350px;
  margin-bottom: 20px;
}

/* Table */
.table-container {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,.1);
  margin-bottom: 30px;
}

.table-container h3 { color: #b91c1c; font-weight: bold; margin-bottom: 15px; }

.table th { background: #dc2626; color: white; }

.badge-approved { background: #198754; }
.badge-cancelled { background: #dc2626; }

@media(max-width:768px) {
  .chart-wrapper { height: 250px; }
}
</style>
</head>

<body>
<div class="container">

<h1><i class="bi bi-graph-up-arrow"></i> Thống Kê Doanh Thu</h1>

<!-- Stats Grid -->
<div class="row">
  <div class="col-md-4">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-cash-coin"></i></div>
      <div class="label">Tổng Doanh Thu</div>
      <div class="number"><?=number_format($totalRevenue, 0, ',', '.')?>₫</div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-check-circle"></i></div>
      <div class="label">Đơn Hàng Thành Công</div>
      <div class="number"><?=$approvedOrders?></div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-x-circle"></i></div>
      <div class="label">Đơn Hàng Hủy</div>
      <div class="number"><?=$cancelledOrders?></div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-arrow-counterclockwise"></i></div>
      <div class="label">Đơn Hàng Hoàn Trả</div>
      <div class="number"><?=$returnedOrders?></div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-cash-coin"></i></div>
      <div class="label">Tiền Hoàn Trả</div>
      <div class="number"><?=number_format($returnedAmount, 0, ',', '.')?>₫</div>
    </div>
  </div>

<!-- Charts -->
<div class="row">
  <!-- Doanh Thu Theo Tháng -->
  <div class="col-md-12">
    <div class="chart-container">
      <h3><i class="bi bi-bar-chart"></i> Doanh Thu & Lợi Nhuận Theo Tháng</h3>
      <div class="chart-wrapper">
        <canvas id="monthChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Top Products -->
<div class="table-container">
  <h3><i class="bi bi-fire"></i> Top 10 Sản Phẩm Bán Chạy Nhất (Theo Doanh Thu)</h3>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Sản Phẩm</th>
          <th class="text-end">Số Lượng Bán</th>
          <th class="text-end">Doanh Thu</th>
          <th class="text-end">% Doanh Thu</th>
        </tr>
      </thead>
      <tbody>
        <?php 
          $totalProductRevenue = array_sum(array_column($topRevenueProducts, 'total_revenue'));
          foreach ($topRevenueProducts as $product): 
            $percentage = $totalProductRevenue > 0 ? ($product['total_revenue'] / $totalProductRevenue) * 100 : 0;
        ?>
          <tr>
            <td><strong><?=htmlspecialchars($product['products_name'])?></strong></td>
            <td class="text-end"><span class="badge bg-info"><?=$product['total_quantity']?></span></td>
            <td class="text-end"><?=number_format($product['total_revenue'], 0, ',', '.')?>₫</td>
            <td class="text-end">
              <div class="progress" style="height: 25px;">
                <div class="progress-bar bg-success" style="width: <?=$percentage?>%">
                  <?=round($percentage, 1)?>%
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Revenue Details -->
<div class="table-container">
  <h3><i class="bi bi-table"></i> Doanh Thu Theo Trạng Thái Chi Tiết</h3>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Trạng Thái</th>
          <th class="text-end">Doanh Thu</th>
          <th class="text-end">% Tổng</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($revenueByStatus as $status): 
          $percentage = $totalRevenue > 0 ? ($status['total_amount'] / $totalRevenue) * 100 : 0;
        ?>
          <tr>
            <td>
              <?php if($status['status'] === 'approved'): ?>
                <span class="badge badge-approved">✓ Thành Công</span>
              <?php else: ?>
                <span class="badge badge-cancelled">✕ Hủy</span>
              <?php endif; ?>
            </td>
            <td class="text-end"><strong><?=number_format($status['total_amount'], 0, ',', '.')?>₫</strong></td>
            <td class="text-end"><?=round($percentage, 1)?>%</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<script>
// === Doanh Thu & Lợi Nhuận Theo Tháng ===
const monthData = <?=json_encode(array_reverse($profitByMonth))?>;
const monthLabels = monthData.map(m => m.month);
const monthAmounts = monthData.map(m => parseInt(m.total_amount));
const monthProfit = monthData.map(m => parseInt(m.profit_amount));

new Chart(document.getElementById('monthChart'), {
  type: 'line',
  data: {
    labels: monthLabels,
    datasets: [
      {
        label: 'Doanh Thu',
        data: monthAmounts,
        borderColor: '#dc2626',
        backgroundColor: 'rgba(220, 38, 38, 0.1)',
        borderWidth: 3,
        tension: 0.4,
        fill: true,
        pointRadius: 8,
        pointBackgroundColor: '#dc2626',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 10
      },
      {
        label: 'Lợi Nhuận',
        data: monthProfit,
        borderColor: '#22c55e',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderWidth: 3,
        tension: 0.4,
        fill: true,
        pointRadius: 8,
        pointBackgroundColor: '#22c55e',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 10
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: { 
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return value.toLocaleString('vi-VN');
          }
        }
      }
    },
    plugins: {
      legend: { display: true, position: 'top' },
      tooltip: {
        callbacks: {
          label: function(context) {
            return context.dataset.label + ': ' + context.parsed.y.toLocaleString('vi-VN') + '₫';
          }
        }
      }
    }
  }
});
</script>

</body>
</html>
