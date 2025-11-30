<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_login();

$order_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$order_id) {
    header('Location: index.php');
    exit;
}

// Fetch Order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

require_once '../../includes/shop_header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Instruksi Pembayaran</h4>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title">Total Tagihan</h5>
                    <h2 class="text-primary mb-4">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></h2>
                    
                    <p class="text-muted">Kode Pesanan: <strong><?php echo $order['order_code']; ?></strong></p>
                    <hr>

                    <?php if ($order['payment_method'] == 'transfer_atm'): ?>
                        <div class="payment-instruction">
                            <h5>Transfer Bank (ATM / Mobile Banking)</h5>
                            <p>Silakan transfer ke rekening berikut:</p>
                            <div class="alert alert-info d-inline-block">
                                <strong>Bank BCA</strong><br>
                                No. Rek: <strong>123-456-7890</strong><br>
                                A/N: Rental Outdoor
                            </div>
                            <div class="text-start mt-3 mx-auto" style="max-width: 500px;">
                                <h6>Cara Pembayaran:</h6>
                                <ol>
                                    <li>Masukkan kartu ATM dan PIN Anda.</li>
                                    <li>Pilih menu Transaksi Lainnya > Transfer > Ke Rek BCA.</li>
                                    <li>Masukkan No. Rekening <strong>123-456-7890</strong>.</li>
                                    <li>Masukkan jumlah <strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>.</li>
                                    <li>Simpan bukti transfer.</li>
                                </ol>
                            </div>
                        </div>

                    <?php elseif ($order['payment_method'] == 'qris'): ?>
                        <div class="payment-instruction">
                            <h5>Scan QRIS</h5>
                            <p>Scan kode QR di bawah ini menggunakan aplikasi e-wallet (GoPay, OVO, Dana, ShopeePay) atau Mobile Banking:</p>
                            <div class="mb-4">
                                <!-- Placeholder QR Code -->
                                <img src="https://dummyimage.com/250x250/000/fff&text=QRIS+CODE" alt="QRIS Code" class="img-fluid border p-2">
                            </div>
                            <div class="text-start mt-3 mx-auto" style="max-width: 500px;">
                                <h6>Cara Pembayaran:</h6>
                                <ol>
                                    <li>Buka aplikasi pembayaran Anda.</li>
                                    <li>Pilih menu Scan / Bayar.</li>
                                    <li>Arahkan kamera ke kode QR di atas.</li>
                                    <li>Periksa nama merchant <strong>Rental Outdoor</strong> dan total tagihan.</li>
                                    <li>Masukkan PIN Anda untuk konfirmasi.</li>
                                </ol>
                            </div>
                        </div>

                    <?php elseif ($order['payment_method'] == 'minimarket'): ?>
                        <div class="payment-instruction">
                            <h5>Pembayaran Minimarket (Indomaret / Alfamart)</h5>
                            <p>Tunjukkan kode pembayaran berikut ke kasir:</p>
                            <div class="alert alert-warning d-inline-block">
                                Kode Pembayaran:<br>
                                <strong class="fs-4">PAY-<?php echo rand(100000, 999999); ?></strong>
                            </div>
                            <div class="text-start mt-3 mx-auto" style="max-width: 500px;">
                                <h6>Cara Pembayaran:</h6>
                                <ol>
                                    <li>Pergi ke gerai Indomaret atau Alfamart terdekat.</li>
                                    <li>Bilang ke kasir ingin membayar tagihan "Rental Outdoor".</li>
                                    <li>Tunjukkan Kode Pembayaran di atas.</li>
                                    <li>Bayar sesuai total tagihan: <strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>.</li>
                                    <li>Simpan struk sebagai bukti pembayaran.</li>
                                </ol>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr class="mt-4">
                    <p class="small text-muted">Setelah melakukan pembayaran, silakan konfirmasi.</p>
                    <a href="order_success.php?code=<?php echo $order['order_code']; ?>" class="btn btn-success btn-lg">Saya Sudah Bayar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../../includes/shop_footer.php';
?>
