<?php
session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

// Lấy từ khóa tìm kiếm
$keyword = trim($_GET["q"] ?? "");

// Lấy thể loại được chọn
$category = $_GET["category"] ?? "";

// Lọc sách trong MySQL
$filteredBooks = searchBooks($keyword, $category);

// Lấy sách nổi bật
$featuredBooks = getFeaturedBooks();

include "includes/header.php";
?>

<!-- BANNER GIỚI THIỆU -->

<section class="hero">

    <div>

        <span class="eyebrow">
            GÓC NHỎ DÀNH CHO NGƯỜI YÊU SÁCH
        </span>

        <h1>
            Mỗi cuốn sách<br>
            là một hành trình.
        </h1>

        <p>
            Chọn cho mình một cuốn sách hay
            và dành chút thời gian cho những điều ý nghĩa.
        </p>

        <a href="#sach-moi" class="btn">
            Khám phá sách
        </a>

    </div>

    <div class="hero-note">

        <span>Đọc một chút mỗi ngày</span>

        <strong>♡</strong>

        <small>Nuôi dưỡng những ý tưởng đẹp</small>

    </div>

</section>


<!-- SÁCH NỔI BẬT -->

<section class="section">

    <div class="section-heading">

        <div>

            <span class="eyebrow">
                ĐƯỢC YÊU THÍCH
            </span>

            <h2>Sách nổi bật</h2>

        </div>

    </div>

    <div class="book-grid">

        <?php foreach ($featuredBooks as $book): ?>

            <?php include "includes/book_card.php"; ?>

        <?php endforeach; ?>

    </div>

</section>


<!-- DANH SÁCH SÁCH -->

<section class="section" id="sach-moi">

    <div class="section-heading">

        <div>

            <span class="eyebrow">
                TỦ SÁCH CỦA CHÚNG MÌNH
            </span>

            <h2>Khám phá sách</h2>

        </div>

        <span class="muted">
            <?= count($filteredBooks) ?> cuốn sách
        </span>

    </div>


    <!-- TÌM KIẾM VÀ LỌC -->

    <form method="GET" class="filter-bar">

        <input
            type="text"
            name="q"
            placeholder="Tìm tên sách hoặc tác giả..."
            value="<?= e($keyword) ?>"
        >

        <select name="category">

            <option value="">Tất cả thể loại</option>

            <?php foreach ($categories as $cat): ?>

                <option
                    value="<?= e($cat) ?>"
                    <?= $category == $cat ? "selected" : "" ?>
                >
                    <?= e($cat) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <button type="submit" class="btn btn-small">
            Tìm kiếm
        </button>

    </form>


    <!-- HIỂN THỊ SÁCH -->

    <?php if (count($filteredBooks) > 0): ?>

        <div class="book-grid">

            <?php foreach ($filteredBooks as $book): ?>

                <?php include "includes/book_card.php"; ?>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            Không tìm thấy sách phù hợp.

        </div>

    <?php endif; ?>

</section>


<?php include "includes/footer.php"; ?>
