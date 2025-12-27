<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_role('admin');

// Get filter parameters
$filter_period = isset($_GET['period']) ? $_GET['period'] : 'all';
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'highest';

// Date range based on filter
$date_condition = "1=1";
if ($filter_period == 'month') {
    $date_condition = "DATE_FORMAT(o.order_date, '%Y-%m') = '$filter_month'";
} elseif ($filter_period == 'year') {
    $date_condition = "YEAR(o.order_date) = '$filter_year'";
}

// ========== PENDAPATAN (REVENUE) ==========
$stmt_revenue = $pdo->query("
    SELECT 
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue,
        SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_revenue,
        COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_orders,
        COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_orders
    FROM orders o
    WHERE $date_condition
");
$revenue_data = $stmt_revenue->fetch();

$total_revenue = $revenue_data['paid_revenue'] ?? 0;
$pending_revenue = $revenue_data['pending_revenue'] ?? 0;
$paid_orders = $revenue_data['paid_orders'] ?? 0;
$pending_orders = $revenue_data['pending_orders'] ?? 0;

// ========== BIAYA OPERASIONAL (OPERATIONAL COSTS) ==========
// Untuk contoh, kita set biaya operasional sebagai persentase dari revenue
// Dalam praktik nyata, ini bisa dari tabel terpisah untuk expenses
$operational_cost_percentage = 0.30; // 30% dari revenue
$operational_costs = $total_revenue * $operational_cost_percentage;

// ========== HARGA POKOK PENJUALAN / HPP (COST OF GOODS) ==========
// Untuk rental, kita hitung sebagai biaya maintenance dan depresiasi
// Misalnya 20% dari revenue sebagai cost
$cogs_percentage = 0.20; // 20%
$cogs = $total_revenue * $cogs_percentage;

// ========== LABA KOTOR (GROSS PROFIT) ==========
$gross_profit = $total_revenue - $cogs;
$gross_profit_margin = $total_revenue > 0 ? ($gross_profit / $total_revenue) * 100 : 0;

// ========== LABA BERSIH (NET PROFIT) ==========
$net_profit = $gross_profit - $operational_costs;
$net_profit_margin = $total_revenue > 0 ? ($net_profit / $total_revenue) * 100 : 0;

// ========== MODAL & ROI ==========
$initial_capital = 50000000; // Modal awal (bisa disesuaikan atau dari database)
$roi = $initial_capital > 0 ? ($net_profit / $initial_capital) * 100 : 0;

// ========== PRODUK TERLARIS/TERENDAH ==========
$sort_order = $sort_by == 'highest' ? 'DESC' : 'ASC';
$stmt_products = $pdo->query("
    SELECT 
        p.id,
        p.name,
        p.price_per_day,
        SUM(oi.qty) as total_qty,
        SUM(oi.subtotal) as total_revenue,
        COUNT(DISTINCT oi.order_id) as order_count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.payment_status = 'paid' AND $date_condition
    GROUP BY p.id
    ORDER BY total_revenue $sort_order
");
$products_data = $stmt_products->fetchAll();

// ========== TRANSAKSI PER BULAN (untuk chart) ==========
$stmt_monthly = $pdo->query("
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') as month,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as revenue,
        COUNT(*) as transaction_count
    FROM orders
    WHERE YEAR(order_date) = '$filter_year'
    GROUP BY month
    ORDER BY month ASC
");
$monthly_data = $stmt_monthly->fetchAll();

require_once '../../includes/admin_header.php';
?>

<h1 class="mt-4">Laporan Keuangan</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Laporan Keuangan</li>
</ol>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-1"></i>
        Filter Laporan
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Periode</label>
                <select name="period" class="form-select" id="periodSelect">
                    <option value="all" <?php echo $filter_period == 'all' ? 'selected' : ''; ?>>Semua Waktu</option>
                    <option value="month" <?php echo $filter_period == 'month' ? 'selected' : ''; ?>>Per Bulan</option>
                    <option value="year" <?php echo $filter_period == 'year' ? 'selected' : ''; ?>>Per Tahun</option>
                </select>
            </div>
            <div class="col-md-3" id="monthFilter" style="display: <?php echo $filter_period == 'month' ? 'block' : 'none'; ?>;">
                <label class="form-label">Bulan</label>
                <input type="month" name="month" class="form-control" value="<?php echo $filter_month; ?>">
            </div>
            <div class="col-md-3" id="yearFilter" style="display: <?php echo $filter_period == 'year' ? 'block' : 'none'; ?>;">
                <label class="form-label">Tahun</label>
                <input type="number" name="year" class="form-control" value="<?php echo $filter_year; ?>" min="2020" max="2099">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sort Produk</label>
                <select name="sort" class="form-select">
                    <option value="highest" <?php echo $sort_by == 'highest' ? 'selected' : ''; ?>>Penjualan Tertinggi</option>
                    <option value="lowest" <?php echo $sort_by == 'lowest' ? 'selected' : ''; ?>>Penjualan Terendah</option>
                </select>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tampilkan Laporan
                </button>
                <a href="financial_report.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
                <button type="button" class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <h6>Total Pendapatan (Paid)</h6>
                <h4>Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h4>
                <small><?php echo $paid_orders; ?> Transaksi Lunas</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <h6>Laba Kotor</h6>
                <h4>Rp <?php echo number_format($gross_profit, 0, ',', '.'); ?></h4>
                <small>Margin: <?php echo number_format($gross_profit_margin, 2); ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <h6>Laba Bersih</h6>
                <h4>Rp <?php echo number_format($net_profit, 0, ',', '.'); ?></h4>
                <small>Margin: <?php echo number_format($net_profit_margin, 2); ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white mb-4">
            <div class="card-body">
                <h6>Pendapatan Pending</h6>
                <h4>Rp <?php echo number_format($pending_revenue, 0, ',', '.'); ?></h4>
                <small><?php echo $pending_orders; ?> Transaksi Pending</small>
            </div>
        </div>
    </div>
</div>

<!-- Laporan Laba Rugi -->
<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-line me-1"></i>
                Laporan Laba Rugi (Income Statement)
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Pendapatan (Revenue)</strong></td>
                        <td class="text-end"><strong>Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="ps-4">Transaksi Lunas</td>
                        <td class="text-end">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Harga Pokok Penjualan (COGS)</strong></td>
                        <td class="text-end text-danger">(Rp <?php echo number_format($cogs, 0, ',', '.'); ?>)</td>
                    </tr>
                    <tr class="table-warning">
                        <td><strong>LABA KOTOR</strong></td>
                        <td class="text-end"><strong>Rp <?php echo number_format($gross_profit, 0, ',', '.'); ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong>Biaya Operasional</strong></td>
                        <td class="text-end text-danger">(Rp <?php echo number_format($operational_costs, 0, ',', '.'); ?>)</td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>LABA BERSIH</strong></td>
                        <td class="text-end"><strong>Rp <?php echo number_format($net_profit, 0, ',', '.'); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-wallet me-1"></i>
                Informasi Modal & ROI
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Modal Awal</strong></td>
                        <td class="text-end">Rp <?php echo number_format($initial_capital, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Pendapatan</strong></td>
                        <td class="text-end">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Biaya</strong></td>
                        <td class="text-end text-danger">Rp <?php echo number_format($cogs + $operational_costs, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Laba Bersih</strong></td>
                        <td class="text-end text-success">Rp <?php echo number_format($net_profit, 0, ',', '.'); ?></td>
                    </tr>
                    <tr class="table-info">
                        <td><strong>Return on Investment (ROI)</strong></td>
                        <td class="text-end"><strong><?php echo number_format($roi, 2); ?>%</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Modal + Keuntungan</strong></td>
                        <td class="text-end"><strong>Rp <?php echo number_format($initial_capital + $net_profit, 0, ',', '.'); ?></strong></td>
                    </tr>
                </table>
                <div class="alert alert-info mt-3">
                    <small><i class="fas fa-info-circle"></i> ROI menunjukkan persentase keuntungan bersih terhadap modal awal yang diinvestasikan.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Produk Terlaris/Terendah -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-trophy me-1"></i>
        Performa Produk - <?php echo $sort_by == 'highest' ? 'Penjualan Tertinggi' : 'Penjualan Terendah'; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Ranking</th>
                        <th>Nama Produk</th>
                        <th>Harga/Hari</th>
                        <th>Jumlah Disewa</th>
                        <th>Total Transaksi</th>
                        <th>Total Pendapatan</th>
                        <th>Kontribusi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($products_data as $prod): 
                        $contribution = $total_revenue > 0 ? ($prod['total_revenue'] / $total_revenue) * 100 : 0;
                    ?>
                    <tr>
                        <td>
                            <?php if ($sort_by == 'highest' && $rank <= 3): ?>
                                <span class="badge bg-warning text-dark">#<?php echo $rank; ?></span>
                            <?php else: ?>
                                <?php echo $rank; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($prod['name']); ?></td>
                        <td>Rp <?php echo number_format($prod['price_per_day'], 0, ',', '.'); ?></td>
                        <td><?php echo $prod['total_qty']; ?> unit</td>
                        <td><?php echo $prod['order_count']; ?> transaksi</td>
                        <td><strong>Rp <?php echo number_format($prod['total_revenue'], 0, ',', '.'); ?></strong></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?php echo $contribution; ?>%">
                                    <?php echo number_format($contribution, 1); ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php 
                    $rank++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Pendapatan Bulanan -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-chart-bar me-1"></i>
        Grafik Pendapatan Bulanan (Tahun <?php echo $filter_year; ?>)
    </div>
    <div class="card-body">
        <canvas id="revenueChart" height="80"></canvas>
    </div>
</div>

<script>
// Filter Period Toggle
document.getElementById('periodSelect').addEventListener('change', function() {
    const period = this.value;
    document.getElementById('monthFilter').style.display = period === 'month' ? 'block' : 'none';
    document.getElementById('yearFilter').style.display = period === 'year' ? 'block' : 'none';
});

// Revenue Chart
const monthlyData = <?php echo json_encode($monthly_data); ?>;
const labels = monthlyData.map(d => d.month);
const revenues = monthlyData.map(d => parseFloat(d.revenue));

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: revenues,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Pendapatan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>

<style>
@media print {
    .sidebar, .navbar, .breadcrumb, .card-header button, form, .btn {
        display: none !important;
    }
    .card {
        border: 1px solid #000;
        page-break-inside: avoid;
    }
}
</style>

<?php
require_once '../../includes/admin_footer.php';
?>