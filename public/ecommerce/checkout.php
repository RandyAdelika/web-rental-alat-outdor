<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_login(); // Must be logged in
require_role('customer'); // Must be customer

if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'];
    $phone = $_POST['phone'];
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];

    // Calculate totals
    $total_amount = 0;
    $total_days = 0; // Just taking the max days or sum? Requirement says "total_days", let's sum or take from first item. Let's sum for now or take max. Usually rental is one period. 
    // But cart allows different dates per item. Let's just sum the days for simplicity in this context or take the max range. 
    // The table schema has `total_days`. Let's assume it's the sum of days of all items or just the duration of the order. 
    // Let's use the max days from the items to represent the "length" of the rental order roughly.
    $max_days = 0;
    $rental_start_min = null;
    $rental_end_max = null;

    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['subtotal'];
        if ($item['days'] > $max_days) $max_days = $item['days'];
        
        if ($rental_start_min === null || $item['start_date'] < $rental_start_min) $rental_start_min = $item['start_date'];
        if ($rental_end_max === null || $item['end_date'] > $rental_end_max) $rental_end_max = $item['end_date'];
    }

    try {
        $pdo->beginTransaction();

        // Generate Order Code
        $order_code = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);

        // Insert Order
        $stmt = $pdo->prepare("INSERT INTO orders (order_code, user_id, order_date, rental_start, rental_end, total_days, total_amount, payment_method, shipping_address, phone, status, payment_status) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, 'new', 'pending')");
        $stmt->execute([
            $order_code, 
            $user_id, 
            $rental_start_min, 
            $rental_end_max, 
            $max_days, 
            $total_amount, 
            $payment_method, 
            $shipping_address, 
            $phone
        ]);
        $order_id = $pdo->lastInsertId();

        // Insert Order Items and Update Stock
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price_per_day, days, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt_log = $pdo->prepare("INSERT INTO stock_log (product_id, user_id, type, qty, description) VALUES (?, ?, 'out', ?, ?)");

        foreach ($_SESSION['cart'] as $item) {
            // Insert Item
            $stmt_item->execute([
                $order_id,
                $item['product_id'],
                $item['qty'],
                $item['price_per_day'],
                $item['days'],
                $item['subtotal']
            ]);

            // Update Stock
            $stmt_stock->execute([$item['qty'], $item['product_id']]);

            // Log Stock
            $stmt_log->execute([
                $item['product_id'],
                $user_id,
                $item['qty'],
                "Order $order_code"
            ]);
        }

        $pdo->commit();

        // Clear cart
        unset($_SESSION['cart']);

        // Redirect to Payment Page
        header("Location: payment.php?id=" . $order_id);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

require_once '../../includes/shop_header.php';
?>
<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_login();
require_role('customer');

if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... kode POST yang sudah ada ...
}

// Hitung total untuk ditampilkan di view
$total_amount = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['subtotal'];
}

require_once '../../includes/shop_header.php';
?>
<div class="container mt-4">
    <h2 class="mb-4">Checkout</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Informasi Pengiriman</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" value="<?php echo current_user_name(); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" name="shipping_address" rows="3" required placeholder="Masukkan alamat lengkap pengiriman..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon / WA</label>
                            <input type="text" class="form-control" name="phone" required placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="transfer_atm">Transfer ATM</option>
                                <option value="qris">QRIS</option>
                                <option value="minimarket">Minimarket (Indomaret/Alfamart)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Buat Pesanan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Ringkasan Pesanan</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div class="d-flex">
                                <?php if (isset($item['image']) && $item['image']): ?>
                                    <img src="/project-web-s5/web-rental-outdor/public/<?php echo $item['image']; ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                <?php else: ?>
                                    <img src="https://dummyimage.com/50x50/dee2e6/6c757d.jpg" alt="img" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                <?php endif; ?>
                                <div>
                                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo $item['qty']; ?> x Rp <?php echo number_format($item['price_per_day'], 0, ',', '.'); ?><br>
                                        Durasi: <?php echo $item['days']; ?> Hari<br>
                                        <span class="text-info"><?php echo $item['start_date']; ?> s/d <?php echo $item['end_date']; ?></span>
                                    </small>
                                </div>
                            </div>
                            <span class="text-muted">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                        </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total (IDR)</span>
                            <strong>Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../../includes/shop_footer.php';
?>
