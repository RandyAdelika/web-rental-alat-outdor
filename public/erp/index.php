<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_role('admin');

require_once '../../includes/admin_header.php';
?>

<h1 class="mt-4">Dashboard</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>

<div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-area me-1"></i>
                Grafik Pemasukan Bulanan
            </div>
            <div class="card-body"><canvas id="myAreaChart" width="100%" height="40"></canvas></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i>
                Grafik Transaksi Bulanan
            </div>
            <div class="card-body"><canvas id="myBarChart" width="100%" height="40"></canvas></div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie me-1"></i>
                Produk Terlaris
            </div>
            <div class="card-body"><canvas id="myPieChart" width="100%" height="40"></canvas></div>
        </div>
    </div>
</div>

<script>
// Helper to fetch data
async function fetchData(type) {
    const response = await fetch('charts_data.php?type=' + type);
    return await response.json();
}

document.addEventListener('DOMContentLoaded', async function() {
    // Pemasukan Chart
    const incomeData = await fetchData('pemasukan');
    const incomeLabels = incomeData.map(d => d.month);
    const incomeValues = incomeData.map(d => d.total);

    new Chart(document.getElementById("myAreaChart"), {
        type: 'line',
        data: {
            labels: incomeLabels,
            datasets: [{
                label: "Pemasukan",
                data: incomeValues,
                borderColor: "rgba(2,117,216,1)",
                backgroundColor: "rgba(2,117,216,0.2)",
                fill: true,
            }],
        },
    });

    // Transaksi Chart
    const txData = await fetchData('transaksi');
    const txLabels = txData.map(d => d.month);
    const txValues = txData.map(d => d.total);

    new Chart(document.getElementById("myBarChart"), {
        type: 'bar',
        data: {
            labels: txLabels,
            datasets: [{
                label: "Jumlah Transaksi",
                data: txValues,
                backgroundColor: "rgba(2,117,216,1)",
            }],
        },
    });

    // Barang Terlaris Chart
    const productData = await fetchData('barang');
    const productLabels = productData.map(d => d.name);
    const productValues = productData.map(d => d.total_qty);

    new Chart(document.getElementById("myPieChart"), {
        type: 'pie',
        data: {
            labels: productLabels,
            datasets: [{
                data: productValues,
                backgroundColor: ['#007bff', '#dc3545', '#ffc107', '#28a745', '#17a2b8'],
            }],
        },
    });
});
</script>

<?php
require_once '../../includes/admin_footer.php';
?>
