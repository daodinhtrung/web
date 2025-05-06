<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];

    // Kiểm tra sản phẩm tồn tại
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
        exit;
    }

    // Kiểm tra sản phẩm đã có trong giỏ hàng
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cart_item = $stmt->fetch();

    if ($cart_item) {
        // Cập nhật số lượng
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$cart_item['id']]);
    } else {
        // Thêm mới vào giỏ hàng
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Thêm vào giỏ hàng thành công!']);
}
?>