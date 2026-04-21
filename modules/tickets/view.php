<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../core/Ticket.php';
require_once '../../includes/auth.php';

checkAuth(['staff', 'it', 'admin']);

$db = new Database();
$conn = $db->connect();
$ticketObj = new Ticket($conn);

$myTickets = $ticketObj->getMyTickets($_SESSION['user_id']);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-clock-history text-primary me-2"></i> ประวัติการแจ้งซ่อมของฉัน</h3>
    <a href="create.php" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg me-1"></i> แจ้งซ่อมใหม่</a>
</div>

<div class="card card-custom p-4 shadow-sm border-top border-primary border-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle w-100">
            <thead class="table-light text-muted">
                <tr>
                    <th>รหัสแจ้งซ่อม</th>
                    <th>วันที่แจ้ง</th>
                    <th>หมวดหมู่</th>
                    <th>หัวข้อปัญหา</th>
                    <th>สถานะ</th>
                    <th>เวลาปิดงาน</th>
                    <th class="text-center">แชท/รายละเอียด</th> </tr>
            </thead>
            <tbody>
                <?php if(empty($myTickets)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">คุณยังไม่มีประวัติการแจ้งซ่อม</td></tr>
                <?php else: ?>
                    <?php foreach($myTickets as $t): ?>
                    <tr>
                        <td class="fw-bold text-primary">#TK-<?php echo $t['ticket_id']; ?></td>
                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($t['category']); ?></span></td>
                        <td><?php echo htmlspecialchars($t['title']); ?></td>
                        <td>
                            <?php 
                                if($t['status'] == 'Pending') echo '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-hourglass-split me-1"></i> รอดำเนินการ</span>';
                                elseif($t['status'] == 'In Progress') echo '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning"><i class="bi bi-tools me-1"></i> กำลังซ่อม</span>';
                                else echo '<span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle me-1"></i> เสร็จสิ้น</span>';
                            ?>
                        </td>
                        <td class="text-muted small">
                            <?php echo ($t['resolved_at']) ? date('d/m/Y H:i', strtotime($t['resolved_at'])) : '-'; ?>
                        </td>
                
                        <td class="text-center">
                            <a href="ticket_detail.php?id=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-info text-white shadow-sm fw-bold">
                                <i class="bi bi-chat-dots-fill me-1"></i> คุยกับช่าง
                            </a>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function rateRepair(ticketId) {
    Swal.fire({
        title: 'ประเมินการซ่อมแซม',
        html: `
            <form id="ratingForm" action="rate_ticket.php" method="POST">
                <input type="hidden" name="ticket_id" value="${ticketId}">
                <select name="rating" class="form-select form-select-lg mb-3" required>
                    <option value="5">⭐⭐⭐⭐⭐ ดีเยี่ยม (5 ดาว)</option>
                    <option value="4">⭐⭐⭐⭐ ดีมาก (4 ดาว)</option>
                    <option value="3">⭐⭐⭐ ปานกลาง (3 ดาว)</option>
                    <option value="2">⭐⭐ พอใช้ (2 ดาว)</option>
                    <option value="1">⭐ ต้องปรับปรุง (1 ดาว)</option>
                </select>
                <textarea name="feedback" class="form-control" rows="2" placeholder="ข้อเสนอแนะเพิ่มเติม (ถ้ามี)"></textarea>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'ส่งแบบประเมิน',
        cancelButtonText: 'ยกเลิก',
        preConfirm: () => { document.getElementById('ratingForm').submit(); }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>