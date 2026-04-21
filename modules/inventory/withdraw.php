<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// อนุญาตเฉพาะ IT และ Admin
checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();

// ==========================================
// 🚀 ระบบจัดการ: ดักจับการกดปุ่ม "เบิกพัสดุ"
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'withdraw') {
    $item_id = $_POST['item_id'];
    $qty = (int)$_POST['quantity'];
    $ticket_id = !empty($_POST['ticket_id']) ? $_POST['ticket_id'] : NULL;
    $admin_id = $_SESSION['user_id'];

    // 1. เช็คสต็อกก่อนว่าพอให้เบิกไหม
    $stmt = $conn->prepare("SELECT stock_quantity, item_name FROM inventory WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if ($item && $item['stock_quantity'] >= $qty) {
        // 2. ถ้าพอ ให้หักสต็อกในตาราง inventory
        $stmtUpdate = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE item_id = ?");
        $stmtUpdate->execute([$qty, $item_id]);

        // 3. บันทึกประวัติลงตาราง inventory_withdrawals
        $stmtInsert = $conn->prepare("INSERT INTO inventory_withdrawals (item_id, ticket_id, admin_id, quantity) VALUES (?, ?, ?, ?)");
        if ($stmtInsert->execute([$item_id, $ticket_id, $admin_id, $qty])) {
            $msg = "<script>Swal.fire('เบิกสำเร็จ!', 'หักสต็อก {$item['item_name']} จำนวน {$qty} ชิ้นเรียบร้อย', 'success');</script>";
        }
    } else {
        // ถ้าสต็อกไม่พอ
        $msg = "<script>Swal.fire('ข้อผิดพลาด!', 'สต็อกไม่เพียงพอ (เหลือแค่ {$item['stock_quantity']} ชิ้น)', 'error');</script>";
    }
}

// 📂 ดึงรายการอะไหล่ที่มีสต็อก > 0 มาแสดงใน Dropdown
$stmt = $conn->query("SELECT * FROM inventory WHERE stock_quantity > 0 ORDER BY item_name ASC");
$available_items = $stmt->fetchAll();

// 📂 ดึงประวัติการเบิกล่าสุดของตัวเองมาแสดง
$stmt = $conn->prepare("
    SELECT w.*, i.item_name, i.unit, t.ticket_id 
    FROM inventory_withdrawals w
    JOIN inventory i ON w.item_id = i.item_id
    LEFT JOIN tickets t ON w.ticket_id = t.ticket_id
    ORDER BY w.withdraw_date DESC LIMIT 10
");
$stmt->execute();
$history = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-cart-dash text-primary me-2"></i> ระบบเบิกพัสดุ/อะไหล่ IT</h3>
            <p class="text-muted">บันทึกการเบิกใช้งาน และผูกกับรหัสงานซ่อม</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary shadow-sm fw-bold">
            <i class="bi bi-arrow-left me-2"></i> กลับแดชบอร์ด
        </a>
    </div>

    <?php if(isset($msg)) echo $msg; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100 border-top border-primary border-5">
                <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2"></i> ฟอร์มทำรายการเบิก</h5>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="withdraw">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกรายการพัสดุ <span class="text-danger">*</span></label>
                        <select name="item_id" class="form-select select2" required>
                            <option value="">-- พิมพ์ค้นหา หรือ เลือกพัสดุ --</option>
                            <?php foreach($available_items as $item): ?>
                                <option value="<?php echo $item['item_id']; ?>">
                                    <?php echo htmlspecialchars($item['item_name']); ?> (คงเหลือ: <?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">จำนวนที่เบิก <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control form-control-lg text-center text-primary fw-bold" value="1" min="1" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">อ้างอิงรหัสงานแจ้งซ่อม (Ticket ID)</label>
                        <input type="number" name="ticket_id" class="form-control" placeholder="เช่น 15 (เว้นว่างได้หากเบิกทั่วไป)">
                        <small class="text-muted">หากเป็นการเบิกเพื่อนำไปซ่อมงาน ให้ระบุเลข Ticket เพื่อเก็บประวัติ</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-3 fs-5">
                        <i class="bi bi-cart-check-fill me-2"></i> ยืนยันการเบิกพัสดุ
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-primary"></i> ประวัติการทำรายการ (10 รายการล่าสุด)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>วัน-เวลา</th>
                                <th>รายการ</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-center">อ้างอิงงาน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($history)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีประวัติการเบิกพัสดุ</td></tr>
                            <?php else: ?>
                                <?php foreach($history as $h): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($h['withdraw_date'])); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($h['item_name']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-danger bg-opacity-10 text-danger border">-<?php echo $h['quantity']; ?> <?php echo htmlspecialchars($h['unit']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($h['ticket_id']): ?>
                                            <a href="../tickets/ticket_detail.php?id=<?php echo $h['ticket_id']; ?>" class="badge bg-secondary text-decoration-none">#TK-<?php echo $h['ticket_id']; ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // แปลง Dropdown ธรรมดาให้กลายเป็นแบบมีช่องพิมพ์ค้นหา
    $('.select2').select2({
        theme: "bootstrap-5",
        width: '100%'
    });
});
</script>

<style>
/* ปรับแต่ง Select2 ให้เข้ากับ Bootstrap 5 */
.select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: 0.375rem; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>

<?php require_once '../../includes/footer.php'; ?>