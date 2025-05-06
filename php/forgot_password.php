<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($username) {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("INSERT INTO change_requests (user_id, type, new_value, token, created_at, expires_at) VALUES (?, 'password', '', ?, NOW(), ?)");
            $stmt->execute([$user['id'], $token, $expires_at]);

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
                $mail->addAddress($user['email']);

                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);

                // Mã hóa tiêu đề
                $subject = 'Xác nhận đặt lại mật khẩu';
                $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

                $link = "http://localhost/reset_password.php?token=$token&user_id={$user['id']}";
                $mail->Body = "Xin chào,<br><br>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình.<br>"
                    . "Vui lòng click vào đường link sau để đặt lại mật khẩu:<br>"
                    . "<a href='$link'>Đặt lại mật khẩu</a><br><br>"
                    . "Link này sẽ hết hạn sau 1 giờ.<br><br>Trân trọng,<br>Cửa Hàng";

                $mail->send();
                $response['success'] = true;
                $response['message'] = 'Link đặt lại mật khẩu đã được gửi tới email của bạn.';
            } catch (Exception $e) {
                $response['message'] = "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
            }
        } else {
            $response['message'] = 'Tên đăng nhập không tồn tại.';
        }
    } elseif ($email) {
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("INSERT INTO change_requests (user_id, type, new_value, token, created_at, expires_at) VALUES (?, 'password', '', ?, NOW(), ?)");
            $stmt->execute([$user['id'], $token, $expires_at]);

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

                // Mã hóa tiêu đề
                $subject = 'Xác nhận đặt lại mật khẩu';
                $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

                $link = "http://localhost/reset_password.php?token=$token&user_id={$user['id']}";
                $mail->Body = "Xin chào,<br><br>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình.<br>"
                    . "Vui lòng click vào đường link sau để đặt lại mật khẩu:<br>"
                    . "<a href='$link'>Đặt lại mật khẩu</a><br><br>"
                    . "Link này sẽ hết hạn sau 1 giờ.<br><br>Trân trọng,<br>Cửa Hàng";

                $mail->send();
                $response['success'] = true;
                $response['message'] = 'Link đặt lại mật khẩu đã được gửi tới email của bạn.';
            } catch (Exception $e) {
                $response['message'] = "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
            }
        } else {
            $response['message'] = 'Email không tồn tại.';
        }
    } else {
        $response['message'] = 'Vui lòng nhập tên đăng nhập hoặc email.';
    }
}

echo json_encode($response);
?>