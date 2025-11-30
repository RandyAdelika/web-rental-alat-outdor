<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_login();
require_role('customer');

$order_id = isset($_GET['id']) ? $_GET['id'] : null;
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    header('Location: history.php');
    exit;
}

// Fetch Order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan atau bukan milik Anda.");
}

// Fetch Order Items
$stmt_items = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

require_once '../../includes/shop_header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pesanan: <?php echo $order['order_code']; ?></h2>
        <a href="history.php" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Informasi Pengiriman</div>
                <div class="card-body">
                    <p><strong>Alamat:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <p><strong>No. HP:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Metode Pembayaran:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Status Pesanan</div>
                <div class="card-body">
                    <p><strong>Status Pembayaran:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                    <p><strong>Status Order:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></p>
                    <p><strong>Tanggal Order:</strong> <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Periode Sewa:</strong> <?php echo $order['rental_start']; ?> s/d <?php echo $order['rental_end']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Item Sewa</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga/Hari</th>
                            <th>Qty</th>
                            <th>Durasi</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>Rp <?php echo number_format($item['price_per_day'], 0, ',', '.'); ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td><?php echo $item['days']; ?> Hari</td>
                            <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total</strong></td>
                            <td><strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../../includes/shop_footer.php';
?>
