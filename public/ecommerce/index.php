<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

// Fetch Categories
$stmt_cat = $pdo->query("SELECT * FROM categories");
$categories = $stmt_cat->fetchAll();

// Fetch active products with filter
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'";
$params = [];

if ($category_id) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}

$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once '../../includes/shop_header.php';
?>

<div class="container mt-4 mb-5">
    <!-- Category Filter -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-10 text-center">
            <h5 class="mb-3">Kategori</h5>
            <a href="index.php" class="btn btn-<?php echo !$category_id ? 'dark' : 'outline-dark'; ?> m-1 rounded-pill">Semua</a>
            <?php foreach ($categories as $cat): ?>
                <a href="index.php?category_id=<?php echo $cat['id']; ?>" class="btn btn-<?php echo $category_id == $cat['id'] ? 'dark' : 'outline-dark'; ?> m-1 rounded-pill">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center">
                <p class="text-muted">Tidak ada produk di kategori ini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col mb-5">
                <div class="card h-100">
                    <!-- Product image-->
                    <?php if ($product['image']): ?>
                        <img class="card-img-top" src="/project-web-s5/web-rental-outdor/public/assets/uploads/products/<?php echo $product['image']; ?>" alt="..." style="height: 200px; object-fit: cover;" />
                    <?php else: ?>
                        <img class="card-img-top" src="https://dummyimage.com/450x300/dee2e6/6c757d.jpg" alt="..." />
                    <?php endif; ?>
                    <!-- Product details-->
                    <div class="card-body p-4">
                        <div class="text-center">
                            <!-- Product name-->
                            <h5 class="fw-bolder"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <!-- Product category-->
                            <div class="small text-muted mb-2"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <!-- Product price-->
                            Rp <?php echo number_format($product['price_per_day'], 0, ',', '.'); ?> / hari
                            <br>
                            <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                        </div>
                    </div>
                    <!-- Product actions-->
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                        <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="product_detail.php?id=<?php echo $product['id']; ?>">Detail</a></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../../includes/shop_footer.php';
?>
