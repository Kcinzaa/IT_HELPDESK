<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../core/Notification.php';
require_once '../../includes/auth.php';

checkAuth(['it', 'admin']);

// เมื่อมีการกดปุ่ม Submit ฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->connect();

    $code = $_POST['asset_code'];
    $name = $_POST['asset_name'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $location = $_POST['location'];
    $date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;

    try {
        $sql = "INSERT INTO assets (asset_code, asset_name, category, status, purchase_date, location) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$code, $name, $category, $status, $date, $location]);

        // เก็บ Log ว่าใครเป็นคนเพิ่มอุปกรณ์
        Notification::logActivity($_SESSION['full_name'], "เพิ่มอุปกรณ์ใหม่เข้าระบบ: {$code} ({$name})");

        echo "<script>
                alert('ขึ้นทะเบียนอุปกรณ์สำเร็จ!');
                window.location.href = 'list_assets.php';
              </script>";
    } catch(PDOException $e) {
        // ดักจับ Error เผื่อกรอกรหัสทรัพย์สินซ้ำ
        $error = "เกิดข้อผิดพลาด: อาจมีรหัสทรัพย์สินนี้ในระบบแล้ว";
    }
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-node-plus text-primary me-2"></i> ขึ้นทะเบียนอุปกรณ์ใหม่</h3>
    <a href="list_assets.php" class="btn btn-secondary text-white"><i class="bi bi-arrow-left"></i> กลับหน้ารายการ</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom p-4 shadow-sm border-top border-primary border-4">
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">รหัสทรัพย์สิน (Asset Code) <span class="text-danger">*</span></label>
                        <input type="text" name="asset_code" class="form-control" required placeholder="เช่น PC-OPD-01">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">หมวดหมู่อุปกรณ์ <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="PC">คอมพิวเตอร์ (PC/Laptop)</option>
                            <option value="Printer">เครื่องพิมพ์ (Printer/Scanner)</option>
                            <option value="Network">อุปกรณ์เครือข่าย (Network)</option>
                            <option value="Medical IT">อุปกรณ์ IT ทางการแพทย์</option>
                            <option value="Other">อื่นๆ</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่ออุปกรณ์ / รุ่น (Model) <span class="text-danger">*</span></label>
                    <input type="text" name="asset_name" class="form-control" required placeholder="เช่น Dell OptiPlex 7090">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">สถานที่ตั้ง / วอร์ด</label>
                        <input type="text" name="location" class="form-control" placeholder="เช่น แผนกอายุรกรรมชาย ชั้น 2">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">สถานะการใช้งาน</label>
                        <select name="status" class="form-select">
                            <option value="Active">ใช้งานปกติ (Active)</option>
                            <option value="Repair">ส่งซ่อม (Repair)</option>
                            <option value="Inactive">ไม่ได้ใช้งาน / สต๊อก (Inactive)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">วันที่จัดซื้อ</label>
                    <input type="date" name="purchase_date" class="form-control">
                </div>

                <hr class="text-secondary">
                <div class="d-flex justify-content-end mt-3">
                    <button type="reset" class="btn btn-light me-2">ล้างข้อมูล</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-2"></i> บันทึกข้อมูลทรัพย์สิน</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>