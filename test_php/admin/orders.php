<?php

session_start();

require_once "../includes/data.php";
require_once "../includes/functions.php";

requireAdmin();

$orders = getAllOrders();

include "../includes/header.php";

?>

<div class="page-heading">

    <span class="eyebrow">QUẢN LÝ BÁN HÀNG</span>

    <h1>Danh sách đơn hàng</h1>

</div>


<div class="admin-table-wrap">

    <table class="admin-table">

        <thead>

            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Chi tiết</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($orders as $order): ?>

                <tr>

                    <td>#<?= $order["id"] ?></td>

                    <td><?= e($order["fullname"]) ?></td>

                    <td><?= e($order["created_at"]) ?></td>

                    <td><?= money($order["total_price"]) ?></td>

                    <td><?= e($order["status"]) ?></td>

                    <td>
                        <a href="order_detail.php?id=<?= $order["id"] ?>">
                            Xem
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>


<?php if (empty($orders)): ?>

    <div class="empty">
        Chưa có đơn hàng nào.
    </div>

<?php endif; ?>


<?php include "../includes/footer.php"; ?>
