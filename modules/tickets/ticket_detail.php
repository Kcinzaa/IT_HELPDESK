<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../core/Ticket.php';
require_once '../../includes/auth.php';

checkAuth(['staff', 'it', 'admin']);

if (!isset($_GET['id'])) {
    header("Location: view.php");
    exit();
}

$db = new Database();
$conn = $db->connect();
$ticketObj = new Ticket($conn);
$ticket = $ticketObj->getTicketById($_GET['id']);

// ระบบรักษาความปลอดภัย: ป้องกันพยาบาลวอร์ดอื่นแอบดูงานคนอื่น
if ($_SESSION['role'] === 'staff' && $ticket['user_id'] != $_SESSION['user_id']) {
    die("<h3 class='text-center mt-5 text-danger'>❌ คุณไม่มีสิทธิ์เข้าถึงข้อมูลของผู้อื่น</h3>");
}

// โค้ดรับข้อมูลแชทเมื่อพยาบาลพิมพ์ตอบกลับ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $sql = "INSERT INTO ticket_comments (ticket_id, user_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ticket['ticket_id'], $_SESSION['user_id'], $msg]);
    
    // รีเฟรชหน้าเพื่อให้ข้อความใหม่ขึ้น
    header("Location: ticket_detail.php?id=" . $ticket['ticket_id']);
    exit();
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-chat-left-text-fill text-primary me-2"></i> รายละเอียดงาน #TK-<?php echo $ticket['ticket_id']; ?></h3>
    <a href="view.php" class="btn btn-secondary text-white"><i class="bi bi-arrow-left"></i> กลับหน้ารายการ</a>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card card-custom p-4 border-top border-secondary border-4 h-100">
            <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-file-earmark-text me-2"></i> ข้อมูลการแจ้งซ่อม</h5>
            <div class="mb-3">
                <small class="text-muted d-block">หมวดหมู่:</small>
                <span class="badge bg-secondary fs-6"><?php echo $ticket['category']; ?></span>
            </div>
            <div class="mb-3">
                <small class="text-muted d-block">หัวข้อปัญหา:</small>
                <div class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($ticket['title']); ?></div>
            </div>
            <div class="mb-3">
                <small class="text-muted d-block">รายละเอียด:</small>
                <div class="bg-light p-3 rounded-3 border"><?php echo nl2br(htmlspecialchars($ticket['problem_desc'])); ?></div>
            </div>
            <?php if(!empty($ticket['image_path'])): ?>
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">ภาพประกอบ:</small>
                    <img src="../../uploads/<?php echo $ticket['image_path']; ?>" class="img-fluid rounded shadow-sm">
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-custom border-top border-info border-4 h-100 d-flex flex-column">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="fw-bold mb-0 text-info"><i class="bi bi-chat-dots-fill me-2"></i> แชทพูดคุยกับช่าง IT</h5>
            </div>
            
            <div class="card-body p-4 overflow-auto" style="height: 450px; background-color: #f8f9fa;">
                <?php 
                    $stmt = $conn->prepare("SELECT c.*, u.full_name, u.role FROM ticket_comments c JOIN users u ON c.user_id = u.user_id WHERE c.ticket_id = ? ORDER BY c.created_at ASC");
                    $stmt->execute([$ticket['ticket_id']]);
                    $comments = $stmt->fetchAll();
                    
                    if(empty($comments)) echo '<div class="text-center text-muted mt-5"><i class="bi bi-chat-square text-light fs-1 d-block mb-2"></i> ยังไม่มีข้อความการพูดคุย</div>';
                    
                    foreach($comments as $c): 
                        $is_me = ($c['user_id'] == $_SESSION['user_id']);
                        $is_it = in_array($c['role'], ['it', 'admin']);
                        
                        // แต่งสีให้ข้อความ (ช่าง IT สีน้ำเงิน, พยาบาลสีขาว)
                        $bg_color = $is_me ? 'bg-primary text-white' : ($is_it ? 'bg-info bg-opacity-10 border border-info' : 'bg-white border');
                        $align = $is_me ? 'text-end' : 'text-start';
                ?>
                    <div class="mb-3 <?php echo $align; ?>">
                        <small class="text-muted" style="font-size: 0.75rem;">
                            <?php echo $is_it ? '🛠️ ช่าง ' : '👩‍⚕️ '; ?>
                            <span class="fw-bold"><?php echo $c['full_name']; ?></span> • <?php echo date('H:i', strtotime($c['created_at'])); ?>
                        </small>
                        <div class="d-inline-block p-2 px-3 rounded-3 mt-1 shadow-sm <?php echo $bg_color; ?>" style="max-width: 85%; text-align: left;">
                            <?php echo nl2br(htmlspecialchars($c['message'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-footer bg-white p-3 border-top">
                <?php if($ticket['status'] != 'Resolved'): ?>
                    <form method="POST" action="">
                        <div class="input-group shadow-sm rounded">
                            <input type="text" name="message" class="form-control form-control-lg border-primary" placeholder="พิมพ์ข้อความอัปเดตงาน..." required autocomplete="off">
                
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success mb-0 text-center py-2">
                        <i class="bi bi-check-circle-fill me-2"></i> งานซ่อมนี้เสร็จสิ้นแล้ว ไม่สามารถส่งข้อความได้
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>