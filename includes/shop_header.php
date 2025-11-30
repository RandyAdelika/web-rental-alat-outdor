<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Rental Outdoor - Sewa Alat Camping</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="/project-web-s5/web-rental-outdor/public/assets/shop/css/styles.css" rel="stylesheet" />
    </head>
    <body>
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="/project-web-s5/web-rental-outdor/public/ecommerce/index.php">Rental Outdoor</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="/project-web-s5/web-rental-outdor/public/ecommerce/index.php">Katalog</a></li>
                        <?php if (is_logged_in()): ?>
                            <li class="nav-item"><a class="nav-link" href="/project-web-s5/web-rental-outdor/public/ecommerce/history.php">Riwayat</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="#!">Tentang Kami</a></li>
                    </ul>
                    <div class="d-flex">
                        <a class="btn btn-outline-dark me-2" href="/project-web-s5/web-rental-outdor/public/ecommerce/cart.php">
                            <i class="bi-cart-fill me-1"></i>
                            Cart
                            <span class="badge bg-dark text-white ms-1 rounded-pill">
                                <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                            </span>
                        </a>
                        <?php if (is_logged_in()): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-dark dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo current_user_name(); ?>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><a class="dropdown-item" href="/project-web-s5/web-rental-outdor/public/logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="/project-web-s5/web-rental-outdor/public/login.php" class="btn btn-outline-primary">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Sewa Alat Outdoor Lengkap</h1>
                    <p class="lead fw-normal text-white-50 mb-0">Tenda, Carrier, dan Perlengkapan Camping Lainnya</p>
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5">
