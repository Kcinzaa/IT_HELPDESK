<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

checkAuth(['staff']);

$db = new Database();
$conn = $db->connect();

// ดึงสถิติของ User คนนี้
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM tickets WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_stats = $stmt->fetch();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-custom border-0 shadow-sm text-center p-5 mb-4">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&size=128&background=random" class="rounded-circle mx-auto mb-3 shadow" width="120">
                <h4 class="fw-bold"><?php echo $_SESSION['full_name']; ?></h4>
                <p class="text-muted"><?php echo $_SESSION['username']; ?> | แผนก IT รพ.</p>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <h5 class="fw-bold text-primary"><?php echo $user_stats['total']; ?></h5>
                        <small class="text-muted">ส่งแจ้งซ่อม</small>
                    </div>
                    <div class="col-6">
                        <h5 class="fw-bold text-success"><?php echo $user_stats['resolved']; ?></h5>
                        <small class="text-muted">ซ่อมเสร็จแล้ว</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-custom border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-4">กิจกรรมการแจ้งซ่อมล่าสุด</h5>
                <div class="timeline-simple">
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute([$_SESSION['user_id']]);
                    $activities = $stmt->fetchAll();

                    foreach($activities as $act) {
                        echo "
                        <div class='border-start border-3 border-primary ps-3 mb-4'>
                            <div class='fw-bold'>#TK-{$act['ticket_id']} - {$act['title']}</div>
                            <small class='text-muted'>".date('d M Y H:i', strtotime($act['created_at']))."</small>
                            <div class='mt-1'><span class='badge bg-light text-dark border'>{$act['status']}</span></div>
                        </div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>