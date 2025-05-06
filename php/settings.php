<?php
session_start();
require_once __DIR__ . '/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Lấy thông tin người dùng
try {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = 'Người dùng không tồn tại.';
    }
} catch (Exception $e) {
    $message = 'Lỗi: ' . $e->getMessage();
}

// Cấu hình PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendVerificationEmail($email, $token, $type, $new_value, $user_id) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'zedt01022004@gmail.com';
        $mail->Password = 'tgevcjfnjhywjelq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('zedt01022004@gmail.com', 'Cửa Hàng');
        $mail->addAddress($email);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        $subject = 'Xác nhận thay đổi ' . ($type == 'username' ? 'tên đăng nhập' : 'email');
        $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $change_type = $type == 'username' ? 'username' : 'email';
        $link = "http://localhost/verify_change.php?token=$token&user_id=$user_id&type=$change_type";
        $mail->Body = "Xin chào,<br><br>Bạn đã yêu cầu thay đổi $change_type thành <strong>$new_value</strong>.<br>"
            . "Vui lòng click vào đường link sau để xác nhận:<br>"
            . "<a href='$link'>Xác nhận thay đổi</a><br><br>"
            . "Link này sẽ hết hạn sau 1 giờ.<br><br>Trân trọng,<br>Cửa Hàng";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Không thể gửi email xác nhận. Lỗi: {$mail->ErrorInfo}";
    }
}

// Xử lý yêu cầu đổi username hoặc email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['change_username']) || isset($_POST['change_email']))) {
    $type = isset($_POST['change_username']) ? 'username' : 'email';
    $new_value = trim($_POST[$type]);
    $current_value = $user[$type];

    if (empty($new_value)) {
        $message = "Vui lòng nhập " . ($type == 'username' ? 'tên đăng nhập' : 'email') . " mới.";
    } elseif ($new_value === $current_value) {
        $message = "Giá trị mới phải khác với " . ($type == 'username' ? 'tên đăng nhập' : 'email') . " hiện tại.";
    } else {
        if ($type == 'email' && !filter_var($new_value, FILTER_VALIDATE_EMAIL)) {
            $message = "Email không hợp lệ.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE $type = ? AND id != ?");
            $stmt->execute([$new_value, $user_id]);
            if ($stmt->fetch()) {
                $message = ($type == 'username' ? 'Tên đăng nhập' : 'Email') . " đã được sử dụng.";
            } else {
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmt = $pdo->prepare("INSERT INTO change_requests (user_id, type, new_value, token, expires_at) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $type, $new_value, $token, $expires_at]);
                $email_to_send = ($type == 'username') ? $user['email'] : $new_value;
                $result = sendVerificationEmail($email_to_send, $token, $type, $new_value, $user_id);
                if ($result === true) {
                    $message = "Yêu cầu thay đổi đã được gửi. Vui lòng kiểm tra email để xác nhận.";
                } else {
                    $message = $result;
                }
            }
        }
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current_password, $user['password'])) {
        $message = "Mật khẩu hiện tại không đúng.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Mật khẩu mới và mật khẩu xác nhận không khớp.";
    } elseif (strlen($new_password) < 8) {
        $message = "Mật khẩu mới phải có ít nhất 8 ký tự.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        $message = "Đổi mật khẩu thành công!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài Đặt</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <!-- Thanh đầu tiên: Logo, Hotline, Tìm kiếm, Tài khoản, Giỏ hàng -->
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
        <!-- Thanh điều hướng chính -->
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
        <section class="settings-form">
            <div class="container">
                <h2>Cài Đặt</h2>
                <div id="notification" class="<?php echo $message ? (strpos($message, 'thành công') !== false ? 'success' : 'error') : ''; ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>;">
                    <span><?php echo $message; ?></span>
                    <button class="close-btn">×</button>
                </div>

                <h3>Đổi Tên Đăng Nhập</h3>
                <form method="POST">
                    <div>
                        <label for="username">Tên đăng nhập hiện tại: <?php echo htmlspecialchars($user['username']); ?></label>
                    </div>
                    <div>
                        <label for="username">Tên đăng nhập mới:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <button type="submit" name="change_username">Gửi Yêu Cầu</button>
                </form>

                <h3>Đổi Email</h3>
                <form method="POST">
                    <div>
                        <label for="email">Email hiện tại: <?php echo htmlspecialchars($user['email']); ?></label>
                    </div>
                    <div>
                        <label for="email">Email mới:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <button type="submit" name="change_email">Gửi Yêu Cầu</button>
                </form>

                <h3>Đổi Mật Khẩu</h3>
                <form method="POST">
                    <div>
                        <label for="current_password">Mật khẩu hiện tại:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div>
                        <label for="new_password">Mật khẩu mới:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div>
                        <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password">Đổi Mật Khẩu</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>© 2025 Cửa Hàng. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>