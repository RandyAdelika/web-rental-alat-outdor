<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch product
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        header('Location: ../login.php');
        exit;
    }

    $qty = (int)$_POST['qty'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Validate dates
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $diff = $start->diff($end);
    $days = $diff->days;
    
    // Minimal 1 day rental. If same day, count as 1 day.
    if ($days == 0) $days = 1;
    if ($end < $start) {
        $error = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        // Add to session cart
        $item = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price_per_day' => $product['price_per_day'],
            'qty' => $qty,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'days' => $days,
            'subtotal' => $product['price_per_day'] * $qty * $days
        ];

        $_SESSION['cart'][] = $item;
        header('Location: cart.php');
        exit;
    }
}

require_once '../../includes/shop_header.php';
?>

<div class="row gx-4 gx-lg-5 align-items-center">
    <div class="col-md-6">
        <?php if ($product['image']): ?>
            <img class="card-img-top mb-5 mb-md-0" src="/project-web-s5/web-rental-outdor/public/assets/uploads/products/<?php echo $product['image']; ?>" alt="..." />
        <?php else: ?>
            <img class="card-img-top mb-5 mb-md-0" src="https://dummyimage.com/600x700/dee2e6/6c757d.jpg" alt="..." />
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <div class="small mb-1">Kategori: <?php echo htmlspecialchars($product['category_name']); ?></div>
        <h1 class="display-5 fw-bolder"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="fs-5 mb-5">
            <span>Rp <?php echo number_format($product['price_per_day'], 0, ',', '.'); ?> / hari</span>
        </div>
        <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <p>Stok Tersedia: <strong><?php echo $product['stock']; ?></strong></p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control" name="end_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="d-flex">
                <input class="form-control text-center me-3" id="inputQuantity" type="number" name="qty" value="1" min="1" max="<?php echo $product['stock']; ?>" style="max-width: 3rem" />
                <button class="btn btn-outline-dark flex-shrink-0" type="submit">
                    <i class="bi-cart-fill me-1"></i>
                    Tambah ke Keranjang
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once '../../includes/shop_footer.php';
?>
