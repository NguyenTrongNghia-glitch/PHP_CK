<?php

session_start();

require_once "../includes/data.php";
require_once "../includes/functions.php";

/** @var PDO $pdo */
requireAdmin();

$id = (int)($_GET["id"] ?? 0);

// Xóa sách (đơn hàng cũ vẫn giữ nguyên tên sách đã mua)
$stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
$stmt->execute([$id]);

// Xóa sách khỏi giỏ hàng
unset($_SESSION["cart"][$id]);

header(
    "Location: index.php?message="
    . urlencode("Đã xóa sách thành công")
);

exit;
