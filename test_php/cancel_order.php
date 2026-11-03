<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

/** @var PDO $pdo */
requireLogin();

$id = (int)($_GET["id"] ?? 0);

// Chỉ hủy được đơn của chính mình và khi đơn còn "Chờ xác nhận"
$stmt = $pdo->prepare(
    "UPDATE orders
     SET status = 'Đã hủy'
     WHERE id = ? AND user_id = ? AND status = 'Chờ xác nhận'"
);

$stmt->execute([$id, (int)$_SESSION["user"]["id"]]);

$message = $stmt->rowCount() > 0
    ? "Đã hủy đơn hàng #" . $id
    : "Không thể hủy đơn hàng này";

header("Location: my_orders.php?message=" . urlencode($message));

exit;
