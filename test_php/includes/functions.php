<?php

// Các trạng thái của đơn hàng
const ORDER_STATUSES = [
    "Chờ xác nhận",
    "Đang giao",
    "Hoàn thành",
    "Đã hủy"
];

// Chống lỗi hiển thị ký tự HTML
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Định dạng tiền Việt Nam
function money($price) {
    return number_format((float)$price, 0, ",", ".") . "đ";
}

// Đường dẫn gốc (khi đang ở trong thư mục admin thì cần lùi lại 1 cấp)
function rootPath() {
    return str_contains($_SERVER["PHP_SELF"], "/admin/") ? "../" : "";
}


// ================= SÁCH =================

// Câu SELECT chung: lấy sách kèm tên thể loại
const BOOK_SELECT = "
    SELECT b.id, b.title, b.author, b.price, b.image,
           b.description, b.featured, c.name AS category
    FROM books b
    LEFT JOIN categories c ON c.id = b.category_id
";

// Tìm sách theo ID
function findBook($id) {
    global $pdo;

    $stmt = $pdo->prepare(BOOK_SELECT . " WHERE b.id = ?");
    $stmt->execute([(int)$id]);

    $book = $stmt->fetch();

    return $book ?: null;
}

// Lấy tất cả sách
function getAllBooks() {
    global $pdo;

    return $pdo->query(BOOK_SELECT . " ORDER BY b.id")->fetchAll();
}

// Tìm kiếm sách theo từ khóa (tên hoặc tác giả) và thể loại
function searchBooks($keyword = "", $category = "") {
    global $pdo;

    $sql = BOOK_SELECT . " WHERE 1 = 1";
    $params = [];

    if ($keyword !== "") {
        $like = "%" . addcslashes($keyword, "%_\\") . "%";
        $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
        $params[] = $like;
        $params[] = $like;
    }

    if ($category !== "") {
        $sql .= " AND c.name = ?";
        $params[] = $category;
    }

    $stmt = $pdo->prepare($sql . " ORDER BY b.id");
    $stmt->execute($params);

    return $stmt->fetchAll();
}

// Lấy sách nổi bật
function getFeaturedBooks() {
    global $pdo;

    return $pdo
        ->query(BOOK_SELECT . " WHERE b.featured = 1 ORDER BY b.id")
        ->fetchAll();
}

// Lấy ID thể loại theo tên (null nếu không có)
function categoryIdByName($name) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);

    $id = $stmt->fetchColumn();

    return $id === false ? null : (int)$id;
}


// ================= GIỎ HÀNG =================

// Đếm số lượng sách trong giỏ
function cartCount() {
    return array_sum($_SESSION["cart"]);
}

// Tính tổng tiền giỏ hàng
function cartTotal() {
    $total = 0;

    foreach ($_SESSION["cart"] as $id => $quantity) {
        $book = findBook($id);

        if ($book) {
            $total += $book["price"] * $quantity;
        }
    }

    return $total;
}


// Tài khoản đang đăng nhập có phải admin không
function isAdmin() {
    return ($_SESSION["user"]["role"] ?? "") === "admin";
}

// Đọc giỏ hàng của một tài khoản từ MySQL: [id sách => số lượng]
function loadCart($userId) {
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT book_id, quantity FROM cart_items WHERE user_id = ?"
    );
    $stmt->execute([(int)$userId]);

    $cart = [];

    foreach ($stmt->fetchAll() as $row) {
        $cart[(int)$row["book_id"]] = (int)$row["quantity"];
    }

    return $cart;
}

// Ghi giỏ hàng trong session vào MySQL (chỉ khi đã đăng nhập)
function saveCart() {
    global $pdo;

    if (!isset($_SESSION["user"])) {
        return;
    }

    $userId = (int)$_SESSION["user"]["id"];

    try {

        $pdo->beginTransaction();

        $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?")
            ->execute([$userId]);

        $stmt = $pdo->prepare(
            "INSERT INTO cart_items (user_id, book_id, quantity)
             VALUES (?, ?, ?)"
        );

        foreach ($_SESSION["cart"] as $bookId => $quantity) {
            $stmt->execute([$userId, (int)$bookId, (int)$quantity]);
        }

        $pdo->commit();

    } catch (PDOException $ex) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

// Gộp hai giỏ hàng (cộng số lượng, tối đa 99 mỗi cuốn)
function mergeCarts($first, $second) {
    foreach ($second as $bookId => $quantity) {
        $first[$bookId] = min(99, ($first[$bookId] ?? 0) + $quantity);
    }

    return $first;
}
// ================= ĐƠN HÀNG =================

// Câu SELECT chung: đặt tên cột giống dữ liệu cũ để giao diện không phải sửa
const ORDER_SELECT = "
    SELECT id, user_id, receiver AS fullname, phone, address,
           total AS total_price, status,
           DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') AS created_at
    FROM orders
";

// Tất cả đơn hàng (mới nhất trước) - dùng cho trang quản trị
function getAllOrders() {
    global $pdo;

    return $pdo->query(ORDER_SELECT . " ORDER BY id DESC")->fetchAll();
}

// Đơn hàng của một người dùng (mới nhất trước)
function getOrdersByUser($userId) {
    global $pdo;

    $stmt = $pdo->prepare(ORDER_SELECT . " WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([(int)$userId]);

    return $stmt->fetchAll();
}

// Một đơn hàng theo ID (null nếu không có)
function findOrder($id) {
    global $pdo;

    $stmt = $pdo->prepare(ORDER_SELECT . " WHERE id = ?");
    $stmt->execute([(int)$id]);

    $order = $stmt->fetch();

    return $order ?: null;
}

// Các cuốn sách trong một đơn hàng
function getOrderItems($orderId) {
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT book_id, title, quantity, price
         FROM order_items
         WHERE order_id = ?
         ORDER BY id"
    );
    $stmt->execute([(int)$orderId]);

    return $stmt->fetchAll();
}


// ================= PHÂN QUYỀN =================

// Kiểm tra đăng nhập
function requireLogin() {
    if (!isset($_SESSION["user"])) {
        header(
            "Location: " . rootPath() . "login.php?message="
            . urlencode("Vui lòng đăng nhập")
        );
        exit;
    }
}

// Kiểm tra quyền quản trị
function requireAdmin() {
    requireLogin();

    if (($_SESSION["user"]["role"] ?? "") !== "admin") {
        header(
            "Location: " . rootPath() . "index.php?message="
            . urlencode("Bạn không có quyền truy cập trang này")
        );
        exit;
    }
}
