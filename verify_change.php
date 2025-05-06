<?php
require_once __DIR__ . '/php/config.php';

$message = '';

if (isset($_GET['token']) && isset($_GET['user_id']) && isset($_GET['type'])) {
    $token = $_GET['token'];
    $user_id = $_GET['user_id'];
    $type = $_GET['type'];

    // Kiểm tra token trong bảng change_requests
    $stmt = $pdo->prepare("SELECT * FROM change_requests WHERE user_id = ? AND token = ? AND type = ?");
    $stmt->execute([$user_id, $token, $type]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        // Kiểm tra thời gian hết hạn
        $current_time = date('Y-m-d H:i:s');
        if ($current_time <= $request['expires_at']) {
            // Cập nhật thông tin người dùng
            $new_value = $request['new_value'];
            $stmt = $pdo->prepare("UPDATE users SET $type = ? WHERE id = ?");
            $stmt->execute([$new_value, $user_id]);

            // Xóa yêu cầu sau khi xử lý
            $stmt = $pdo->prepare("DELETE FROM change_requests WHERE user_id = ? AND token = ?");
            $stmt->execute([$user_id, $token]);

            $message = "Thay đổi $type thành công! Bạn có thể đăng nhập với $type mới.";
        } else {
            $message = "Link xác nhận đã hết hạn. Vui lòng gửi yêu cầu mới.";
        }
    } else {
        $message = "Link xác nhận không hợp lệ.";
    }
} else {
    $message = "Yêu cầu không hợp lệ.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Thay Đổi</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <!-- Thanh đầu tiên: Logo, Hotline, Tìm kiếm, Tài khoản, Giỏ hàng -->
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
        <!-- Thanh điều hướng chính -->
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
        <section class="verify-change">
            <div class="container">
                <h2>Xác Nhận Thay Đổi</h2>
                <div id="notification" class="<?php echo strpos($message, 'thành công') !== false ? 'success' : 'error'; ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>;">
                    <span><?php echo $message; ?></span>
                    <button class="close-btn">×</button>
                </div>
                <p>Quay lại <a href="php/settings.php">Cài Đặt</a>.</p>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>© 2025 Cửa Hàng. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>