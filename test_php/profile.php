<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

/** @var PDO $pdo */
requireLogin();

$userId = (int)$_SESSION["user"]["id"];

// Lấy thông tin tài khoản hiện tại
$stmt = $pdo->prepare(
    "SELECT full_name, email, phone, address, password
     FROM users
     WHERE id = ?"
);
$stmt->execute([$userId]);

$user = $stmt->fetch();

// Tài khoản đã bị xóa thì đăng xuất
if (!$user) {

    unset($_SESSION["user"]);

    header("Location: login.php");

    exit;
}

$profileErrors = [];
$passwordErrors = [];
$success = "";

$fullname = $user["full_name"];
$phone = $user["phone"] ?? "";
$address = $user["address"] ?? "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST["action"] ?? "";


    // CẬP NHẬT THÔNG TIN
    if ($action == "profile") {

        $fullname = trim($_POST["fullname"] ?? "");

        $phone = trim($_POST["phone"] ?? "");

        $address = trim($_POST["address"] ?? "");

        if ($fullname == "") {

            $profileErrors[] = "Vui lòng nhập họ và tên.";

        } elseif (mb_strlen($fullname) > 100) {

            $profileErrors[] = "Họ và tên tối đa 100 ký tự.";
        }

        if ($phone != "" && !preg_match("/^[0-9]{9,11}$/", $phone)) {

            $profileErrors[] = "Số điện thoại phải gồm 9 đến 11 chữ số.";
        }

        if (mb_strlen($address) > 255) {

            $profileErrors[] = "Địa chỉ tối đa 255 ký tự.";
        }

        if (empty($profileErrors)) {

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET full_name = ?, phone = ?, address = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $fullname,
                $phone != "" ? $phone : null,
                $address != "" ? $address : null,
                $userId
            ]);

            // Cập nhật tên hiển thị trên thanh menu
            $_SESSION["user"]["fullname"] = $fullname;

            $success = "Đã cập nhật thông tin tài khoản.";
        }
    }


    // ĐỔI MẬT KHẨU
    elseif ($action == "password") {

        $current = $_POST["current_password"] ?? "";

        $new = $_POST["new_password"] ?? "";

        $confirm = $_POST["confirm_password"] ?? "";

        if (!password_verify($current, $user["password"])) {

            $passwordErrors[] = "Mật khẩu hiện tại không đúng.";
        }

        if (strlen($new) < 6) {

            $passwordErrors[] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        }

        if ($new !== $confirm) {

            $passwordErrors[] = "Mật khẩu nhập lại không khớp.";
        }

        if (empty($passwordErrors)) {

            $stmt = $pdo->prepare(
                "UPDATE users SET password = ? WHERE id = ?"
            );

            $stmt->execute([
                password_hash($new, PASSWORD_DEFAULT),
                $userId
            ]);

            $success = "Đã đổi mật khẩu thành công.";
        }
    }
}

include "includes/header.php";

?>

<div class="page-heading">

    <span class="eyebrow">TÀI KHOẢN CỦA BẠN</span>

    <h1>Thông tin cá nhân</h1>

</div>


<?php if ($success != ""): ?>

    <div class="notice">
        <?= e($success) ?>
    </div>

<?php endif; ?>


<!-- THÔNG TIN TÀI KHOẢN -->

<div class="auth-wrap">

    <form method="POST" class="auth-card">

        <input type="hidden" name="action" value="profile">

        <h1>Thông tin tài khoản</h1>


        <?php foreach ($profileErrors as $message): ?>

            <div class="error">
                <?= e($message) ?>
            </div>

        <?php endforeach; ?>


        <label>Email (không thể thay đổi)</label>

        <input
            type="email"
            value="<?= e($user["email"]) ?>"
            disabled
        >


        <label>Họ và tên</label>

        <input
            type="text"
            name="fullname"
            maxlength="100"
            value="<?= e($fullname) ?>"
            required
        >


        <label>Số điện thoại</label>

        <input
            type="tel"
            name="phone"
            maxlength="11"
            value="<?= e($phone) ?>"
        >


        <label>Địa chỉ nhận hàng</label>

        <textarea
            name="address"
            rows="3"
            maxlength="255"
        ><?= e($address) ?></textarea>


        <button type="submit" class="btn full">
            Lưu thông tin
        </button>

    </form>

</div>


<!-- ĐỔI MẬT KHẨU -->

<div class="auth-wrap">

    <form method="POST" class="auth-card">

        <input type="hidden" name="action" value="password">

        <h1>Đổi mật khẩu</h1>


        <?php foreach ($passwordErrors as $message): ?>

            <div class="error">
                <?= e($message) ?>
            </div>

        <?php endforeach; ?>


        <label>Mật khẩu hiện tại</label>

        <input
            type="password"
            name="current_password"
            required
        >


        <label>Mật khẩu mới (ít nhất 6 ký tự)</label>

        <input
            type="password"
            name="new_password"
            minlength="6"
            required
        >


        <label>Nhập lại mật khẩu mới</label>

        <input
            type="password"
            name="confirm_password"
            minlength="6"
            required
        >


        <button type="submit" class="btn full">
            Đổi mật khẩu
        </button>

    </form>

</div>


<?php include "includes/footer.php"; ?>
