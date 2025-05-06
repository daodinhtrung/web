<?php
require_once __DIR__ . '/php/config.php';

// Đặt múi giờ để đồng bộ với cơ sở dữ liệu
date_default_timezone_set('Asia/Ho_Chi_Minh');

$message = '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';

if (empty($token) || empty($user_id)) {
    $message = 'Token hoặc user_id không hợp lệ.';
} else {
    try {
        // Kiểm tra token trong bảng change_requests
        $stmt = $pdo->prepare("SELECT user_id FROM change_requests WHERE type = 'password' AND token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset || $reset['user_id'] != $user_id) {
            $message = 'Token không hợp lệ hoặc đã hết hạn.';
        } else {
            // Lấy thông tin user từ bảng users
            $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = 'Người dùng không tồn tại.';
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $password = $_POST['password'];
                    $confirm_password = $_POST['confirm_password'];

                    // Kiểm tra mật khẩu
                    if (empty($password) || empty($confirm_password)) {
                        $message = 'Vui lòng điền đầy đủ thông tin.';
                    } elseif ($password !== $confirm_password) {
                        $message = 'Mật khẩu không khớp.';
                    } elseif (strlen($password) < 6) {
                        $message = 'Mật khẩu phải có ít nhất 6 ký tự.';
                    } else {
                        // Mã hóa mật khẩu và cập nhật vào bảng users
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
                        if ($stmt->execute([$hashed_password, $user_id])) {
                            // Xóa token trong bảng change_requests sau khi đặt lại mật khẩu thành công
                            $stmt = $pdo->prepare("DELETE FROM change_requests WHERE token = ? AND type = 'password'");
                            $stmt->execute([$token]);

                            $message = 'Đặt lại mật khẩu thành công! Bạn có thể <a href="../login.html">đăng nhập</a> ngay bây giờ.';
                        } else {
                            $message = 'Lỗi khi đặt lại mật khẩu. Vui lòng thử lại.';
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .login-page {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f2f5;
        }
        .login-container {
            display: flex;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 800px;
        }
        .login-left {
            padding: 40px;
            background-color: #fff;
            border-right: 1px solid #ddd;
            text-align: center;
        }
        .login-right {
            padding: 40px;
            width: 100%;
        }
        .logo {
            width: 100px;
            margin-bottom: 20px;
        }
        .login-right h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .login-right input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .login-right button {
            width: 100%;
            padding: 10px;
            background-color: #1877f2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-right button:hover {
            background-color: #165bdb;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #e7f3ff;
            color: #1a73e8;
        }
        .message.error {
            background-color: #ffe6e6;
            color: #d93025;
        }
    </style>
</head>
<body>
    <main class="login-page">
        <div class="login-container">
            <div class="login-left">
                <img src="../images/logo.png" alt="Logo" class="logo">
                <p>Nền tảng thương mại điện tử<br>yêu thích ở Đông Nam Á & Đài Loan</p>
            </div>
            <div class="login-right">
                <h2>Đặt Lại Mật Khẩu</h2>
                <?php if ($message): ?>
                    <div class="message <?php echo strpos($message, 'thành công') !== false ? 'success' : 'error'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <?php if (!$message || strpos($message, 'thành công') === false): ?>
                    <form method="POST">
                        <div>
                            <input type="password" name="password" placeholder="Mật khẩu mới" required>
                        </div>
                        <div>
                            <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                        </div>
                        <button type="submit">Đặt Lại Mật Khẩu</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>