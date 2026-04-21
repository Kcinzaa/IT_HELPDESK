<?php
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';
require_once '../includes/auth.php';

checkAuth(['it', 'admin']);

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

// ==========================================
// 🚀 ระบบดักจับการส่งข้อมูล (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $ticket_id_post = $_POST['ticket_id'];

    // 1. กรณีส่งข้อความแชทปกติ
    if (!empty($_POST['message'])) {
        $message = trim($_POST['message']);
        $stmt = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id_post, $user_id, $message]);
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $ticket_id_post);
        exit();
    }

    // 2. กรณีบันทึกการเบิกอะไหล่
    if (isset($_POST['action']) && $_POST['action'] == 'withdraw_asset') {
        $item_id = $_POST['item_id'];
        $qty = (int)$_POST['quantity'];

        // เช็คสต็อกก่อนหัก
        $stmtCheck = $conn->prepare("SELECT stock_quantity, item_name FROM inventory WHERE item_id = ?");
        $stmtCheck->execute([$item_id]);
        $item = $stmtCheck->fetch();

        if ($item && $item['stock_quantity'] >= $qty) {
            // หักสต็อก
            $stmtUpd = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE item_id = ?");
            $stmtUpd->execute([$qty, $item_id]);

            // บันทึกการเบิก
            $stmtIns = $conn->prepare("INSERT INTO inventory_withdrawals (item_id, ticket_id, admin_id, quantity) VALUES (?, ?, ?, ?)");
            $stmtIns->execute([$item_id, $ticket_id_post, $user_id, $qty]);

            // บันทึกข้อความลงแชทอัตโนมัติว่ามีการเบิกของ
            $log_msg = "🛠️ [ระบบ] บันทึกการใช้อะไหล่: " . $item['item_name'] . " จำนวน " . $qty;
            $stmtLog = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, message) VALUES (?, ?, ?)");
            $stmtLog->execute([$ticket_id_post, $user_id, $log_msg]);

            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $ticket_id_post . "&success=1");
            exit();
        }
    }
}

$ticketObj = new Ticket($conn);
$ticket = $ticketObj->getTicketById($_GET['id']);

if (!$ticket) {
    die("❌ ไม่พบข้อมูลการแจ้งซ่อมรหัสนี้");
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-ticket-detailed text-primary me-2"></i> รายละเอียดงาน #TK-<?php echo $ticket['ticket_id']; ?></h3>
    <a href="kanban.php" class="btn btn-secondary text-white"><i class="bi bi-arrow-left"></i> กลับหน้าบอร์ด</a>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card card-custom p-4 mb-4 shadow-sm border-0">
            <h5 class="border-bottom pb-2 mb-3 text-primary fw-bold">ข้อมูลการแจ้งซ่อม</h5>
            <div class="row mb-2">
                <div class="col-sm-4 text-muted fw-bold">ผู้แจ้ง:</div>
                <div class="col-sm-8"><?php echo $ticket['reporter_name']; ?> (แผนก: <?php echo $ticket['dept_name']; ?>)</div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-4 text-muted fw-bold">หมวดหมู่:</div>
                <div class="col-sm-8"><span class="badge bg-secondary"><?php echo $ticket['category']; ?></span></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-4 text-muted fw-bold">หัวข้อปัญหา:</div>
                <div class="col-sm-8 text-danger fw-bold"><?php echo htmlspecialchars($ticket['title']); ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted fw-bold">รายละเอียด:</div>
                <div class="col-sm-8 bg-light p-3 rounded border"><?php echo nl2br(htmlspecialchars($ticket['problem_desc'])); ?></div>
            </div>
            
            <?php if(!empty($ticket['image_path'])): ?>
            <div class="row">
                <div class="col-sm-4 text-muted fw-bold">ภาพประกอบ:</div>
                <div class="col-sm-8 text-center">
                    <img src="../uploads/<?php echo $ticket['image_path']; ?>" class="img-fluid rounded border shadow-sm" style="max-height: 300px;">
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card card-custom mb-4 border-0 shadow-sm d-flex flex-column" style="height: 450px;">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots-fill text-primary me-2"></i> บันทึกการทำงาน & โต้ตอบ</h6>
            </div>
            
            <div class="card-body p-3 overflow-auto" style="background-color: #f8f9fa;">
                <?php 
                    $stmt = $conn->prepare("SELECT c.*, u.full_name FROM ticket_comments c JOIN users u ON c.user_id = u.user_id WHERE c.ticket_id = ? ORDER BY c.created_at ASC");
                    $stmt->execute([$ticket['ticket_id']]);
                    $comments = $stmt->fetchAll();
                    
                    if(empty($comments)) echo '<div class="text-center text-muted mt-5 small">ยังไม่มีการบันทึกข้อความ</div>';
                    
                    foreach($comments as $c): 
                        $is_me = ($c['user_id'] == $_SESSION['user_id']);
                        $bg_color = $is_me ? 'bg-primary text-white' : 'bg-white border';
                        $align = $is_me ? 'text-end' : 'text-start';
                ?>
                    <div class="mb-3 <?php echo $align; ?>">
                        <small class="text-muted" style="font-size: 0.75rem;"><?php echo $c['full_name']; ?> • <?php echo date('H:i', strtotime($c['created_at'])); ?></small>
                        <div class="d-inline-block p-2 px-3 rounded-3 mt-1 shadow-sm <?php echo $bg_color; ?>" style="max-width: 85%; text-align: left;">
                            <?php echo nl2br(htmlspecialchars($c['message'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-footer bg-white p-3 border-top">
                            <?php 
                            // เช็คสถานะ (รองรับทั้งคำว่า Resolved หรือ ปิดงาน)
                            if($ticket['status'] != 'Resolved' && $ticket['status'] != 'ปิดงาน'): 
                            ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                                    <div class="input-group shadow-sm">
                                        <input type="text" name="message" class="form-control border-primary" placeholder="พิมพ์ข้อความ..." required autocomplete="off">
                                        <button class="btn btn-primary px-3" type="submit"><i class="bi bi-send-fill"></i></button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-success mb-0 text-center py-2 shadow-sm">
                                    <i class="bi bi-check-circle-fill me-2"></i> งานซ่อมนี้เสร็จสิ้นแล้ว ไม่สามารถส่งข้อความได้
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($ticket['status'] != 'Resolved' && $ticket['status'] != 'ปิดงาน'): ?>
                    <div class="card card-custom border-0 shadow-sm border-start border-warning border-5">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-box-seam text-warning me-2"></i> บันทึกการเบิกอะไหล่สำหรับเคสนี้</h6>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="withdraw_asset">
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                    
                                <div class="row g-2">
                                    <div class="col-8">
                                        <select name="item_id" class="form-select form-select-sm" required>
                                            <option value="">-- เลือกอะไหล่ --</option>
                                            <?php
                                                $items = $conn->query("SELECT * FROM inventory WHERE stock_quantity > 0 ORDER BY item_name ASC")->fetchAll();
                                                foreach($items as $i) {
                                                    echo "<option value='{$i['item_id']}'>{$i['item_name']} (เหลือ {$i['stock_quantity']})</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" required>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold">
                                            <i class="bi bi-cart-plus-fill me-1"></i> ยืนยันการเบิกอะไหล่
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>  
        </div>          
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>