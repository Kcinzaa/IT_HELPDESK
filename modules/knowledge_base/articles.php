<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// อนุญาตให้เข้าได้ทุกคน (staff, it, admin)
checkAuth(['staff', 'it', 'admin']);

$db = new Database();
$conn = $db->connect();

// ระบบค้นหาบทความ
$search = $_GET['search'] ?? '';
$sql = "SELECT k.*, u.full_name FROM knowledge_articles k 
        JOIN users u ON k.author_id = u.user_id ";

// แยกชื่อตัวแปรเป็น :search1 และ :search2 ป้องกัน Error
if ($search) {
    $sql .= "WHERE k.title LIKE :search1 OR k.category LIKE :search2 ";
}
$sql .= "ORDER BY k.created_at DESC";

$stmt = $conn->prepare($sql);

if ($search) {
    $stmt->execute([
        'search1' => "%$search%",
        'search2' => "%$search%"
    ]);
} else {
    $stmt->execute();
}
$articles = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i> คลังความรู้ (Knowledge Base)</h3>
            <p class="text-muted">คู่มือวิธีแก้ไขปัญหาเบื้องต้นด้วยตัวเอง</p>
        </div>
        <?php if($_SESSION['role'] != 'staff'): ?>
            <a href="create_article.php" class="btn btn-outline-primary fw-bold shadow-sm"><i class="bi bi-pencil-square me-2"></i>เขียนคู่มือใหม่</a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm mb-5 p-2 rounded-pill">
        <form method="GET" action="" class="d-flex m-0">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 text-muted ps-4"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-0 shadow-none fs-5 py-2" placeholder="ค้นหาวิธีแก้ปัญหา เช่น 'ปริ้นเตอร์', 'เข้าเน็ตไม่ได้'..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary rounded-pill px-4 fw-bold m-1" type="submit">ค้นหา</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <?php if(empty($articles)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">ไม่พบคู่มือที่คุณค้นหา</h5>
                <a href="articles.php" class="btn btn-sm btn-outline-secondary mt-2">ดูคู่มือทั้งหมด</a>
            </div>
        <?php else: ?>
            <?php foreach($articles as $a): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm border-top border-info border-4 hover-lift">
                    <div class="card-body p-4">
                        <span class="badge bg-info text-white mb-3 px-2 py-1"><?php echo htmlspecialchars($a['category']); ?></span>
                        <h5 class="card-title fw-bold text-dark mb-3"><?php echo htmlspecialchars($a['title']); ?></h5>
                        
                        <div class="card-text text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo strip_tags($a['content']); ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 p-4 pt-0 d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($a['full_name']); ?></small>
                        
                        <button class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#articleModal_<?php echo $a['article_id']; ?>">
                            อ่านต่อ
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="articleModal_<?php echo $a['article_id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        
                        <div class="modal-header bg-primary text-white border-0 p-4">
                            <h4 class="modal-title fw-bold mb-0"><i class="bi bi-book-half me-2"></i><?php echo htmlspecialchars($a['title']); ?></h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body p-0 bg-light">
                            <div class="bg-white px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                                <span class="badge bg-info text-white px-3 py-2 fs-6 shadow-sm"><?php echo htmlspecialchars($a['category']); ?></span>
                                <div class="text-end text-muted small">
                                    <div><i class="bi bi-person-fill me-1"></i> เขียนโดย: <span class="fw-bold"><?php echo htmlspecialchars($a['full_name']); ?></span></div>
                                    <div><i class="bi bi-calendar-event me-1"></i> อัปเดตเมื่อ: <?php echo date('d M Y, H:i', strtotime($a['created_at'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="p-4 p-md-5 bg-white article-content">
                                <?php echo $a['content']; ?> </div>
                        </div>
                        
                        <div class="modal-footer border-0 bg-light p-3 justify-content-center">
                            <button type="button" class="btn btn-secondary btn-lg fw-bold px-5 rounded-pill shadow-sm" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
/* แต่งเอฟเฟกต์เมาส์ชี้ */
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }

/* จัดการความสวยงามของเนื้อหาที่พิมพ์จาก CKEditor ให้พอดีกับ Modal */
.article-content img { max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin: 15px 0; }
.article-content table { width: 100% !important; border-collapse: collapse; margin-bottom: 1rem; }
.article-content table td, .article-content table th { border: 1px solid #dee2e6; padding: 0.75rem; }
.article-content h2, .article-content h3 { color: #0d6efd; font-weight: bold; margin-top: 1.5rem; }
</style>

<?php require_once '../../includes/footer.php'; ?>