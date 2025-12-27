# web-rental-alat-outdor
Website Admin & E-Commerce build with PHP Native & Bootstrap.

## 🚀 Instalasi & Cara Menjalankan

### 1. Copy project ke `htdocs`
1. Pastikan sudah menginstall XAMPP.
2. Copy folder project ini ke direktori `htdocs`:
   - Windows: `C:\xampp\htdocs\project-web-s5/web-rental-outdor`
   - macOS: `/Applications/XAMPP/htdocs/project-web-s5/web-rental-outdor` (atau sesuai lokasi XAMPP kamu)

> Nama folder bebas, tapi sesuaikan juga dengan konfigurasi path di dalam project jika kamu mengubahnya.

---

### 2. Jalankan XAMPP
1. Buka **XAMPP Control Panel**.
2. Start service:
   - **Apache** (untuk web server)
   - **MySQL** (untuk database)

Jika keduanya sudah status **Running**, berarti server lokal siap dipakai.

---

### 3. Import database `schema.sql` ke phpMyAdmin
1. Buka browser dan akses: `http://localhost/phpmyadmin`
2. Buat database baru, misalnya dengan nama: `db_rental_outdoor`
3. Pilih database tersebut, lalu:
   - Buka tab **Import**
   - Klik **Choose File** / **Browse**
   - Pilih file `database/schema.sql` dari project ini
   - Klik **Go**

Jika berhasil, tabel-tabel seperti `users`, `products`, `categories`, `orders`, dll akan otomatis terbuat.

---

### 4. Jalankan website
Setelah database siap dan Apache/MySQL sudah berjalan:

- Untuk halaman **E-Commerce (toko)** biasanya:
  ```text
  http://localhost/project-web-s5/web-rental-outdor/public/ecommerce/index.php
