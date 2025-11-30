<?php
require_once '../../config/db.php';
require_once '../../includes/auth.php';

require_role('admin');

header('Content-Type: application/json');

$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type == 'pemasukan') {
    // Monthly Income (Paid orders)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(total_amount) as total 
        FROM orders 
        WHERE payment_status = 'paid' 
        GROUP BY month 
        ORDER BY month ASC 
        LIMIT 12
    ");
    echo json_encode($stmt->fetchAll());

} elseif ($type == 'transaksi') {
    // Monthly Transactions Count
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(order_date, '%Y-%m') as month, COUNT(*) as total 
        FROM orders 
        GROUP BY month 
        ORDER BY month ASC 
        LIMIT 12
    ");
    echo json_encode($stmt->fetchAll());

} elseif ($type == 'barang') {
    // Best Selling Products
    $stmt = $pdo->query("
        SELECT p.name, SUM(oi.qty) as total_qty 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        JOIN orders o ON oi.order_id = o.id
        WHERE o.payment_status = 'paid'
        GROUP BY p.id 
        ORDER BY total_qty DESC 
        LIMIT 5
    ");
    echo json_encode($stmt->fetchAll());

} else {
    echo json_encode([]);
}
?>
