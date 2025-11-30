-- Database: db_rental_outdoor

CREATE DATABASE IF NOT EXISTS db_rental_outdoor;
USE db_rental_outdoor;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Plain text as requested for now, ideally hashed
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Table: products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_day DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Table: orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    rental_start DATE NOT NULL,
    rental_end DATE NOT NULL,
    total_days INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('transfer_atm', 'qris', 'minimarket') NOT NULL,
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    status ENUM('new', 'on_process', 'completed', 'cancelled') DEFAULT 'new',
    shipping_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: order_items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    qty INT NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    days INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Table: stock_log
CREATE TABLE IF NOT EXISTS stock_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT, -- Nullable if system generated
    type ENUM('in', 'out', 'adjustment') NOT NULL,
    qty INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Initial Data

-- Users
INSERT INTO users (name, email, password, role) VALUES
('Admin Rental', 'admin@rental.com', 'admin123', 'admin'),
('Customer User', 'user@rental.com', 'user123', 'customer');

-- Categories
INSERT INTO categories (name, description) VALUES
('Tenda', 'Berbagai jenis tenda camping'),
('Carrier', 'Tas carrier berbagai ukuran'),
('Aksesoris', 'Perlengkapan pendukung camping');

-- Products
-- Assuming IDs: 1=Tenda, 2=Carrier, 3=Aksesoris
INSERT INTO products (category_id, name, description, price_per_day, stock, status, image) VALUES
-- Tenda
(1, 'Tenda kap2 (Compas)', 'Tenda kapasitas 2 orang merk Compas', 35000, 5, 'active', 'tenda_kap2_compas.jpg'),
(1, 'Tenda kap4-5 (Tendaki)', 'Tenda kapasitas 4-5 orang merk Tendaki', 38000, 5, 'active', 'tenda_kap4_5_tendaki.jpg'),
(1, 'Tenda kap4-5 (Big Adventure)', 'Tenda kapasitas 4-5 orang merk Big Adventure', 40000, 5, 'active', 'tenda_kap4_5_big_adventure.jpg'),

-- Carrier
(2, 'Consina Gen 3 60+5L', 'Carrier Consina Gen 3 kapasitas 60+5 Liter', 33000, 5, 'active', 'consina_gen3_60_5l.jpg'),
(2, 'Deuter 65+10L', 'Carrier Deuter kapasitas 65+10 Liter', 28000, 5, 'active', 'deuter_65_10l.jpg'),
(2, 'Eiger Strimline 45L Red', 'Carrier Eiger Strimline 45L warna Merah', 28000, 5, 'active', 'eiger_strimline_45l_red.jpg'),
(2, 'Eiger Strimline 45L Black', 'Carrier Eiger Strimline 45L warna Hitam', 28000, 5, 'active', 'eiger_strimline_45l_black.jpg'),
(2, 'Eiger Rhinos 45L', 'Carrier Eiger Rhinos kapasitas 45 Liter', 28000, 5, 'active', 'eiger_rhinos_45l.jpg'),

-- Aksesoris
(3, 'SleepingBag', 'Sleeping Bag hangat dan nyaman', 10000, 10, 'active', 'sleepingbag.jpg'),
(3, 'Matras Karet', 'Matras bahan karet anti slip', 5000, 10, 'active', 'matras_karet.jpg'),
(3, 'Matras Aluminium', 'Matras lapisan aluminium foil', 8000, 10, 'active', 'matras_aluminium.jpg'),
(3, 'Lampu Tenda', 'Lampu penerangan untuk tenda', 5000, 10, 'active', 'lampu_tenda.jpg'),
(3, 'Hammok', 'Hammock kuat untuk bersantai', 12000, 5, 'active', 'hammok.jpg'),
(3, 'Flysheet 3x4', 'Flysheet ukuran 3x4 meter', 15000, 5, 'active', 'flysheet_3x4.jpg'),
(3, 'Tiang Flysheet + Tali', 'Set tiang flysheet dan tali pengikat', 12000, 5, 'active', 'tiang_flysheet_tali.jpg'),
(3, 'Bantal Tiup', 'Bantal angin portable', 3000, 10, 'active', 'bantal_tiup.jpg'),
(3, 'Egg Box (isi 4pcs)', 'Kotak pelindung telur isi 4', 5000, 5, 'active', 'egg_box_isi_4pcs.png'),
(3, 'Dirigen Lipat 10L', 'Jerigen air lipat kapasitas 10 Liter', 3000, 5, 'active', 'dirigen_lipat_10l.png'),
(3, 'Kursi Lipat', 'Kursi lipat portable', 10000, 5, 'active', 'kursi_lipat.png'),
(3, 'Meja Lipat', 'Meja lipat portable', 10000, 5, 'active', 'meja_lipat.png'),
(3, 'Tripod', 'Tripod untuk memasak (cooking tripod)', 10000, 5, 'active', 'tripod.png');
