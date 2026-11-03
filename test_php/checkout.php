<?php

session_start();

require_once "includes/data.php";
require_once "includes/functions.php";


/** @var PDO $pdo */
// Kiểm tra đăng nhập
if (!isset($_SESSION["user"])) {

    header(
        "Location: login.php?message="
        . urlencode("Vui lòng đăng nhập để đặt hàng")
    );

    exit;
}
// Admin không đặt hàng
if (isAdmin()) {

    header("Location: index.php");

    exit;
}

// Kiểm tra giỏ hàng
if (empty($_SESSION["cart"])) {

    header("Location: cart.php");

    exit;
}

$userId = (int)$_SESSION["user"]["id"];

$error = "";

// Điền sẵn số điện thoại và địa chỉ đã lưu trong tài khoản
$stmt = $pdo->prepare("SELECT phone, address FROM users WHERE id = ?");
$stmt->execute([$userId]);

$saved = $stmt->fetch() ?: [];

$phone = $saved["phone"] ?? "";

$address = $saved["address"] ?? "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $phone = trim($_POST["phone"] ?? "");

    $address = trim($_POST["address"] ?? "");

    if ($phone == "" || $address == "") {

        $error = "Vui lòng nhập đầy đủ thông tin.";

    } elseif (mb_strlen($phone) > 20 || mb_strlen($address) > 255) {

        $error = "Số điện thoại tối đa 20 ký tự, địa chỉ tối đa 255 ký tự.";

    } else {

        // Lấy giá hiện tại của từng cuốn sách trong giỏ
        $items = [];

        $total = 0;

        foreach ($_SESSION["cart"] as $bookId => $quantity) {

            $book = findBook($bookId);

            if ($book) {

                $items[] = [$book, (int)$quantity];

                $total += $book["price"] * $quantity;
            }
        }

        if (empty($items)) {

            $error = "Giỏ hàng của bạn không còn sách hợp lệ.";

        } else {

            try {

                // Lưu đơn hàng và chi tiết đơn hàng cùng lúc
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "INSERT INTO orders
                        (user_id, receiver, phone, address, total, status)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $userId,
                    $_SESSION["user"]["fullname"],
                    $phone,
                    $address,
                    $total,
                    "Chờ xác nhận"
                ]);

                $orderId = (int)$pdo->lastInsertId();

                $stmt = $pdo->prepare(
                    "INSERT INTO order_items
                        (order_id, book_id, title, quantity, price)
                     VALUES (?, ?, ?, ?, ?)"
                );

                foreach ($items as [$book, $quantity]) {

                    $stmt->execute([
                        $orderId,
                        $book["id"],
                        $book["title"],
                        $quantity,
                        $book["price"]
                    ]);
                }

                $pdo->commit();

                // Xóa giỏ hàng sau khi đặt
                $_SESSION["cart"] = [];
                                saveCart();

                header(
                    "Location: my_orders.php?message="
                    . urlencode("Đặt hàng thành công")
                );

                exit;

            } catch (PDOException $ex) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = "Không thể lưu đơn hàng, vui lòng thử lại.";
            }
        }
    }
}

include "includes/header.php";

?>

<div class="auth-wrap">

    <form method="POST" class="auth-card">

        <span class="eyebrow">
            HOÀN TẤT ĐƠN HÀNG
        </span>

        <h1>Thông tin nhận sách</h1>

        <p class="muted">
            Tổng thanh toán:
            <strong><?= money(cartTotal()) ?></strong>
        </p>


        <?php if ($error != ""): ?>

            <div class="error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <label>Số điện thoại</label>

        <input
            type="tel"
            name="phone"
            maxlength="20"
            value="<?= e($phone) ?>"
            required
        >


        <label>Địa chỉ nhận hàng</label>

        <textarea
            name="address"
            rows="4"
            maxlength="255"
            required
        ><?= e($address) ?></textarea>


        <button type="submit" class="btn full">
            Xác nhận đặt hàng
        </button>

    </form>

</div>


<?php include "includes/footer.php"; ?>
