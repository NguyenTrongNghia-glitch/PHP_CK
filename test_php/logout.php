<?php

session_start();

// Xóa thông tin đăng nhập
unset($_SESSION["user"]);
// Xóa giỏ hàng trong session (giỏ đã được lưu trong MySQL)
$_SESSION["cart"] = [];

// Quay về trang chủ
header("Location: index.php?message=Bạn đã đăng xuất");

exit;

?>
