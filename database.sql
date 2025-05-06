CREATE DATABASE ecommerce;
USE ecommerce;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    gender ENUM('Nam', 'Nữ', 'Khác'),
    birth_date DATE,
    address TEXT,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Thêm người dùng mẫu (mật khẩu: password123)
INSERT INTO users (username, password, full_name, gender, birth_date, address, avatar) VALUES 
('user1', '$2y$10$6X8z3j5k9Qz7Y0x2m4n5vO7w8x9y0z1a2b3c4d5e6f7g8h9i0j', 'Nguyễn Văn A', 'Nam', '1990-01-01', '123 Đường Láng, Hà Nội', 'avatars/1.jpg'),
('user2', '$2y$10$6X8z3j5k9Qz7Y0x2m4n5vO7w8x9y0z1a2b3c4d5e6f7g8h9i0j', 'Trần Thị B', 'Nữ', '1995-02-02', '456 Lê Lợi, TP.HCM', 'avatars/2.jpg');