<?php
// Kết nối MySQL và nạp dữ liệu dùng chung cho mọi trang.
// Phải gọi session_start() trước khi require file này.

require_once __DIR__ . "/db.php";

// Danh sách thể loại lấy từ bảng categories
$categories = $pdo
    ->query("SELECT name FROM categories ORDER BY id")
    ->fetchAll(PDO::FETCH_COLUMN);

// Giỏ hàng vẫn lưu tạm trong session: [id sách => số lượng]
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// Bỏ khỏi giỏ những cuốn sách đã bị xóa khỏi cửa hàng
if (!empty($_SESSION["cart"])) {

    $ids = array_map("intval", array_keys($_SESSION["cart"]));

    $marks = implode(",", array_fill(0, count($ids), "?"));

    $stmt = $pdo->prepare("SELECT id FROM books WHERE id IN ($marks)");
    $stmt->execute($ids);

    $existing = array_map("intval", $stmt->fetchAll(PDO::FETCH_COLUMN));

    foreach ($ids as $id) {
        if (!in_array($id, $existing, true)) {
            unset($_SESSION["cart"][$id]);
        }
    }
}
