<?php

session_start();

require_once "../includes/data.php";
require_once "../includes/functions.php";

requireAdmin();

$books = getAllBooks();

include "../includes/header.php";

?>

<div class="page-heading admin-heading">

    <div>

        <span class="eyebrow">KHU VỰC QUẢN TRỊ</span>

        <h1>Quản lý sách</h1>

        <p class="muted">
            Quản lý danh sách sản phẩm của cửa hàng.
        </p>

    </div>

    <a href="book_form.php" class="btn">
        + Thêm sách mới
    </a>

</div>


<div class="admin-table-wrap">

    <table class="admin-table">

        <thead>

            <tr>
                <th>Sách</th>
                <th>Thể loại</th>
                <th>Giá</th>
                <th>Thao tác</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($books as $book): ?>

                <tr>

                    <td>

                        <div class="table-book">

                            <img
                                src="<?= e($book["image"]) ?>"
                                alt=""
                            >

                            <span>

                                <strong>
                                    <?= e($book["title"]) ?>
                                </strong>

                                <small>
                                    <?= e($book["author"]) ?>
                                </small>

                            </span>

                        </div>

                    </td>

                    <td>
                        <?= e($book["category"]) ?>
                    </td>

                    <td>
                        <?= money($book["price"]) ?>
                    </td>

                    <td>

                        <a href="book_form.php?id=<?= $book["id"] ?>">
                            Sửa
                        </a>

                        |

                        <a
                            href="delete_book.php?id=<?= $book["id"] ?>"
                            class="remove"
                            onclick="return confirm('Bạn có chắc muốn xóa sách này?')"
                        >
                            Xóa
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>


<?php include "../includes/footer.php"; ?>
