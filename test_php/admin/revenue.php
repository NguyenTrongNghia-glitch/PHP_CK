<?php

session_start();

require_once "../includes/data.php";
require_once "../includes/functions.php";

requireAdmin();

/* Chỉ tính đơn có trạng thái "Hoàn thành" */
const REVENUE_STATUS = "Hoàn thành";

/* =====================================================
   Đọc bộ lọc (4 chế độ tách riêng):
   day   = một ngày
   week  = một tuần
   month = một tháng
   range = từ ngày - đến ngày
   Bảng kết quả luôn hiện theo từng ngày (chỉ ngày có đơn).
   ===================================================== */
function parseDate(?string $value): ?DateTime
{
    $d = DateTime::createFromFormat("Y-m-d", (string) $value);
    return ($d && $d->format("Y-m-d") === $value) ? $d->setTime(0, 0, 0) : null;
}

$mode = $_GET["mode"] ?? "range";
if (!in_array($mode, ["day", "week", "month", "range"], true)) {
    $mode = "month";
}

$today = new DateTime("today");

$dayValue   = $_GET["day"]   ?? $today->format("Y-m-d");
$weekValue  = $_GET["week"]  ?? $today->format("o-\WW");
$monthValue = $_GET["month"] ?? $today->format("Y-m");
$fromValue  = $_GET["from"]  ?? (new DateTime("first day of this month"))->format("Y-m-d");
$toValue    = $_GET["to"]    ?? $today->format("Y-m-d");

// Xác định khoảng ngày [fromDate, toDate] theo chế độ
$fromDate = null;
$toDate   = null;

if ($mode === "day") {
    $fromDate = parseDate($dayValue);
    $toDate   = $fromDate ? clone $fromDate : null;
} elseif ($mode === "week") {
    if (preg_match('/^(\d{4})-W(\d{2})$/', $weekValue, $m)) {
        $fromDate = (new DateTime())->setISODate((int) $m[1], (int) $m[2])->setTime(0, 0, 0);
        $toDate   = (clone $fromDate)->modify("+6 days");
    }
} elseif ($mode === "month") {
    $first = parseDate($monthValue . "-01");
    if ($first) {
        $fromDate = $first;
        $toDate   = (clone $first)->modify("last day of this month");
    }
} else {
    $fromDate = parseDate($fromValue);
    $toDate   = parseDate($toValue);
    if ($fromDate && $toDate && $fromDate > $toDate) {
        [$fromDate, $toDate] = [$toDate, $fromDate];
    }
}

// Giá trị không hợp lệ -> quay về tháng hiện tại
if (!$fromDate || !$toDate) {
    $mode     = "month";
    $fromDate = new DateTime("first day of this month 00:00:00");
    $toDate   = (clone $fromDate)->modify("last day of this month");
    $monthValue = $fromDate->format("Y-m");
}

$fromStr = $fromDate->format("Y-m-d");
$toStr   = $toDate->format("Y-m-d");
$toExclusive = (clone $toDate)->modify("+1 day")->format("Y-m-d");

if ($mode === "day") {
    $label = "Ngày " . $fromDate->format("d/m/Y");
} elseif ($mode === "week") {
    $label = "Tuần " . $fromDate->format("W/o") . " (" . $fromDate->format("d/m") . " - " . $toDate->format("d/m/Y") . ")";
} elseif ($mode === "month") {
    $label = "Tháng " . $fromDate->format("m/Y");
} else {
    $label = "Từ " . $fromDate->format("d/m/Y") . " đến " . $toDate->format("d/m/Y");
}

/* =====================================================
   Truy vấn doanh thu theo từng ngày
   ===================================================== */
$stmt = $pdo->prepare(
    "SELECT DATE(created_at) AS d,
            COUNT(*) AS orders_count,
            COALESCE(SUM(total), 0) AS revenue
     FROM orders
     WHERE status = ?
       AND created_at >= ?
       AND created_at <  ?
     GROUP BY DATE(created_at)
     ORDER BY d"
);
$stmt->execute([REVENUE_STATUS, $fromStr . " 00:00:00", $toExclusive . " 00:00:00"]);

$rows = [];
$totalOrders  = 0;
$totalRevenue = 0;

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $rows[] = [
        "period"  => (new DateTime($r["d"]))->format("d/m/Y"),
        "orders"  => (int) $r["orders_count"],
        "revenue" => (float) $r["revenue"],
    ];
    $totalOrders  += (int) $r["orders_count"];
    $totalRevenue += (float) $r["revenue"];
}

/* =====================================================
   Xuất file Excel / Word (HTML đặt đuôi .xls / .doc)
   ===================================================== */
$export = $_GET["export"] ?? "";

if ($export === "excel" || $export === "word") {

    $isExcel  = $export === "excel";
    $filename = "doanh-thu-" . $fromDate->format("Ymd") . "-" . $toDate->format("Ymd")
              . ($isExcel ? ".xls" : ".doc");

    header("Content-Type: " . ($isExcel ? "application/vnd.ms-excel" : "application/msword") . "; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    header("Cache-Control: max-age=0");

    echo "\xEF\xBB\xBF"; // BOM UTF-8 để giữ tiếng Việt
    ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<style>
    table { border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 6px 10px; }
    th { background: #e8e8e8; }
    .num { text-align: right; }
    .total td { font-weight: bold; background: #f5f5f5; }
</style>
</head>
<body>
    <h2>BÁO CÁO DOANH THU - HIỆU SÁCH NHỎ</h2>
    <p>Kỳ báo cáo: <?= e($label) ?><br>
       Tính các đơn có trạng thái: <?= e(REVENUE_STATUS) ?></p>

    <table>
        <thead>
            <tr>
                <th>Ngày</th>
                <th>Số đơn</th>
                <th>Doanh thu (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row["period"]) ?></td>
                    <td class="num"><?= $row["orders"] ?></td>
                    <td class="num"><?= number_format($row["revenue"], 0, ",", ".") ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td>Tổng cộng</td>
                <td class="num"><?= $totalOrders ?></td>
                <td class="num"><?= number_format($totalRevenue, 0, ",", ".") ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
    <?php
    exit;
}

/* =====================================================
   Hiển thị trang
   ===================================================== */
$query = http_build_query([
    "mode"  => $mode,
    "day"   => $dayValue,
    "week"  => $weekValue,
    "month" => $monthValue,
    "from"  => $fromValue,
    "to"    => $toValue,
]);

include "../includes/header.php";

?>

<div class="page-heading">

    <span class="eyebrow">QUẢN LÝ BÁN HÀNG</span>

    <h1>Doanh thu</h1>

</div>


<form method="get" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap; margin-bottom:20px;">

    <div>
        <label>Lọc theo</label><br>
        <div style="display:flex; gap:14px; padding:8px 0;">
            <label><input type="radio" name="mode" value="range" <?= $mode === "range" ? "checked" : "" ?> onchange="toggleRevenueInputs()"> Khoảng ngày</label>
            <label><input type="radio" name="mode" value="day"   <?= $mode === "day"   ? "checked" : "" ?> onchange="toggleRevenueInputs()"> Ngày</label>
            <label><input type="radio" name="mode" value="week"  <?= $mode === "week"  ? "checked" : "" ?> onchange="toggleRevenueInputs()"> Tuần</label>
            <label><input type="radio" name="mode" value="month" <?= $mode === "month" ? "checked" : "" ?> onchange="toggleRevenueInputs()"> Tháng</label>
        </div>
    </div>

    <div id="box-day">
        <label for="day">Chọn ngày</label><br>
        <input type="date" name="day" id="day" value="<?= e($dayValue) ?>">
    </div>

    <div id="box-week">
        <label for="week">Chọn tuần</label><br>
        <input type="week" name="week" id="week" value="<?= e($weekValue) ?>">
    </div>

    <div id="box-month">
        <label for="month">Chọn tháng</label><br>
        <input type="month" name="month" id="month" value="<?= e($monthValue) ?>">
    </div>

    <div id="box-from">
        <label for="from">Từ ngày</label><br>
        <input type="date" name="from" id="from" value="<?= e($fromValue) ?>">
    </div>

    <div id="box-to">
        <label for="to">Đến ngày</label><br>
        <input type="date" name="to" id="to" value="<?= e($toValue) ?>">
    </div>

    <button type="submit" class="btn">Lọc</button>

    <a href="revenue.php?<?= e($query) ?>&export=excel" class="btn">Xuất Excel</a>
    <a href="revenue.php?<?= e($query) ?>&export=word" class="btn">Xuất Word</a>
    <a href="orders.php" class="btn">Quay lại đơn hàng</a>

</form>

<p>
    <strong>Kỳ báo cáo:</strong> <?= e($label) ?>
    &nbsp;|&nbsp;
    <em>Chỉ tính đơn “<?= e(REVENUE_STATUS) ?>”</em>
</p>


<?php if (empty($rows)): ?>

    <div class="empty">
        Không có đơn hoàn thành nào trong khoảng thời gian này.
    </div>

<?php else: ?>

    <div class="admin-table-wrap">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Số đơn</th>
                    <th>Doanh thu</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($rows as $row): ?>

                    <tr>
                        <td><?= e($row["period"]) ?></td>
                        <td><?= $row["orders"] ?></td>
                        <td><?= money($row["revenue"]) ?></td>
                    </tr>

                <?php endforeach; ?>

                <tr style="font-weight:bold;">
                    <td>Tổng cộng</td>
                    <td><?= $totalOrders ?></td>
                    <td><?= money($totalRevenue) ?></td>
                </tr>

            </tbody>

        </table>

    </div>

<?php endif; ?>


<script>
// Mỗi chế độ chỉ hiện ô nhập của riêng nó
function toggleRevenueInputs() {
    var mode = document.querySelector('input[name="mode"]:checked').value;
    var show = {
        "box-day":   mode === "day",
        "box-week":  mode === "week",
        "box-month": mode === "month",
        "box-from":  mode === "range",
        "box-to":    mode === "range"
    };
    for (var id in show) {
        document.getElementById(id).style.display = show[id] ? "" : "none";
    }
}
toggleRevenueInputs();
</script>


<?php include "../includes/footer.php"; ?>
