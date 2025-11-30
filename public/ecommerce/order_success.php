<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

$code = isset($_GET['code']) ? $_GET['code'] : '';

require_once '../../includes/shop_header.php';
?>

<div class="text-center py-5">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
    </div>
    <h1 class="fw-bolder">Terima Kasih!</h1>
    <p class="lead">Pesanan Anda telah berhasil dibuat.</p>
    <?php if ($code): ?>
        <div class="alert alert-success d-inline-block px-5">
            Kode Pesanan: <strong><?php echo htmlspecialchars($code); ?></strong>
        </div>
        <p>Silakan simpan kode pesanan ini untuk konfirmasi pembayaran.</p>
    <?php endif; ?>
    <div class="mt-4">
        <a href="index.php" class="btn btn-primary">Kembali ke Katalog</a>
    </div>
</div>

<?php
require_once '../../includes/shop_footer.php';
?>
