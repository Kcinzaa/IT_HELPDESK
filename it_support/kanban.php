<?php
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';
require_once '../includes/auth.php';

checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();
$ticketObj = new Ticket($conn);

// ดึงงานทั้งหมดมาเพื่อแยกใส่แต่ละคอลัมน์
$all_tickets = $ticketObj->getAllTickets();

$pending = [];
$in_progress = [];
$resolved = [];

// แยกงานตามสถานะ
foreach ($all_tickets as $t) {
    if ($t['status'] == 'Pending') $pending[] = $t;
    elseif ($t['status'] == 'In Progress') $in_progress[] = $t;
    else $resolved[] = $t;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<style>
    .kanban-board { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; min-height: 70vh; }
    .kanban-col { flex: 1; min-width: 300px; background-color: #f1f5f9; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; }
    .kanban-col-header { font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; }
    .kanban-items { flex-grow: 1; min-height: 200px; }
    
    .kanban-card { 
        background: white; border-radius: 8px; padding: 15px; margin-bottom: 15px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.05); cursor: grab; border-left: 4px solid #cbd5e1;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kanban-card:active { cursor: grabbing; transform: scale(1.02); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
    .card-pending { border-left-color: #ef4444; }
    .card-progress { border-left-color: #f59e0b; }
    .card-resolved { border-left-color: #10b981; }
    
    /* กล่องเงาเวลาลาก (Ghost) */
    .sortable-ghost { opacity: 0.4; background-color: #e2e8f0; border: 2px dashed #94a3b8; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-kanban text-primary me-2"></i> กระดานจัดการงาน </h3>
    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> ลากการ์ดเพื่อเปลี่ยนสถานะงานได้ทันที</span>
</div>

<div class="kanban-board">
    
    <div class="kanban-col">
        <div class="kanban-col-header text-danger"><i class="bi bi-circle-fill me-2 small"></i> รอดำเนินการ (<?php echo count($pending); ?>)</div>
        <div class="kanban-items" id="col-pending" data-status="Pending">
            <?php foreach($pending as $t): ?>
                <div class="kanban-card card-pending" data-id="<?php echo $t['ticket_id']; ?>">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold text-primary">#TK-<?php echo $t['ticket_id']; ?></span>
                        <span class="badge bg-light text-dark border"><?php echo $t['category']; ?></span>
                    </div>
                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($t['title']); ?></h6>
                    <small class="text-muted d-block mb-2"><i class="bi bi-geo-alt me-1"></i> <?php echo $t['dept_name']; ?></small>
                    <a href="ticket_detail.php?id=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-outline-secondary w-100 mt-2">ดูรายละเอียด</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="kanban-col">
        <div class="kanban-col-header text-warning"><i class="bi bi-circle-fill me-2 small"></i> กำลังดำเนินการ (<?php echo count($in_progress); ?>)</div>
        <div class="kanban-items" id="col-progress" data-status="In Progress">
            <?php foreach($in_progress as $t): ?>
                <div class="kanban-card card-progress" data-id="<?php echo $t['ticket_id']; ?>">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold text-primary">#TK-<?php echo $t['ticket_id']; ?></span>
                        <span class="badge bg-light text-dark border"><?php echo $t['category']; ?></span>
                    </div>
                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($t['title']); ?></h6>
                    <small class="text-muted d-block mb-2"><i class="bi bi-person me-1"></i> ช่าง: <?php echo $t['it_name'] ?? 'คุณ'; ?></small>
                    <a href="ticket_detail.php?id=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-outline-secondary w-100 mt-2">ดู/อัปเดตงาน</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="kanban-col">
        <div class="kanban-col-header text-success"><i class="bi bi-circle-fill me-2 small"></i> ปิดงานแล้ว (<?php echo count($resolved); ?>)</div>
        <div class="kanban-items" id="col-resolved" data-status="Resolved">
            <?php foreach($resolved as $t): ?>
                <div class="kanban-card card-resolved" data-id="<?php echo $t['ticket_id']; ?>">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold text-secondary">#TK-<?php echo $t['ticket_id']; ?></span>
                        <span class="badge bg-light text-dark border"><?php echo $t['category']; ?></span>
                    </div>
                    <h6 class="fw-bold text-muted mb-1 text-decoration-line-through"><?php echo htmlspecialchars($t['title']); ?></h6>
                    <small class="text-muted d-block mb-2"><i class="bi bi-check2-circle me-1"></i> ปิดงานเรียบร้อย</small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const columns = ['col-pending', 'col-progress', 'col-resolved'];
    
    columns.forEach(colId => {
        const el = document.getElementById(colId);
        new Sortable(el, {
            group: 'kanban', // ทำให้ลากข้ามคอลัมน์ได้
            animation: 150,
            ghostClass: 'sortable-ghost',
            
            // เมื่อปล่อยเมาส์ (ลากเสร็จ)
            onEnd: function (evt) {
                const itemEl = evt.item; // การ์ดที่ถูกลาก
                const toList = evt.to;   // คอลัมน์เป้าหมายที่ถูกปล่อย
                
                const ticketId = itemEl.getAttribute('data-id');
                const newStatus = toList.getAttribute('data-status');
                const oldStatus = evt.from.getAttribute('data-status');

                // ถ้าลากกลับมาที่เดิม ไม่ต้องทำอะไร
                if (newStatus === oldStatus) return;

                // เปลี่ยนสีแถบด้านซ้ายของการ์ดตามสถานะใหม่ทันที
                itemEl.classList.remove('card-pending', 'card-progress', 'card-resolved');
                if(newStatus === 'Pending') itemEl.classList.add('card-pending');
                else if(newStatus === 'In Progress') itemEl.classList.add('card-progress');
                else itemEl.classList.add('card-resolved');

                // 🚀 ยิง API ไปหลังบ้านเพื่ออัปเดตฐานข้อมูล (ใช้ API ตัวที่เราเคยสร้างไว้!)
                fetch('../api/update_ticket_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ticket_id: ticketId, status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // โชว์แจ้งเตือนเบาๆ มุมจอ
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success',
                            title: `อัปเดตงาน #TK-${ticketId} เป็น ${newStatus} แล้ว`,
                            showConfirmButton: false, timer: 3000
                        });
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                        window.location.reload(); // ถ้าระบบพัง ให้โหลดหน้าใหม่เพื่อคืนค่า
                    }
                });
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>