<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

// Handle Remove Item
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        // Re-index array
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit;
}

require_once '../../includes/shop_header.php';
?>

<h2 class="mb-4">Keranjang Sewa</h2>

<?php if (empty($_SESSION['cart'])): ?>
    <div class="alert alert-info">Keranjang belanja Anda kosong. <a href="index.php">Kembali ke Katalog</a></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Tgl Sewa</th>
                    <th>Durasi</th>
                    <th>Qty</th>
                    <th>Harga/Hari</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_amount = 0;
                foreach ($_SESSION['cart'] as $key => $item): 
                    $total_amount += $item['subtotal'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['start_date']; ?> s/d <?php echo $item['end_date']; ?></td>
                    <td><?php echo $item['days']; ?> Hari</td>
                    <td><?php echo $item['qty']; ?></td>
                    <td>Rp <?php echo number_format($item['price_per_day'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="cart.php?remove=<?php echo $key; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus item ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="5" class="text-end"><strong>Total</strong></td>
                    <td colspan="2"><strong>Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="index.php" class="btn btn-secondary">Lanjut Belanja</a>
        <a href="checkout.php" class="btn btn-success">Checkout</a>
    </div>
<?php endif; ?>

<?php
require_once '../../includes/shop_footer.php';
?>
