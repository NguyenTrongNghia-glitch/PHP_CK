<?php

session_start();

require_once "../includes/data.php";
require_once "../includes/functions.php";

/** @var PDO $pdo */
requireAdmin();

$id = (int)($_GET["id"] ?? 0);

$selectedOrder = findOrder($id);


// Cập nhật trạng thái
if (
    $selectedOrder &&
    $_SERVER["REQUEST_METHOD"] == "POST"
) {

    $status = $_POST["status"] ?? "";

    if (in_array($status, ORDER_STATUSES, true)) {

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        header(
            "Location: orders.php?message="
            . urlencode("Đã cập nhật đơn hàng")
        );

        exit;
    }
}

include "../includes/header.php";

?>

<div class="page-heading">

    <span class="eyebrow">CHI TIẾT ĐƠN HÀNG</span>

    <h1>Đơn hàng #<?= $id ?></h1>

</div>


<?php if (!$selectedOrder): ?>

    <div class="empty">
        Không tìm thấy đơn hàng.
    </div>

<?php else: ?>

    <div class="cart-list">

        <h3>Thông tin khách hàng</h3>

        <p>
            Họ tên:
            <?= e($selectedOrder["fullname"]) ?>
        </p>

        <p>
            Số điện thoại:
            <?= e($selectedOrder["phone"]) ?>
        </p>

        <p>
            Địa chỉ:
            <?= e($selectedOrder["address"]) ?>
        </p>

        <p>
            Ngày đặt:
            <?= e($selectedOrder["created_at"]) ?>
        </p>

        <h3>Danh sách sản phẩm</h3>

        <?php foreach (getOrderItems($id) as $item): ?>

            <p>
                <?= e($item["title"]) ?>
                - <?= (int)$item["quantity"] ?> cuốn
                - <?= money($item["price"] * $item["quantity"]) ?>
            </p>

        <?php endforeach; ?>


        <h3>
            Tổng tiền:
            <?= money($selectedOrder["total_price"]) ?>
        </h3>


        <form method="POST">

            <label>Trạng thái đơn hàng</label>

            <select name="status">

                <?php foreach (ORDER_STATUSES as $status): ?>

                    <option
                        value="<?= e($status) ?>"
                        <?= $selectedOrder["status"] == $status ? "selected" : "" ?>
                    >
                        <?= e($status) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <button class="btn">
                Cập nhật trạng thái
            </button>

        </form>

    </div>

<?php endif; ?>


<?php include "../includes/footer.php"; ?>
