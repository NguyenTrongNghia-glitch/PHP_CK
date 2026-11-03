<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

include "includes/header.php";

?>

<div class="page-heading">

    <span class="eyebrow">GIỎ HÀNG CỦA BẠN</span>

    <h1>Những cuốn sách đã chọn</h1>

</div>


<?php if (empty($_SESSION["cart"])): ?>

    <div class="empty">

        <p>Giỏ hàng của bạn đang trống.</p>

        <a href="index.php" class="btn">
            Tiếp tục mua sách
        </a>

    </div>

<?php else: ?>

    <div class="cart-layout">

        <div class="cart-list">

            <?php foreach ($_SESSION["cart"] as $id => $qty): ?>

                <?php

                $book = findBook($id);

                if (!$book) {
                    continue;
                }

                ?>

                <div class="cart-item">

                    <img
                        src="<?= e($book["image"]) ?>"
                        alt=""
                    >

                    <div class="cart-book">

                        <strong>
                            <?= e($book["title"]) ?>
                        </strong>

                        <small>
                            <?= e($book["author"]) ?>
                        </small>

                        <span>
                            <?= money($book["price"]) ?>
                        </span>

                    </div>


                    <!-- CẬP NHẬT SỐ LƯỢNG -->

                    <form
                        method="POST"
                        action="cart_action.php"
                        class="quantity-form"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="update"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $id ?>"
                        >

                        <input
                            type="number"
                            name="quantity"
                            min="1"
                            max="99"
                            value="<?= $qty ?>"
                        >

                        <button type="submit" class="text-button">
                            Cập nhật
                        </button>

                    </form>


                    <!-- XÓA SÁCH -->

                    <a
                        href="cart_action.php?action=remove&id=<?= $id ?>"
                        class="remove"
                    >
                        Xóa
                    </a>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- TỔNG TIỀN -->

        <aside class="summary">

            <h3>Tổng đơn hàng</h3>

            <div class="summary-line">

                <span>Số lượng</span>

                <strong>
                    <?= cartCount() ?> sản phẩm
                </strong>

            </div>

            <div class="summary-line total">

                <span>Tổng tiền</span>

                <strong>
                    <?= money(cartTotal()) ?>
                </strong>

            </div>

            <a href="checkout.php" class="btn full">
                Tiến hành đặt hàng
            </a>

            <a href="index.php" class="continue">
                ← Tiếp tục mua sách
            </a>

        </aside>

    </div>

<?php endif; ?>


<?php include "includes/footer.php"; ?>
