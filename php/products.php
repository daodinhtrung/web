<?php
require_once 'config.php';

// Kiểm tra và thêm sản phẩm vào bảng products nếu chưa tồn tại
$products = [
    ["id" => 1, "name" => "Áo Thun", "price" => 150000, "image" => "product1.jpg"],
    ["id" => 2, "name" => "Quần Jeans", "price" => 250000, "image" => "product2.jpg"],
    ["id" => 3, "name" => "Giày Sneaker", "price" => 350000, "image" => "product3.jpg"],
    ["id" => 4, "name" => "Túi Xách", "price" => 200000, "image" => "product4.jpg"],
    ["id" => 5, "name" => "Đồng Hồ", "price" => 300000, "image" => "product5.jpg"],
];

// Tạo bảng products nếu chưa tồn tại
$pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    image VARCHAR(255) NOT NULL
)");

foreach ($products as $product) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO products (id, name, price, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$product['id'], $product['name'], $product['price'], $product['image']]);
}

// Hiển thị sản phẩm
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();

foreach ($products as $product) {
    echo '<div class="product-card">';
    echo '<img src="images/' . $product['image'] . '" alt="' . $product['name'] . '">';
    echo '<h3>' . $product['name'] . '</h3>';
    echo '<p>' . number_format($product['price'], 0, ',', '.') . ' VNĐ</p>';
    echo '<button class="order-btn" data-id="' . $product['id'] . '">Đặt Hàng</button>';
    echo '</div>';
}
?>