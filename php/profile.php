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
    $stmt = $pdo->prepare("SELECT username, email, avatar, full_name, gender, birth_date, address, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = 'Người dùng không tồn tại.';
    } else {
        $avatar = $user['avatar'] ? "../{$user['avatar']}" : '../images/default-avatar.jpg';
    }
} catch (Exception $e) {
    $message = 'Lỗi: ' . $e->getMessage();
}

// Xử lý cập nhật thông tin hồ sơ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if (!empty($birth_date) && !DateTime::createFromFormat('Y-m-d', $birth_date)) {
        $message = "Ngày sinh không hợp lệ.";
    } elseif (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
        $message = "Số điện thoại phải có 10-11 chữ số.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, gender = ?, birth_date = ?, address = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $gender, $birth_date ?: null, $address, $phone, $user_id]);
        $message = "Cập nhật thông tin thành công!";

        // Cập nhật lại thông tin người dùng
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Xử lý cập nhật ảnh đại diện (upload file)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['error'] === UPLOAD_ERR_OK) {
        if (!in_array($file['type'], $allowed_types)) {
            $message = 'Định dạng file không hợp lệ. Chỉ chấp nhận JPEG, PNG, GIF.';
        } elseif ($file['size'] > $max_size) {
            $message = 'File quá lớn. Kích thước tối đa là 5MB.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = __DIR__ . '/../uploads/' . $new_filename;

            if (!is_dir(__DIR__ . '/../uploads/')) {
                mkdir(__DIR__ . '/../uploads/', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $relative_path = "uploads/$new_filename";
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$relative_path, $user_id]);
                $avatar = "../$relative_path";
                $message = 'Cập nhật ảnh đại diện thành công!';
            } else {
                $message = 'Lỗi khi tải file lên server.';
            }
        }
    } else {
        $message = 'Lỗi khi tải file: ' . $file['error'];
    }
}

// Xử lý cập nhật ảnh đại diện (cropper)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cropped_image'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    $croppedImage = $_POST['cropped_image'];
    if (empty($croppedImage)) {
        $response['message'] = "Không nhận được dữ liệu hình ảnh.";
        echo json_encode($response);
        exit;
    }

    $avatarDir = '../images/avatars/';
    if (!is_dir($avatarDir)) {
        if (!mkdir($avatarDir, 0755, true)) {
            $response['message'] = "Không thể tạo thư mục lưu ảnh.";
            echo json_encode($response);
            exit;
        }
    }

    if (!is_writable($avatarDir)) {
        $response['message'] = "Thư mục images/avatars/ không có quyền ghi.";
        echo json_encode($response);
        exit;
    }

    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $croppedImage));
    if ($imageData === false) {
        $response['message'] = "Dữ liệu hình ảnh không hợp lệ.";
        echo json_encode($response);
        exit;
    }

    $filename = "$user_id.jpg";
    $destination = "$avatarDir$filename";

    if (file_put_contents($destination, $imageData)) {
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        if ($stmt->execute(["images/avatars/$filename", $user_id])) {
            $response['success'] = true;
            $response['message'] = "Cập nhật avatar thành công!";
        } else {
            $response['message'] = "Lỗi khi cập nhật avatar vào cơ sở dữ liệu.";
        }
    } else {
        $response['message'] = "Lỗi khi lưu avatar vào thư mục.";
    }

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="../css/styles.css">
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
                    <button type="submit"><i class="fa fa-search"></i></button>
                </div>
                <div class="user-account">
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Tài Khoản <i class="fa fa-user"></i></a>
                        <div class="dropdown-content">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="profile.php">Thông tin tài khoản</a>
                                <a href="settings.php">Cài đặt</a>
                                <a href="logout.php">Đăng xuất</a>
                            <?php else: ?>
                                <a href="login.html">Đăng nhập</a>
                                <a href="register.html">Đăng ký</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="cart">
                    <a href="cart.php"><i class="fa fa-shopping-cart"></i> Giỏ Hàng</a>
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
                    <a href="index.php#yonex">Vợt cầu lông Yonex</a>
                    <a href="index.php#victor">Vợt cầu lông Victor</a>
                    <a href="index.php#lining">Vợt cầu lông Lining</a>
                    <a href="index.php#apacs">Vợt cầu lông Apacs</a>
                    <a href="index.php#kawasaki">Vợt cầu lông Kawasaki</a>
                </div>
            </div>
            <a href="../index.php#products">SẢN PHẨM</a>
        </div>
    </nav>
</header>
    <main>
        <section class="profile-form">
            <div class="container">
                <h2>Thông Tin Cá Nhân</h2>
                <div id="notification" class="<?php echo $message ? (strpos($message, 'thành công') !== false ? 'success' : 'error') : ''; ?>" style="display: <?php echo $message ? 'block' : 'none'; ?>;">
                    <span><?php echo $message; ?></span>
                    <button class="close-btn">×</button>
                </div>
                <div class="profile-sidebar">
                    <div class="avatar-section">
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-preview">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    </div>
                    <nav class="sidebar-nav">
                        <a href="#" class="active">Hồ Sơ Của Tôi</a>
                        <a href="#">Ngân Hàng</a>
                        <a href="#">Địa Chỉ</a>
                        <a href="settings.php">Đổi Mật Khẩu</a>
                        <a href="#">Cài Đặt Thông Báo</a>
                        <a href="../index.php#products">Đơn Mua</a>
                    </nav>
                </div>

                <div class="profile-content">
                    <h3>Hồ Sơ Của Tôi</h3>
                    <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                    <form method="POST" class="profile-details">
                        <div class="form-group">
                            <label for="username">Tên đăng nhập:</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="full_name">Tên:</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại:</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Giới tính:</label>
                            <div class="gender-options">
                                <label><input type="radio" name="gender" value="Nam" <?php echo ($user['gender'] == 'Nam') ? 'checked' : ''; ?>> Nam</label>
                                <label><input type="radio" name="gender" value="Nữ" <?php echo ($user['gender'] == 'Nữ') ? 'checked' : ''; ?>> Nữ</label>
                                <label><input type="radio" name="gender" value="Khác" <?php echo ($user['gender'] == 'Khác') ? 'checked' : ''; ?>> Khác</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="birth_date">Ngày sinh:</label>
                            <input type="date" id="birth_date" name="birth_date" value="<?php echo $user['birth_date'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Địa chỉ:</label>
                            <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile">Lưu</button>
                    </form>

                    <div class="avatar-upload">
                        <h4>Ảnh đại diện:</h4>
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-preview">
                        <form id="avatarForm" enctype="multipart/form-data" method="POST">
                            <input type="file" id="avatarInput" name="avatar" accept="image/*">
                        </form>
                        <p>Dung lượng file tối đa 5 MB<br>Định dạng: .JPEG, .PNG, .GIF</p>
                    </div>
                </div>

                <!-- Cropper Modal -->
                <div id="cropper-modal">
                    <div class="cropper-container">
                        <img id="image-to-crop" src="" alt="Image to Crop">
                        <div class="cropper-actions">
                            <button id="crop-btn">Cắt và Lưu</button>
                            <button id="cancel-btn">Hủy</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>© 2025 Cửa Hàng. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>