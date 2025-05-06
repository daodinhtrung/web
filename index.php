<?php
session_start();
require_once 'php/config.php';

// Lấy sản phẩm
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();

$username = '';
$avatar = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $username = $user['username'];
    $avatar = $user['avatar'] ? $user['avatar'] : 'images/default-avatar.jpg';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<header>
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <img src="images/logo.png" alt="Logo" class="logo">
                <div class="hotline">
                    <span>HOTLINE: 0977508430 | 0338000308</span>
                </div>
                <div class="search-bar">
                    <input type="text" placeholder="Tìm sản phẩm...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <div class="user-account">
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Tài Khoản <i class="fas fa-user"></i></a>
                        <div class="dropdown-content">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="php/profile.php">Thông tin tài khoản</a>
                                <a href="php/settings.php">Cài đặt</a>
                                <a href="php/logout.php">Đăng xuất</a>
                            <?php else: ?>
                                <a href="login.html">Đăng nhập</a>
                                <a href="register.html">Đăng ký</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="cart">
                    <a href="php/cart.php"><i class="fas fa-shopping-cart"></i> Giỏ Hàng</a>
                </div>
            </div>
        </div>
    </div>
    <nav class="main-nav">
        <div class="container">
            <a href="index.php">TRANG CHỦ</a>
            <div class="dropdown">
                <a href="#" class="dropbtn">VỢT CẦU LÔNG</a>
                <div class="dropdown-content">
                    <a href="index.php#yonex">Vợt cầu lông Yonex</a>
                    <a href="index.php#victor">Vợt cầu lông Victor</a>
                    <a href="index.php#lining">Vợt cầu lông Lining</a>
                    <a href="index.php#apacs">Vợt cầu lông Apacs</a>
                    <a href="index.php#kawasaki">Vợt cầu lông Kawasaki</a>
                </div>
            </div>
            <a href="index.php#products">SẢN PHẨM</a>
        </div>
    </nav>
</header>

<main>
    <section id="home" class="banner-slider">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="images/banner1.jpg" alt="Banner 1"></div>
                <div class="swiper-slide"><img src="images/banner2.jpg" alt="Banner 2"></div>
                <div class="swiper-slide"><img src="images/banner3.jpg" alt="Banner 3"></div>                
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </section>

    <section class="download-module">
        <div class="container">
            <div class="module-content" style="background-image: url('images/download-bg.jpg');">
                <div class="module-qr">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="products" class="product-list">
        <div class="container">
            <h2>Sản Phẩm Mới</h2>
            <div class="product-grid">
                <?php foreach ($products as $product) { ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3><?php echo $product['name']; ?></h3>
                        <p><?php echo number_format($product['price']); ?>đ</p>
                        <button class="order-btn" data-id="<?php echo $product['id']; ?>">Thêm vào giỏ</button>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="container">
        <p>© 2025 Shopping. All rights reserved.</p>
    </div>
</footer>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>