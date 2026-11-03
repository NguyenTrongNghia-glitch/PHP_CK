<?php

session_start();

require_once "../includes/data.php";
require_once "../includes/functions.php";

requireAdmin();
/** @var PDO $pdo */
$currentId = (int)$_SESSION["user"]["id"];


// Đổi quyền người dùng (user <-> admin)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userId = (int)($_POST["user_id"] ?? 0);

    $role = $_POST["role"] ?? "";

    if ($userId == $currentId) {

        $message = "Bạn không thể tự đổi quyền của chính mình";

    } elseif (!in_array($role, ["user", "admin"], true)) {

        $message = "Quyền không hợp lệ";

    } else {

        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $userId]);

        $message = "Đã cập nhật quyền người dùng";
    }

    header("Location: users.php?message=" . urlencode($message));

    exit;
}


// Danh sách người dùng kèm số đơn hàng đã đặt
$users = $pdo->query(
    "SELECT u.id, u.full_name, u.email, u.phone, u.role,
            DATE_FORMAT(u.created_at, '%d/%m/%Y') AS created_at,
            COUNT(o.id) AS order_count
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     GROUP BY u.id
     ORDER BY u.id"
)->fetchAll();

include "../includes/header.php";

?>

<div class="page-heading">

    <span class="eyebrow">QUẢN LÝ NGƯỜI DÙNG</span>

    <h1>Danh sách người dùng</h1>

    <p class="muted">
        Tổng cộng <?= count($users) ?> tài khoản.
    </p>

</div>


<div class="admin-table-wrap">

    <table class="admin-table">

        <thead>

            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Ngày đăng ký</th>
                <th>Số đơn</th>
                <th>Quyền</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($users as $user): ?>

                <tr>

                    <td><?= $user["id"] ?></td>

                    <td><?= e($user["full_name"]) ?></td>

                    <td><?= e($user["email"]) ?></td>

                    <td><?= e($user["phone"] ?? "") ?></td>

                    <td><?= e($user["created_at"]) ?></td>

                    <td><?= (int)$user["order_count"] ?></td>

                    <td>

                        <?php if ($user["id"] == $currentId): ?>

                            <strong><?= e($user["role"]) ?></strong>
                            (bạn)

                        <?php else: ?>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="user_id"
                                    value="<?= $user["id"] ?>"
                                >

                                <select name="role">

                                    <option
                                        value="user"
                                        <?= $user["role"] == "user" ? "selected" : "" ?>
                                    >
                                        user
                                    </option>

                                    <option
                                        value="admin"
                                        <?= $user["role"] == "admin" ? "selected" : "" ?>
                                    >
                                        admin
                                    </option>

                                </select>

                                <button type="submit" class="text-button">
                                    Lưu
                                </button>

                            </form>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>


<?php include "../includes/footer.php"; ?>
