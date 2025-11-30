<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_login();
require_role('customer');

$user_id = $_SESSION['user_id'];

// Fetch user orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

require_once '../../includes/shop_header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Riwayat Pesanan Saya</h2>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Anda belum memiliki riwayat pesanan. <a href="index.php">Mulai Sewa</a></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Kode Order</th>
                        <th>Tanggal Order</th>
                        <th>Total Hari</th>
                        <th>Total Biaya</th>
                        <th>Status Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?php echo $o['order_code']; ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($o['created_at'])); ?></td>
                        <td><?php echo $o['total_days']; ?> Hari</td>
                        <td>Rp <?php echo number_format($o['total_amount'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if ($o['payment_status'] == 'paid'): ?>
                                <span class="badge bg-success">Lunas</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?php echo ucfirst($o['payment_status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $status_class = 'secondary';
                            if ($o['status'] == 'new') $status_class = 'primary';
                            if ($o['status'] == 'on_process') $status_class = 'info';
                            if ($o['status'] == 'completed') $status_class = 'success';
                            if ($o['status'] == 'cancelled') $status_class = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $o['status'])); ?></span>
                        </td>
                        <td>
                            <a href="history_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../../includes/shop_footer.php';
?>
