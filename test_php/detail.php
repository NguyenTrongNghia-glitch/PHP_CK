<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

// Lấy ID sách từ URL
$id = $_GET["id"] ?? 0;

// Tìm sách
$book = findBook($id);

include "includes/header.php";

?>

<?php if (!$book): ?>

    <div class="empty">

        Không tìm thấy sách.

        <a href="index.php">Quay lại trang chủ</a>

    </div>

<?php else: ?>

    <div class="breadcrumb">

        <a href="index.php">Trang chủ</a>
        / Chi tiết sách

    </div>

    <section class="detail-layout">

        <div class="detail-image">

            <img
                src="<?= e($book["image"]) ?>"
                alt="<?= e($book["title"]) ?>"
            >

        </div>


        <div class="detail-content">

            <span class="eyebrow">
                <?= e($book["category"]) ?>
            </span>

            <h1>
                <?= e($book["title"]) ?>
            </h1>

            <p>
                Tác giả:
                <?= e($book["author"]) ?>
            </p>

            <strong class="detail-price">

                <?= money($book["price"]) ?>

            </strong>

            <h3>Giới thiệu sách</h3>

            <p>
                <?= e($book["description"]) ?>
            </p>
          <?php if (!isAdmin()): ?>

            <a
                href="cart_action.php?action=add&id=<?= $book["id"] ?>"
                class="btn"
            >
                Thêm vào giỏ hàng
            </a>
         <?php endif; ?>
        </div>

    </section>

<?php endif; ?>


<?php include "includes/footer.php"; ?>
