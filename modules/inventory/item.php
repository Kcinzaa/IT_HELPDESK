<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// อนุญาตเฉพาะ IT และ Admin
checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();

// ==========================================
// 🚀 ระบบจัดการ: เพิ่ม / แก้ไข / ลบ / เติมสต็อก
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ➕ เพิ่มอะไหล่ใหม่
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = trim($_POST['item_name']);
        $type = $_POST['item_type'];
        $qty = $_POST['stock_quantity'];
        $unit = trim($_POST['unit']);
        $min = $_POST['min_threshold'];

        $stmt = $conn->prepare("INSERT INTO inventory (item_name, item_type, stock_quantity, unit, min_threshold) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $type, $qty, $unit, $min])) {
            $msg = "<script>Swal.fire('สำเร็จ!', 'เพิ่มรายการอะไหล่เรียบร้อย', 'success');</script>";
        }
    }

    // ✏️ แก้ไขข้อมูลอะไหล่
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = $_POST['item_id'];
        $name = trim($_POST['item_name']);
        $type = $_POST['item_type'];
        $qty = $_POST['stock_quantity'];
        $unit = trim($_POST['unit']);
        $min = $_POST['min_threshold'];

        $stmt = $conn->prepare("UPDATE inventory SET item_name=?, item_type=?, stock_quantity=?, unit=?, min_threshold=? WHERE item_id=?");
        if ($stmt->execute([$name, $type, $qty, $unit, $min, $id])) {
            $msg = "<script>Swal.fire('สำเร็จ!', 'อัปเดตข้อมูลอะไหล่แล้ว', 'success');</script>";
        }
    }

    // 📦 เติมสต็อกด่วน
    if (isset($_POST['action']) && $_POST['action'] == 'restock') {
        $id = $_POST['item_id'];
        $add_qty = $_POST['add_qty'];
        
        $stmt = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity + ? WHERE item_id=?");
        if ($stmt->execute([$add_qty, $id])) {
            $msg = "<script>Swal.fire('สต็อกอัปเดต!', 'เติมสต็อกเข้าคลังเรียบร้อย', 'success');</script>";
        }
    }

    // 🗑️ ลบข้อมูล
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['item_id'];
        $stmt = $conn->prepare("DELETE FROM inventory WHERE item_id=?");
        if ($stmt->execute([$id])) {
            $msg = "<script>Swal.fire('ลบแล้ว!', 'ลบรายการออกจากคลังเรียบร้อย', 'success');</script>";
        }
    }
}

// 📂 ดึงข้อมูลอะไหล่ทั้งหมดมาแสดง
$stmt = $conn->query("SELECT * FROM inventory ORDER BY item_name ASC");
$items = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-tools text-primary me-2"></i> จัดการรายการวัสดุ/อะไหล่ IT</h3>
            <p class="text-muted">ตรวจสอบสต็อกคงเหลือ เบิกจ่าย และเติมอะไหล่</p>
        </div>
        <button class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-2"></i> เพิ่มรายการอะไหล่ใหม่
        </button>
    </div>

    <?php if(isset($msg)) echo $msg; ?>

    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="inventoryTable">
                <thead class="table-dark">
                    <tr>
                        <th class="border-0 rounded-start">รหัส (ID)</th>
                        <th class="border-0">ชื่อวัสดุ/อะไหล่</th>
                        <th class="border-0 text-center">หมวดหมู่</th>
                        <th class="border-0 text-center">คงเหลือ</th>
                        <th class="border-0 text-center">จุดสั่งซื้อ (Min)</th>
                        <th class="border-0 text-center">สถานะสต็อก</th>
                        <th class="border-0 text-center rounded-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $i): ?>
                    <tr>
                        <td class="text-muted">#<?php echo $i['item_id']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($i['item_name']); ?></td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                <?php echo htmlspecialchars($i['item_type']); ?>
                            </span>
                        </td>
                        <td class="text-center fw-bold fs-5 text-primary">
                            <?php echo $i['stock_quantity']; ?> <span class="fs-6 text-muted fw-normal"><?php echo htmlspecialchars($i['unit']); ?></span>
                        </td>
                        <td class="text-center text-muted"><?php echo $i['min_threshold']; ?></td>
                        <td class="text-center">
                            <?php 
                                if($i['stock_quantity'] <= 0) {
                                    echo '<span class="badge bg-danger pulse"><i class="bi bi-x-circle me-1"></i>ของหมด!</span>';
                                } elseif($i['stock_quantity'] <= $i['min_threshold']) {
                                    echo '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>ใกล้หมด</span>';
                                } else {
                                    echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>ปกติ</span>';
                                }
                            ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-success mx-1" 
                                data-bs-toggle="modal" data-bs-target="#restockModal"
                                data-id="<?php echo $i['item_id']; ?>"
                                data-name="<?php echo htmlspecialchars($i['item_name']); ?>"
                                data-unit="<?php echo htmlspecialchars($i['unit']); ?>"
                                title="เติมสต็อก">
                                <i class="bi bi-box-arrow-in-down"></i>
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-outline-secondary mx-1" 
                                data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="<?php echo $i['item_id']; ?>"
                                data-name="<?php echo htmlspecialchars($i['item_name']); ?>"
                                data-type="<?php echo htmlspecialchars($i['item_type']); ?>"
                                data-qty="<?php echo $i['stock_quantity']; ?>"
                                data-unit="<?php echo htmlspecialchars($i['unit']); ?>"
                                data-min="<?php echo $i['min_threshold']; ?>"
                                title="แก้ไข">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            
                            <form method="POST" class="d-inline" id="deleteForm_<?php echo $i['item_id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_id" value="<?php echo $i['item_id']; ?>">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $i['item_id']; ?>)" title="ลบ">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>เพิ่มรายการอะไหล่ใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อวัสดุ/อะไหล่ <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">หมวดหมู่</label>
                        <select name="item_type" class="form-select">
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Network">Network</option>
                            <option value="Other">อื่นๆ</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">จำนวนสต็อกตั้งต้น <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">หน่วยนับ <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" placeholder="เช่น อัน, เส้น, กล่อง" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-danger">แจ้งเตือนเมื่อของเหลือน้อยกว่า (ชิ้น) <span class="text-danger">*</span></label>
                        <input type="number" name="min_threshold" class="form-control" value="5" min="0" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลอะไหล่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="item_id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อวัสดุ/อะไหล่ <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">หมวดหมู่</label>
                        <select name="item_type" id="edit_type" class="form-select">
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Network">Network</option>
                            <option value="Other">อื่นๆ</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">คงเหลือ <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" id="edit_qty" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">หน่วยนับ <span class="text-danger">*</span></label>
                            <input type="text" name="unit" id="edit_unit" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-danger">แจ้งเตือนเมื่อของเหลือน้อยกว่า</label>
                        <input type="number" name="min_threshold" id="edit_min" class="form-control" min="0" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-box-arrow-in-down me-2"></i>เติมสต็อกด่วน</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="restock">
                <input type="hidden" name="item_id" id="restock_id">
                <div class="modal-body p-4 text-center">
                    <p class="fw-bold mb-1" id="restock_name" style="font-size: 1.1rem;"></p>
                    <p class="text-muted small mb-3">ระบุจำนวนที่ต้องการเพิ่มเข้าไปในคลัง</p>
                    
                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text bg-light border-success text-success fw-bold">+</span>
                        <input type="number" name="add_qty" class="form-control border-success text-center fw-bold text-success fs-3" value="1" min="1" required>
                        <span class="input-group-text bg-light border-success" id="restock_unit"></span>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-2">
                    <button type="submit" class="btn btn-success w-100 fw-bold fs-5 py-2">ยืนยันการเติมของ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // 💥 ใส่ setTimeout เพื่อบังคับให้โค้ดนี้ทำงาน "เป็นอันดับสุดท้าย" (รอให้ของเก่ารันเสร็จก่อน ค่อยลบทิ้งแล้วสร้างใหม่)
    setTimeout(function() {
        $('#inventoryTable').DataTable({
            destroy: true,      // สั่งทำลายของเก่าทิ้ง
            retrieve: true,     // ดึงโครงสร้างเดิมมาใช้
            language: { 
                "sSearch": "🔍 ค้นหาอะไหล่:", 
                "sLengthMenu": "แสดง _MENU_ แถว",
                "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว",
                "oPaginate": { "sPrevious": "ก่อนหน้า", "sNext": "ถัดไป" }
            },
            pageLength: 10
        });
    }, 100);

    // นำข้อมูลเข้า Modal แก้ไข
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        $('#edit_id').val(button.data('id'));
        $('#edit_name').val(button.data('name'));
        $('#edit_type').val(button.data('type'));
        $('#edit_qty').val(button.data('qty'));
        $('#edit_unit').val(button.data('unit'));
        $('#edit_min').val(button.data('min'));
    });

    // นำข้อมูลเข้า Modal เติมสต็อก
    $('#restockModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        $('#restock_id').val(button.data('id'));
        $('#restock_name').text(button.data('name'));
        $('#restock_unit').text(button.data('unit'));
    });
});

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "หากลบแล้วข้อมูลอะไหล่รายการนี้จะหายไป!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm_' + id).submit();
        }
    });
}
</script>

<style>
.pulse { animation: pulse 2s infinite; }
@keyframes pulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
</style>

<?php require_once '../../includes/footer.php'; ?>