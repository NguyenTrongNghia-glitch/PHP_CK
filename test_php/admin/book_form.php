<?php

session_start();

require_once "../includes/data.php";
require_once "../includes/functions.php";

/** @var PDO $pdo */
/** @var array $categories */
requireAdmin();

$id = (int)($_GET["id"] ?? 0);

$error = "";

// Nếu có ID thì lấy thông tin sách để sửa
$book = $id ? findBook($id) : null;

if ($id && !$book) {
    header("Location: index.php?message=Không tìm thấy sách");
    exit;
}

// Nếu là thêm sách mới
if (!$book) {

    $book = [
        "title" => "",
        "author" => "",
        "category" => "Văn học",
        "price" => "",
        "image" => "",
        "description" => "",
        "featured" => false
    ];
}


// Khi nhấn nút lưu
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"] ?? "");

    $author = trim($_POST["author"] ?? "");

    $category = $_POST["category"] ?? "";

    $price = (int)($_POST["price"] ?? 0);

    $image = trim($_POST["image"] ?? "");

    $description = trim($_POST["description"] ?? "");

    $featured = isset($_POST["featured"]);


    // Kiểm tra dữ liệu
    if (
        $title == "" ||
        $author == "" ||
        $price <= 0 ||
        $price > 999999999 ||
        mb_strlen($title) > 200 ||
        mb_strlen($author) > 100 ||
        mb_strlen($image) > 500 ||
        $image == "" ||
        !in_array($category, $categories, true)
    ) {

        $error = "Vui lòng nhập đầy đủ thông tin hợp lệ.";

    } else {

        $categoryId = categoryIdByName($category);

        if ($id == 0) {

            // Thêm sách mới
            $stmt = $pdo->prepare(
                "INSERT INTO books
                    (category_id, title, author, price, image, description, featured)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $categoryId, $title, $author, $price,
                $image, $description, $featured ? 1 : 0
            ]);

        } else {

            // Cập nhật sách đã có
            $stmt = $pdo->prepare(
                "UPDATE books
                 SET category_id = ?, title = ?, author = ?, price = ?,
                     image = ?, description = ?, featured = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $categoryId, $title, $author, $price,
                $image, $description, $featured ? 1 : 0, $id
            ]);
        }


        header("Location: index.php?message=" . urlencode("Lưu sách thành công"));

        exit;

    }
}

include "../includes/header.php";

?>

<div class="auth-wrap">

    <form method="POST" class="auth-card">

        <span class="eyebrow">QUẢN LÝ SẢN PHẨM</span>

        <h1>
            <?= $id ? "Chỉnh sửa sách" : "Thêm sách mới" ?>
        </h1>


        <?php if ($error != ""): ?>

            <div class="error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <label>Tên sách</label>

        <input
            type="text"
            name="title"
            required
            value="<?= e($_POST["title"] ?? $book["title"]) ?>"
        >


        <label>Tác giả</label>

        <input
            type="text"
            name="author"
            required
            value="<?= e($_POST["author"] ?? $book["author"]) ?>"
        >


        <label>Thể loại</label>

        <select name="category">

            <?php foreach ($categories as $cat): ?>

                <option
                    value="<?= e($cat) ?>"
                    <?= $book["category"] == $cat ? "selected" : "" ?>
                >
                    <?= e($cat) ?>
                </option>

            <?php endforeach; ?>

        </select>


        <label>Giá sách (VNĐ)</label>

        <input
            type="number"
            name="price"
            min="1000"
            required
            value="<?= e($_POST["price"] ?? $book["price"]) ?>"
        >


        <label>Đường dẫn ảnh bìa</label>

        <input
            type="text"
            name="image"
            required
            value="<?= e($_POST["image"] ?? $book["image"]) ?>"
        >


        <label>Mô tả sách</label>

        <textarea
            name="description"
            rows="4"
        ><?= e($_POST["description"] ?? $book["description"]) ?></textarea>


        <label class="check-label">

            <input
                type="checkbox"
                name="featured"
                <?= $book["featured"] ? "checked" : "" ?>
            >

            Hiển thị sách nổi bật

        </label>


        <button type="submit" class="btn full">
            Lưu sách
        </button>

        <a href="index.php" class="continue">
            Quay lại quản trị
        </a>

    </form>

</div>


<?php include "../includes/footer.php"; ?>
