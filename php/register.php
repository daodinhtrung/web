<?php
require_once __DIR__ . '/config.php';

// Đặt múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đảm bảo phản hồi là JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không hợp lệ.');
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($email) || empty($password)) {
        throw new Exception('Vui lòng điền đầy đủ thông tin.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email không hợp lệ.');
    }

    if (strlen($password) < 6) {
        throw new Exception('Mật khẩu phải có ít nhất 6 ký tự.');
    }

    // Kiểm tra username đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception('Tên đăng nhập đã tồn tại.');
    }

    // Kiểm tra email đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception('Email đã được sử dụng.');
    }

    // Mã hóa mật khẩu và thêm người dùng mới
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt->execute([$username, $email, $hashed_password])) {
        $response['success'] = true;
        $response['message'] = 'Đăng ký thành công! Bạn sẽ được chuyển hướng đến trang đăng nhập.';
    } else {
        throw new Exception('Lỗi khi đăng ký. Vui lòng thử lại.');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (Throwable $e) {
    $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>