<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// อนุญาตเฉพาะ IT และ Admin
checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();

// ==========================================
// 🚀 ระบบจัดการ: ดักจับการ แก้ไข และ ลบ ข้อมูล
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✏️ กรณีแก้ไขข้อมูล
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = $_POST['asset_id'];
        $code = trim($_POST['asset_code']);
        $name = trim($_POST['asset_name']);
        $category = $_POST['category'];
        $location = trim($_POST['location']); // เปลี่ยนให้ตรงกับ DB เดิม
        $status = $_POST['status'];
        $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : NULL;

        $stmt = $conn->prepare("UPDATE assets SET asset_code=?, asset_name=?, category=?, location=?, status=?, purchase_date=? WHERE asset_id=?");
        if ($stmt->execute([$code, $name, $category, $location, $status, $purchase_date, $id])) {
            $msg = "<script>Swal.fire('สำเร็จ!', 'อัปเดตข้อมูลทรัพย์สินแล้ว', 'success');</script>";
        }
    }

    // 🗑️ กรณีลบข้อมูล
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['asset_id'];
        $stmt = $conn->prepare("DELETE FROM assets WHERE asset_id=?");
        if ($stmt->execute([$id])) {
            $msg = "<script>Swal.fire('ลบแล้ว!', 'ลบข้อมูลทรัพย์สินเรียบร้อย', 'success');</script>";
        }
    }
}

// 📂 ดึงข้อมูลทรัพย์สินทั้งหมดมาแสดง
$stmt = $conn->query("SELECT * FROM assets ORDER BY created_at DESC");
$assets = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-pc-display text-primary me-2"></i> ระบบจัดการทรัพย์สิน IT</h3>
            <p class="text-muted">ขึ้นทะเบียน จัดการ และติดตามสถานะอุปกรณ์</p>
        </div>
        <a href="add_asset.php" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-plus-circle me-2"></i> ขึ้นทะเบียนอุปกรณ์ใหม่
        </a>
    </div>

    <?php if(isset($msg)) echo $msg; ?>

    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="assetsTable">
                <thead class="table-dark">
                    <tr>
                        <th class="border-0 rounded-start">รหัสทรัพย์สิน (SN)</th>
                        <th class="border-0">ชื่ออุปกรณ์/รุ่น</th>
                        <th class="border-0 text-center">หมวดหมู่</th>
                        <th class="border-0">สถานที่ตั้ง</th>
                        <th class="border-0 text-center">สถานะ</th>
                        <th class="border-0 text-center rounded-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($assets as $a): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($a['asset_code']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($a['asset_name']); ?>
                            <?php if(!empty($a['purchase_date'])): ?>
                                <br><small class="text-muted"><i class="bi bi-calendar3"></i> ซื้อเมื่อ: <?php echo date('d/m/Y', strtotime($a['purchase_date'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                <?php echo htmlspecialchars($a['category']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($a['location']); ?></td>
                        <td class="text-center">
                            <?php 
                                // ปรับให้รองรับคำว่า Active/Inactive ของระบบเก่า
                                if(strtolower($a['status']) == 'active' || $a['status'] == 'ใช้งานปกติ') {
                                    echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>ใช้งานปกติ</span>';
                                } elseif(strtolower($a['status']) == 'repair' || $a['status'] == 'ส่งซ่อม') {
                                    echo '<span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>ส่งซ่อม</span>';
                                } else {
                                    echo '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>เลิกใช้งาน</span>';
                                }
                            ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-dark" onclick="viewQR(<?php echo $a['asset_id']; ?>, '<?php echo $a['asset_code']; ?>')" title="QR Code">
                                <i class="bi bi-qr-code"></i> QR
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-outline-secondary mx-1" 
                                data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="<?php echo $a['asset_id']; ?>"
                                data-code="<?php echo htmlspecialchars($a['asset_code']); ?>"
                                data-name="<?php echo htmlspecialchars($a['asset_name']); ?>"
                                data-category="<?php echo htmlspecialchars($a['category']); ?>"
                                data-location="<?php echo htmlspecialchars($a['location']); ?>"
                                data-purchasedate="<?php echo htmlspecialchars($a['purchase_date']); ?>"
                                data-status="<?php echo htmlspecialchars($a['status']); ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            
                            <form method="POST" class="d-inline" id="deleteForm_<?php echo $a['asset_id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="asset_id" value="<?php echo $a['asset_id']; ?>">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $a['asset_id']; ?>)">
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

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลทรัพย์สิน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="asset_id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">รหัสทรัพย์สิน (SN) <span class="text-danger">*</span></label>
                        <input type="text" name="asset_code" id="edit_code" class="form-control" required readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่ออุปกรณ์/รุ่น <span class="text-danger">*</span></label>
                        <input type="text" name="asset_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">หมวดหมู่</label>
                            <select name="category" id="edit_category" class="form-select">
                                <option value="PC">PC</option>
                                <option value="Notebook">Notebook</option>
                                <option value="Printer">Printer</option>
                                <option value="Network">Network</option>
                                <option value="Other">อื่นๆ</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">สถานะเครื่อง</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Active">ใช้งานปกติ (Active)</option>
                                <option value="Repair">ส่งซ่อม (Repair)</option>
                                <option value="Inactive">เลิกใช้งาน (Inactive)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">สถานที่ตั้ง (แผนก/ตึก)</label>
                        <input type="text" name="location" id="edit_location" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">วันที่ซื้อ (Purchase Date)</label>
                        <input type="date" name="purchase_date" id="edit_purchase_date" class="form-control">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        
        // 🚀 ให้มันปริ้นท์ค่าออกมาดูเลยว่า ดึงข้อมูลมาได้ไหม! (ดูผลลัพธ์ใน F12 > Console)
        console.log("--- เริ่มดึงข้อมูล ---");
        console.log("ID ที่ได้:", button.data('id'));
        console.log("ชื่อที่ได้:", button.data('name'));
        console.log("-------------------");

        $('#edit_id').val(button.data('id'));
        $('#edit_code').val(button.data('code'));
        $('#edit_name').val(button.data('name'));
        $('#edit_category').val(button.data('category'));
        $('#edit_location').val(button.data('location')); 
        $('#edit_purchase_date').val(button.data('purchasedate'));
        
        var status = button.data('status');
        if(status == 'Active' || status == 'ใช้งานปกติ') {
            $('#edit_status').val('Active');
        } else if(status == 'Repair' || status == 'ส่งซ่อม') {
            $('#edit_status').val('Repair');
        } else {
            $('#edit_status').val('Inactive');
        }
    });
});

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "หากลบแล้วข้อมูลทรัพย์สินนี้จะหายไปจากระบบ!",
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
    })
}

function viewQR(id, code) {
    let qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo BASE_URL; ?>modules/assets/view_asset.php?id=" + id;
    Swal.fire({
        title: 'รหัส: ' + code,
        text: 'สแกนเพื่อดูข้อมูลหรือแจ้งซ่อม',
        imageUrl: qrUrl,
        imageWidth: 200,
        imageHeight: 200,
        imageAlt: 'QR Code',
        confirmButtonText: 'ปิด'
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>