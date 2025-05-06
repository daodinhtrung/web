<?php
require_once __DIR__ . '/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra dữ liệu
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ.";
    } elseif (strlen($password) < 8) {
        $message = "Mật khẩu phải có ít nhất 8 ký tự.";
    } elseif ($password !== $confirm_password) {
        $message = "Mật khẩu và xác nhận mật khẩu không khớp.";
    } else {
        // Kiểm tra username và email đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $message = "Tên đăng nhập hoặc email đã được sử dụng.";
        } else {
            // Mã hóa mật khẩu và lưu người dùng mới
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            $message = "Đăng ký thành công! Vui lòng đăng nhập.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
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
                    <button type="submit"><i class="fa fa-search"></i></button>
                </div>
                <div class="user-account">
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Tài Khoản <i class="fa fa-user"></i></a>
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
                    <a href="php/cart.php"><i class="fa fa-shopping-cart"></i> Giỏ Hàng</a>
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
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-left">
                <img src="../images/logo.png" alt="Logo" class="logo">
                <p>Chào mừng bạn đến với Cửa Hàng!</p>
            </div>
            <div class="login-right">
                <h2>Đăng Ký</h2>
                <div id="notification" class="<?php echo $message ? (strpos($message, 'thành công') !== false ? 'success' : 'error') : ''; ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>;">
                    <span><?php echo $message; ?></span>
                    <button class="close-btn">×</button>
                </div>
                <form id="register-form" method="POST">
                    <div>
                        <input type="text" id="username" name="username" placeholder="Tên đăng nhập" required>
                    </div>
                    <div>
                        <input type="email" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div>
                        <input type="password" id="password" name="password" placeholder="Mật khẩu" required>
                    </div>
                    <div>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                    </div>
                    <button type="submit">Đăng Ký</button>
                </form>
                <div class="register-link">
                    <p>Đã có tài khoản? <a href="#" id="show-login-from-register">Đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/script.js"></script>
</body>
</html>