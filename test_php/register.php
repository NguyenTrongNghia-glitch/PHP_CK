<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

/** @var PDO $pdo */
// Đã đăng nhập thì không cần đăng ký nữa
if (isset($_SESSION["user"])) {

    header("Location: index.php");

    exit;
}

$errors = [];

$fullname = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["fullname"] ?? "");

    $email = strtolower(trim($_POST["email"] ?? ""));

    $phone = trim($_POST["phone"] ?? "");

    $password = $_POST["password"] ?? "";

    $confirm = $_POST["confirm"] ?? "";


    // Kiểm tra dữ liệu
    if ($fullname == "") {

        $errors[] = "Vui lòng nhập họ và tên.";

    } elseif (mb_strlen($fullname) > 100) {

        $errors[] = "Họ và tên tối đa 100 ký tự.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Email không hợp lệ.";

    } elseif (mb_strlen($email) > 100) {

        $errors[] = "Email tối đa 100 ký tự.";
    }

    if ($phone != "" && !preg_match("/^[0-9]{9,11}$/", $phone)) {

        $errors[] = "Số điện thoại phải gồm 9 đến 11 chữ số.";
    }

    if (strlen($password) < 6) {

        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }

    if ($password !== $confirm) {

        $errors[] = "Mật khẩu nhập lại không khớp.";
    }


    // Kiểm tra email đã được đăng ký chưa
    if (empty($errors)) {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $errors[] = "Email này đã được đăng ký.";
        }
    }


    // Lưu tài khoản vào MySQL
    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, email, password, phone)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $fullname,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $phone != "" ? $phone : null
            ]);

            header(
                "Location: login.php?message="
                . urlencode("Đăng ký thành công, hãy đăng nhập.")
            );

            exit;

        } catch (PDOException $ex) {

            // 23000 = trùng email (hai người đăng ký cùng lúc)
            if ($ex->getCode() == "23000") {

                $errors[] = "Email này đã được đăng ký.";

            } else {

                $errors[] = "Không thể tạo tài khoản, vui lòng thử lại.";
            }
        }
    }
}

include "includes/header.php";

?>

<div class="auth-wrap">

    <form method="POST" class="auth-card">

        <span class="eyebrow">
            THAM GIA CÙNG CHÚNG MÌNH
        </span>

        <h1>Đăng ký</h1>

        <p class="muted">
            Tạo tài khoản để mua sách và theo dõi đơn hàng.
        </p>


        <?php foreach ($errors as $message): ?>

            <div class="error">
                <?= e($message) ?>
            </div>

        <?php endforeach; ?>


        <label>Họ và tên</label>

        <input
            type="text"
            name="fullname"
            maxlength="100"
            value="<?= e($fullname) ?>"
            required
        >


        <label>Email</label>

        <input
            type="email"
            name="email"
            maxlength="100"
            value="<?= e($email) ?>"
            required
        >


        <label>Số điện thoại (không bắt buộc)</label>

        <input
            type="tel"
            name="phone"
            maxlength="11"
            value="<?= e($phone) ?>"
        >


        <label>Mật khẩu (ít nhất 6 ký tự)</label>

        <input
            type="password"
            name="password"
            minlength="6"
            required
        >


        <label>Nhập lại mật khẩu</label>

        <input
            type="password"
            name="confirm"
            minlength="6"
            required
        >


        <button type="submit" class="btn full">
            Tạo tài khoản
        </button>


        <p class="auth-switch">

            Đã có tài khoản?

            <a href="login.php">Đăng nhập</a>

        </p>

    </form>

</div>


<?php include "includes/footer.php"; ?>
