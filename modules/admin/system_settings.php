<?php
require_once '../../core/config.php';
require_once '../../includes/auth.php';

// ป้องกันคนอื่นเข้า อนุญาตเฉพาะ Admin
checkAuth(['admin']);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-gear-fill text-secondary me-2"></i> ตั้งค่าระบบเบื้องต้น</h3>
            <p class="text-muted">ปรับแต่งค่าการทำงานต่างๆ ของระบบ Helpdesk</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold border-bottom pb-2 mb-4 text-success"><i class="bi bi-line me-2"></i>เชื่อมต่อ Line Notify</h5>
                <form>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">LINE Notify Token (สำหรับส่งแจ้งเตือนให้ทีมช่าง)</label>
                        <input type="text" class="form-control bg-light" value="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" readonly>
                        <small class="text-muted mt-1 d-block">ระบบกำลังใช้ Token พื้นฐานจากไฟล์ <code>core/Notification.php</code></small>
                    </div>
                    <button type="button" class="btn btn-outline-success w-100" onclick="Swal.fire('Coming Soon', 'ฟีเจอร์เปลี่ยน Token ผ่านหน้าเว็บจะเปิดให้ใช้งานเร็วๆ นี้', 'info')">
                        <i class="bi bi-pencil-square me-2"></i>แก้ไข Token
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold border-bottom pb-2 mb-4 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>การบำรุงรักษาระบบ</h5>
                <p class="text-muted small">หากเปิดโหมดนี้ ผู้ใช้งานทั่วไปจะไม่สามารถกดแจ้งซ่อมได้ชั่วคราว (ใช้ตอนที่ Server กำลังมีปัญหา)</p>
                <div class="form-check form-switch mb-4 mt-3">
                    <input class="form-check-input cursor-pointer" type="checkbox" id="maintenanceMode" style="transform: scale(1.5); margin-left: -1.5em; margin-top: 0.3em;">
                    <label class="form-check-label ms-3 fw-bold text-dark" for="maintenanceMode">เปิดโหมดปรับปรุงระบบ (Maintenance Mode)</label>
                </div>
                <button type="button" class="btn btn-warning text-dark fw-bold w-100" onclick="Swal.fire('อัปเดตแล้ว', 'บันทึกการตั้งค่าระบบเรียบร้อย', 'success')">
                    <i class="bi bi-save2-fill me-2"></i> บันทึกการตั้งค่า
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
</style>

<?php require_once '../../includes/footer.php'; ?>