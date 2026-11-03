<?php
// includes/db.php - kết nối MySQL bằng PDO
$host = '127.0.0.1';
$port = '3306';
$dbname = 'test_php';
$user = 'root';
$pass = ''; // XAMPP mặc định không có mật khẩu

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Không kết nối được CSDL: ' . $e->getMessage());
}
