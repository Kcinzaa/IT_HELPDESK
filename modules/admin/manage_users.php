<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// ล็อกประตู! อนุญาตเฉพาะ Admin เท่านั้น
checkAuth(['admin']);

$db = new Database();
$conn = $db->connect();

// 🚀 ดักจับการทำงาน เพิ่ม/ลบ ผู้ใช้งาน
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // กรณีเพิ่มผู้ใช้ใหม่
    if (isset($_POST['action']) && $_POST['action'] == 'add_user') {
        $username = trim($_POST['username']);
        $fullname = trim($_POST['full_name']);
        $role = $_POST['role'];
        $dept = trim($_POST['dept_id']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสผ่าน

        try {
            $sql = "INSERT INTO users (username, password, full_name, role, dept_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username, $password, $fullname, $role, $dept]);
            $msg = "<script>Swal.fire('สำเร็จ!', 'เพิ่มผู้ใช้งานใหม่เรียบร้อย', 'success');</script>";
        } catch (PDOException $e) {
            $msg = "<script>Swal.fire('ข้อผิดพลาด!', 'Username นี้อาจมีในระบบแล้ว กรุณาใช้ชื่ออื่น', 'error');</script>";
        }
    }
    
    // กรณีลบผู้ใช้
    if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
        $user_id = $_POST['user_id'];
        // ป้องกัน Admin เผลอลบตัวเอง
        if ($user_id != $_SESSION['user_id']) {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $msg = "<script>Swal.fire('ลบแล้ว!', 'ลบผู้ใช้งานออกจากระบบเรียบร้อย', 'success');</script>";
        } else {
            $msg = "<script>Swal.fire('หยุดนะ!', 'คุณไม่สามารถลบบัญชีตัวเองได้', 'warning');</script>";
        }
    }
}

// ดึงข้อมูลผู้ใช้ทั้งหมดมาแสดง
$stmt = $conn->query("SELECT user_id, username, full_name, role, dept_id FROM users ORDER BY user_id DESC");
$all_users = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-people-fill text-warning me-2"></i> จัดการผู้ใช้งานระบบ</h3>
            <p class="text-muted">เพิ่ม ลบ หรือแก้ไขบัญชีของพนักงานและช่าง IT</p>
        </div>
        <button class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill me-2"></i> เพิ่มผู้ใช้งานใหม่
        </button>
    </div>

    <?php if(isset($msg)) echo $msg; ?>

    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">ID</th>
                        <th class="border-0">ชื่อ-นามสกุล</th>
                        <th class="border-0">Username</th>
                        <th class="border-0">แผนก (วอร์ด)</th>
                        <th class="border-0">ระดับสิทธิ์ (Role)</th>
                        <th class="border-0 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_users as $u): ?>
                    <tr>
                        <td class="text-muted">#<?php echo $u['user_id']; ?></td>
                        <td class="fw-bold">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['full_name']); ?>&background=random" class="rounded-circle me-2" width="30">
                            <?php echo htmlspecialchars($u['full_name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary border"><?php echo htmlspecialchars($u['dept_id']); ?></span></td>
                        <td>
                            <?php 
                                if($u['role'] == 'admin') echo '<span class="badge bg-warning text-dark"><i class="bi bi-shield-lock-fill"></i> Admin</span>';
                                elseif($u['role'] == 'it') echo '<span class="badge bg-info text-dark"><i class="bi bi-tools"></i> IT Support</span>';
                                else echo '<span class="badge bg-light text-dark border">พยาบาล/Staff</span>';
                            ?>
                        </td>
                        <td class="text-center">
                            <?php if($u['user_id'] != $_SESSION['user_id']): ?>
                            <form method="POST" class="d-inline" id="deleteForm_<?php echo $u['user_id']; ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $u['user_id']; ?>)">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                            <?php else: ?>
                                <span class="text-muted small">ตัวคุณเอง</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>สร้างบัญชีผู้ใช้ใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required placeholder="เช่น นพ.สมชาย ใจดี">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">แผนก / วอร์ด <span class="text-danger">*</span></label>
                        <input type="text" name="dept_id" class="form-control" required placeholder="เช่น OPD, ER, เภสัชกรรม">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">ระดับสิทธิ์การใช้งาน <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="staff">พยาบาล / เจ้าหน้าที่ (Staff)</option>
                            <option value="it">ช่างคอมพิวเตอร์ (IT Support)</option>
                            <option value="admin">ผู้ดูแลระบบ (Admin)</option>
                        </select>
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

<script>
// แปลงตารางธรรมดาให้ค้นหาและแบ่งหน้าได้
$(document).ready(function() {
    if ($('#usersTable').length > 0) {
        $('#usersTable').DataTable({
            language: { "sSearch": "🔍 ค้นหา:", "sLengthMenu": "แสดง _MENU_ รายการ" },
            pageLength: 10
        });
    }
});

// แจ้งเตือนก่อนลบ
function confirmDelete(userId) {
    Swal.fire({
        title: 'ยืนยันการลบบัญชี?',
        text: "หากลบแล้วจะไม่สามารถเข้าสู่ระบบด้วยบัญชีนี้ได้อีก!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm_' + userId).submit();
        }
    })
}
</script>

<?php require_once '../../includes/footer.php'; ?>