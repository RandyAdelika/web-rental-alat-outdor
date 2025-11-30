<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_role('admin');

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
    $stmt->execute([$status, $payment_status, $order_id]);
    
    // If cancelled, maybe return stock? (Skipped for simplicity as per request focus)
    
    header('Location: transactions.php');
    exit;
}

// Fetch Orders
$stmt = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

require_once '../../includes/admin_header.php';
?>

<h1 class="mt-4">Riwayat Transaksi</h1>
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Daftar Pesanan
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode Order</th>
                    <th>Customer</th>
                    <th>Tgl Order</th>
                    <th>Periode Sewa</th>
                    <th>Total</th>
                    <th>Metode Bayar</th>
                    <th>Status Bayar</th>
                    <th>Status Order</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?php echo $o['order_code']; ?></td>
                    <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                    <td><?php echo $o['created_at']; ?></td>
                    <td><?php echo $o['rental_start']; ?> s/d <?php echo $o['rental_end']; ?></td>
                    <td>Rp <?php echo number_format($o['total_amount'], 0, ',', '.'); ?></td>
                    <td><?php echo $o['payment_method']; ?></td>
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                        <td>
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="pending" <?php echo $o['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $o['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="cancelled" <?php echo $o['payment_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <select name="status" class="form-select form-select-sm">
                                <option value="new" <?php echo $o['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="on_process" <?php echo $o['status'] == 'on_process' ? 'selected' : ''; ?>>On Process</option>
                                <option value="completed" <?php echo $o['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $o['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once '../../includes/admin_footer.php';
?>
