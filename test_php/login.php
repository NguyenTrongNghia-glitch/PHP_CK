<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

/** @var PDO $pdo */
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = strtolower(trim($_POST["email"] ?? ""));

    $password = $_POST["password"] ?? "";

    // Tìm tài khoản trong MySQL
    $stmt = $pdo->prepare(
        "SELECT id, full_name, email, password, role
         FROM users
         WHERE email = ?"
    );
    $stmt->execute([$email]);

    $foundUser = $stmt->fetch();

    // Kiểm tra mật khẩu
    if (
        $foundUser &&
        password_verify($password, $foundUser["password"])
    ) {

                // Giỏ hàng tạm của khách (đang đăng nhập tài khoản khác thì bỏ qua)
        $guestCart = isset($_SESSION["user"]) ? [] : $_SESSION["cart"];

        session_regenerate_id(true);

        // Chỉ lưu thông tin cần thiết (không lưu mật khẩu) vào session
        $_SESSION["user"] = [
            "id"       => (int)$foundUser["id"],
            "fullname" => $foundUser["full_name"],
            "email"    => $foundUser["email"],
            "role"     => $foundUser["role"]
        ];
                // Giỏ hàng: gộp giỏ tạm với giỏ đã lưu trong MySQL (admin không có giỏ)
        if ($foundUser["role"] == "admin") {

            $_SESSION["cart"] = [];

        } else {

            $_SESSION["cart"] = mergeCarts(
                loadCart($foundUser["id"]),
                $guestCart
            );

            saveCart();
        }

        // Phân quyền
        if ($foundUser["role"] == "admin") {

            header("Location: admin/index.php");

        } else {

            header("Location: index.php");
        }

        exit;

    } else {

        $error = "Email hoặc mật khẩu không chính xác.";
    }
}

include "includes/header.php";

?>

<div class="auth-wrap">

    <form method="POST" class="auth-card">

        <span class="eyebrow">
            CHÀO MỪNG BẠN TRỞ LẠI
        </span>

        <h1>Đăng nhập</h1>

        <p class="muted">
            Đăng nhập để mua những cuốn sách yêu thích.
        </p>


        <?php if ($error != ""): ?>

            <div class="error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <label>Email</label>

        <input
            type="email"
            name="email"
            value="<?= e($_POST["email"] ?? "") ?>"
            required
        >


        <label>Mật khẩu</label>

        <input
            type="password"
            name="password"
            required
        >


        <button type="submit" class="btn full">
            Đăng nhập
        </button>


        <div class="demo-login">

            <strong>Tài khoản quản trị mẫu</strong>

            <p>Email: admin@gmail.com</p>

            <p>Mật khẩu: 123456</p>

        </div>


        <p class="auth-switch">

            Chưa có tài khoản?

            <a href="register.php">Đăng ký</a>

        </p>

    </form>

</div>


<?php include "includes/footer.php"; ?>
