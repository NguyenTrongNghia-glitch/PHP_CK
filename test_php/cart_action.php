<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";
// Admin không có giỏ hàng
if (isAdmin()) {

    header(
        "Location: index.php?message="
        . urlencode("Tài khoản quản trị không dùng giỏ hàng")
    );

    exit;
}

$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);

$action = $_GET["action"] ?? $_POST["action"] ?? "";

$book = findBook($id);

// Kiểm tra sách có tồn tại không
if (!$book) {

    header("Location: index.php?message=Sách không tồn tại");
    exit;

}


// THÊM SÁCH
if ($action == "add") {

    if (!isset($_SESSION["cart"][$id])) {

        $_SESSION["cart"][$id] = 1;

    } else {

        $_SESSION["cart"][$id]++;

    }

}


// CẬP NHẬT SỐ LƯỢNG
elseif ($action == "update") {

    $quantity = (int)($_POST["quantity"] ?? 1);

    if ($quantity < 1) {
        $quantity = 1;
    }

    if ($quantity > 99) {
        $quantity = 99;
    }

    $_SESSION["cart"][$id] = $quantity;

}


// XÓA SÁCH
elseif ($action == "remove") {

    unset($_SESSION["cart"][$id]);

}

// LƯU GIỎ HÀNG VÀO MYSQL (chỉ khi đã đăng nhập)
saveCart();

// QUAY LẠI GIỎ HÀNG
header("Location: cart.php");
exit;

?>
