<?php
session_start();
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}

// Lấy sản phẩm trong giỏ hàng (giả lập)
$cart_items = [];
// Thêm logic lấy dữ liệu giỏ hàng từ database nếu cần
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<header>
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <img src="../images/logo.png" alt="Logo" class="logo">
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
                                <a href="profile.php">Thông tin tài khoản</a>
                                <a href="settings.php">Cài đặt</a>
                                <a href="logout.php">Đăng xuất</a>
                            <?php else: ?>
                                <a href="../login.html">Đăng nhập</a>
                                <a href="../register.html">Đăng ký</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="cart">
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ Hàng</a>
                </div>
            </div>
        </div>
    </div>
    <nav class="main-nav">
        <div class="container">
            <a href="../index.php">TRANG CHỦ</a>
            <div class="dropdown">
                <a href="#" class="dropbtn">VỢT CẦU LÔNG</a>
                <div class="dropdown-content">
                    <a href="../index.php#yonex">Vợt cầu lông Yonex</a>
                    <a href="../index.php#victor">Vợt cầu lông Victor</a>
                    <a href="../index.php#lining">Vợt cầu lông Lining</a>
                    <a href="../index.php#apacs">Vợt cầu lông Apacs</a>
                    <a href="../index.php#kawasaki">Vợt cầu lông Kawasaki</a>
                </div>
            </div>
            <a href="../index.php#products">SẢN PHẨM</a>
        </div>
    </nav>
</header>

<main>
    <section class="cart-page">
        <div class="container">
            <h2>Giỏ Hàng</h2>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Sản Phẩm</th>
                        <th>Đơn Giá</th>
                        <th>Số Lượng</th>
                        <th>Thành Tiền</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cart_items)): ?>
                        <tr>
                            <td colspan="5">Giỏ hàng của bạn đang trống.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="cart-item-image">
                                    <?php echo $item['name']; ?>
                                </td>
                                <td><?php echo number_format($item['price']); ?>đ</td>
                                <td>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                    <button class="update-btn">Cập nhật</button>
                                </td>
                                <td><?php echo number_format($item['price'] * $item['quantity']); ?>đ</td>
                                <td>
                                    <button class="remove-btn">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <h3>Tổng cộng: <span>0đ</span></h3>
                <button class="checkout-btn">Thanh Toán</button>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="container">
        <p>© 2025 Shopping. All rights reserved.</p>
    </div>
</footer>

<script src="../js/script.js"></script>
</body>
</html>