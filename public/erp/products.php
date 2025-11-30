<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_role('admin');

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    
    // Handle Image Upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../public/assets/uploads/products/';
        // Create dir if not exists (just in case)
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('prod_') . '.' . $file_ext;
            $dest_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest_path)) {
                $image_path = 'assets/uploads/products/' . $new_filename;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price_per_day, stock, description, image, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$name, $category_id, $price, $stock, $description, $image_path]);
    
    // Log stock
    $product_id = $pdo->lastInsertId();
    $stmt_log = $pdo->prepare("INSERT INTO stock_log (product_id, user_id, type, qty, description) VALUES (?, ?, 'in', ?, 'Initial Stock')");
    $stmt_log->execute([$product_id, $_SESSION['user_id'], $stock]);

    header('Location: products.php');
    exit;
}

// Handle Update Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Get old data
    $stmt_old = $pdo->prepare("SELECT stock, image FROM products WHERE id = ?");
    $stmt_old->execute([$id]);
    $old_data = $stmt_old->fetch();
    $old_stock = $old_data['stock'];
    $image_path = $old_data['image'];

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../public/assets/uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('prod_') . '.' . $file_ext;
            $dest_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest_path)) {
                // Delete old image if exists
                if ($image_path && file_exists('../../public/' . $image_path)) {
                    unlink('../../public/' . $image_path);
                }
                $image_path = 'assets/uploads/products/' . $new_filename;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, price_per_day = ?, stock = ?, description = ?, image = ?, status = ? WHERE id = ?");
    $stmt->execute([$name, $category_id, $price, $stock, $description, $image_path, $status, $id]);

    if ($stock != $old_stock) {
        $diff = $stock - $old_stock;
        $type = $diff > 0 ? 'adjustment' : 'adjustment'; 
        $stmt_log = $pdo->prepare("INSERT INTO stock_log (product_id, user_id, type, qty, description) VALUES (?, ?, ?, ?, 'Manual Update')");
        $stmt_log->execute([$id, $_SESSION['user_id'], $type, abs($diff)]);
    }

    header('Location: products.php');
    exit;
}

// Handle Delete Product
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get image to delete file
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        if ($product['image'] && file_exists('../../public/' . $product['image'])) {
            unlink('../../public/' . $product['image']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    header('Location: products.php');
    exit;
}

// Fetch Products
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
$products = $stmt->fetchAll();

// Fetch Categories for dropdown
$stmt_cat = $pdo->query("SELECT * FROM categories");
$categories = $stmt_cat->fetchAll();

require_once '../../includes/admin_header.php';
?>

<h1 class="mt-4">Kelola Produk</h1>
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Daftar Produk
        <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addProductModal">Tambah Produk</button>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga/Hari</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($products as $p): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td>
                        <?php if ($p['image']): ?>
                            <img src="/project-web-s5/web-rental-outdor/public/<?php echo $p['image']; ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                            <span class="text-muted">No Img</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td>Rp <?php echo number_format($p['price_per_day'], 0, ',', '.'); ?></td>
                    <td><?php echo $p['stock']; ?></td>
                    <td>
                        <?php if ($p['status'] == 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm btn-edit" 
                            data-id="<?php echo $p['id']; ?>"
                            data-name="<?php echo htmlspecialchars($p['name']); ?>"
                            data-category="<?php echo $p['category_id']; ?>"
                            data-price="<?php echo $p['price_per_day']; ?>"
                            data-stock="<?php echo $p['stock']; ?>"
                            data-description="<?php echo htmlspecialchars($p['description']); ?>"
                            data-status="<?php echo $p['status']; ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#editProductModal">
                            Edit
                        </button>
                        <a href="products.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Tambah Produk Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label class="form-label">Nama Produk</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select class="form-select" name="category_id" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Harga Sewa / Hari</label>
                <input type="number" class="form-control" name="price" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Stok Awal</label>
                <input type="number" class="form-control" name="stock" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar Produk</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Edit Produk</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3">
                <label class="form-label">Nama Produk</label>
                <input type="text" class="form-control" name="name" id="edit_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select class="form-select" name="category_id" id="edit_category" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Harga Sewa / Hari</label>
                <input type="number" class="form-control" name="price" id="edit_price" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Stok</label>
                <input type="number" class="form-control" name="stock" id="edit_stock" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" id="edit_status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ganti Gambar (Opsional)</label>
                <input type="file" class="form-control" name="image" accept="image/*">
                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_category').value = this.dataset.category;
            document.getElementById('edit_price').value = this.dataset.price;
            document.getElementById('edit_stock').value = this.dataset.stock;
            document.getElementById('edit_description').value = this.dataset.description;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });
});
</script>

<?php
require_once '../../includes/admin_footer.php';
?>
