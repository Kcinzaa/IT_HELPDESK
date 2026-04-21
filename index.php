<?php
require_once 'core/config.php';
session_start();

// ถ้ายังไม่ได้ล็อกอิน ให้ไปหน้า Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; // ดึงสิทธิ์การใช้งานมาเก็บไว้เช็ค

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="mb-5 text-center text-lg-start">
        <h2 class="fw-bold text-dark">สวัสดีครับคุณ <?php echo $_SESSION['full_name']; ?> 👋</h2>
        <?php if ($role == 'staff'): ?>
            <p class="text-muted small">ยินดีต้อนรับสู่ศูนย์บริการ IT Helpdesk โรงพยาบาลหาดใหญ่ (Service Portal)</p>
        <?php else: ?>
            <p class="text-muted small">ศูนย์ควบคุมระบบ IT Helpdesk (IT Command Center)</p>
        <?php endif; ?>
    </div>

    <?php if ($role == 'staff'): ?>
        <div class="row g-4 mb-5">
            <div class="col-6 col-lg-3">
                <a href="modules/tickets/create.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-megaphone-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">แจ้งซ่อมด่วน</h5>
                    <p class="text-muted small mb-0">ส่งเรื่องแจ้งซ่อมใหม่ระบุพิกัด ตึก/ชั้น/ห้อง</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="modules/tickets/view.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-search fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">ติดตามสถานะ</h5>
                    <p class="text-muted small mb-0">เช็คคิวงานและคุยแชทกับช่าง IT</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="modules/knowledge_base/articles.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-book-half fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">คู่มือแก้ปัญหา</h5>
                    <p class="text-muted small mb-0">ความรู้เบื้องต้นและการแก้ไขปัญหาด้วยตัวเอง</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift cursor-pointer" onclick="showHotline()">
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-telephone-outbound-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">ติดต่อสายด่วน</h5>
                    <p class="text-muted small mb-0">เบอร์โทรติดต่อทีมช่างกรณีฉุกเฉิน</p>
                </div>
            </div>
        </div>

    <?php elseif ($role == 'it'): ?>
        <div class="row g-4 mb-5">
            <div class="col-6 col-lg-3">
                <a href="it_support/dashboard.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-speedometer2 fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">แดชบอร์ดภาพรวม</h5>
                    <p class="text-muted small mb-0">ติดตามสถานะงานและดูกราฟสถิติแบบ Real-time</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="it_support/kanban.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-kanban fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">บอร์ดจัดการงาน</h5>
                    <p class="text-muted small mb-0">อัปเดตสถานะงานอย่างรวดเร็วด้วยการลากวาง (Drag & Drop)</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="modules/tickets/manage.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-list-task fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">รายการงานแบบตาราง</h5>
                    <p class="text-muted small mb-0">ดูคิวงานทั้งหมดและใช้ตัวกรองเพื่อค้นหาอย่างละเอียด</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="modules/reports/monthly_report.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-graph-up-arrow text-primary fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">รายงานประจำเดือน</h5>
                    <p class="text-muted small mb-0">ดูหน้ารายงานประจำเดือน เพื่อสรุปว่าแต่ละเดือนทำอะไรไปบ้าง</p>
                </a>
            </div>
        </div>
    <?php elseif ($role == 'admin'): ?>
        <div class="row g-4 mb-5">
            <div class="col-6 col-lg-3">
                <a href="modules/admin/manage_users.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">จัดการผู้ใช้งาน</h5>
                    <p class="text-muted small mb-0">เพิ่ม/ลบ บัญชีพนักงาน</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="modules/admin/system_settings.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-secondary bg-opacity-10 text-secondary rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-gear-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">ตั้งค่าระบบ</h5>
                    <p class="text-muted small mb-0">ปรับแต่งค่าต่างๆ ในระบบ</p>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="it_support/dashboard.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-seam fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">แดชบอร์ดอะไหล่</h5>
                    <p class="text-muted small mb-0">ดูสถิติงานอะไหล่ทั้งหมด</p>
                </a>
            </div>
        </div>

    <?php else: ?>
        <div class="row g-4 mb-5">
            <div class="col-6 col-lg-3">
                <a href="it_support/dashboard.php" class="card card-custom border-0 shadow-sm text-center p-4 h-100 hover-lift text-decoration-none">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-speedometer2 fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">แดชบอร์ดภาพรวม</h5>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-lift { transition: all 0.3s ease; }
.hover-lift:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
.cursor-pointer { cursor: pointer; }
</style>

<script>
function showHotline() {
    Swal.fire({
        title: 'สายด่วน IT โรงพยาบาล',
        html: '<div class="p-3"><h2 class="text-primary fw-bold mb-3">โทร. 1122</h2><p class="mb-0 text-muted">หรือเบอร์ภายใน 4501-4505</p><p class="small text-danger mt-2">*กรณีระบบล่มหรือกระทบชีวิตผู้ป่วยเท่านั้น*</p></div>',
        icon: 'info',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#0d6efd'
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>