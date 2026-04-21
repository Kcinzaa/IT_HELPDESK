<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../core/Ticket.php';
require_once '../../includes/auth.php';

// เฉพาะช่างและหัวหน้าเท่านั้น
checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();
$ticketObj = new Ticket($conn);

$tickets = $ticketObj->getAllTickets();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-list-task text-primary me-2"></i> จัดการคิวงานแจ้งซ่อม (All Tickets)</h3>
</div>

<div class="card card-custom p-4 shadow-sm border-top border-primary border-4">
    
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" id="searchInput" placeholder="🔍 ค้นหาหัวข้อปัญหา, วอร์ด...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="ticketTable">
            <thead class="table-dark">
                <tr>
                    <th>Ticket ID</th>
                    <th>วัน-เวลาที่แจ้ง</th>
                    <th>วอร์ด / ผู้แจ้ง</th>
                    <th>ปัญหาที่พบ</th>
                    <th>ความเร่งด่วน</th>
                    <th>สถานะ</th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($tickets)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">ยังไม่มีงานแจ้งซ่อมในระบบ</td></tr>
                <?php else: ?>
                    <?php foreach($tickets as $t): ?>
                    <tr>
                        <td class="fw-bold text-primary">#TK-<?php echo $t['ticket_id']; ?></td>
                        <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($t['dept_name']); ?></div>
                            <div class="small text-muted"><i class="bi bi-person"></i> <?php echo htmlspecialchars($t['reporter_name']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($t['title']); ?></td>
                        <td>
                            <?php 
                                if($t['urgency'] == 'Critical') echo '<span class="badge bg-danger rounded-pill px-3">ด่วนมาก</span>';
                                elseif($t['urgency'] == 'High') echo '<span class="badge bg-warning text-dark rounded-pill px-3">ด่วน</span>';
                                else echo '<span class="badge bg-info text-dark rounded-pill px-3">ทั่วไป</span>';
                            ?>
                        </td>
                        <td>
                            <?php 
                                if($t['status'] == 'Pending') echo '<span class="badge border border-secondary text-secondary">รอดำเนินการ</span>';
                                elseif($t['status'] == 'In Progress') echo '<span class="badge bg-warning text-dark shadow-sm">กำลังซ่อม</span>';
                                else echo '<span class="badge bg-success shadow-sm">เสร็จสิ้น</span>';
                            ?>
                        </td>
                        <td class="text-center">
                            <a href="../../it_support/ticket_detail.php?id=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-primary shadow-sm">
                                <i class="bi bi-wrench-adjustable me-1"></i> เปิดดู / อัปเดตงาน
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
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#ticketTable tbody tr');
    
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>