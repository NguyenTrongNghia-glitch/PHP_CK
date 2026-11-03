
<?php

$currentUser = $_SESSION["user"] ?? null;

// Xác định đường dẫn khi đang ở trang quản trị
$rootPath = str_contains($_SERVER["PHP_SELF"], "/admin/")
    ? "../"
    : "";

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hiệu Sách Nhỏ</title>

    <link rel="stylesheet"
          href="<?= $rootPath ?>assets/css/style.css">

</head>

<body>

<header class="site-header">

    <div class="nav-wrap">

        <a href="<?= $rootPath ?>index.php" class="logo">

            <span class="logo-mark">h.</span>

            <span>
                Hiệu Sách Nhỏ
                <small>Một góc nhỏ của tri thức</small>
            </span>

        </a>

        <nav>

            <a href="<?= $rootPath ?>index.php">
                Trang chủ
            </a>

            <a href="<?= $rootPath ?>index.php#sach-moi">
                Sách
            </a>
         <?php if (!isAdmin()): ?>
            <a href="<?= $rootPath ?>cart.php">
                Giỏ hàng (<?= cartCount() ?>)
            </a>
         <?php endif; ?>
            <?php if ($currentUser): ?>

                <span class="hello">
                    Xin chào, <?= e($currentUser["fullname"]) ?>
                </span>

                <?php if ($currentUser["role"] == "admin"): ?>

                    <a href="<?= $rootPath ?>admin/index.php">
                        Quản trị sách
                    </a>

                    <a href="<?= $rootPath ?>admin/orders.php">
                        Đơn hàng
                    </a>

                    <a href="<?= $rootPath ?>admin/users.php">
                        Người dùng
                    </a>

                <?php else: ?>

                    <a href="<?= $rootPath ?>my_orders.php">
                        Đơn hàng của tôi
                    </a>

                <?php endif; ?>

                <a href="<?= $rootPath ?>profile.php">
                    Tài khoản
                </a>

                <a href="<?= $rootPath ?>logout.php">
                    Đăng xuất
                </a>

            <?php else: ?>

                <a href="<?= $rootPath ?>login.php">
                    Đăng nhập
                </a>

                <a href="<?= $rootPath ?>register.php"
                   class="nav-register">
                    Đăng ký
                </a>

            <?php endif; ?>

        </nav>

    </div>

</header>

<main class="container">

    <?php if (!empty($_GET["message"])): ?>

        <div class="notice">
            <?= e($_GET["message"]) ?>
        </div>

    <?php endif; ?>
