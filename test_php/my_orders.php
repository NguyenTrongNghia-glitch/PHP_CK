<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";

requireLogin();

$userId = (int)$_SESSION["user"]["id"];

$orders = getOrdersByUser($userId);

include "includes/header.php";

?>

<div class="page-heading">

    <span class="eyebrow">ĐƠN HÀNG CỦA BẠN</span>

    <h1>Lịch sử mua hàng</h1>

</div>


<?php foreach ($orders as $order): ?>

<div class="cart-list order-card">

    <h3>Đơn hàng #<?= $order["id"] ?></h3>

    <p>
        Ngày đặt: <?= e($order["created_at"]) ?>
    </p>

    <p>
        Địa chỉ: <?= e($order["address"]) ?>
    </p>

    <p>
        Số điện thoại: <?= e($order["phone"]) ?>
    </p>

    <p>
        Trạng thái:

        <strong><?= e($order["status"]) ?></strong>
    </p>

    <p>
        Tổng tiền:

        <strong><?= money($order["total_price"]) ?></strong>
    </p>

    <h4>Sách đã mua</h4>

    <?php foreach (getOrderItems($order["id"]) as $item): ?>

        <p>
            <?= e($item["title"]) ?>
            - Số lượng: <?= (int)$item["quantity"] ?>
        </p>

    <?php endforeach; ?>

    <?php if ($order["status"] == "Chờ xác nhận"): ?>

        <a
            href="cancel_order.php?id=<?= $order["id"] ?>"
            class="remove"
            onclick="return confirm('Bạn có muốn hủy đơn hàng này không?')"
        >
            Hủy đơn hàng
        </a>

    <?php endif; ?>

</div>

<?php endforeach; ?>


<?php if (empty($orders)): ?>

    <div class="empty">

        Bạn chưa có đơn hàng nào.

        <br><br>

        <a href="index.php" class="btn">
            Mua sách ngay
        </a>

    </div>

<?php endif; ?>


<?php include "includes/footer.php"; ?>
