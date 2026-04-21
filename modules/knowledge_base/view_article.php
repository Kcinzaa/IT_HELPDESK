<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// อนุญาตเฉพาะ IT และ Admin (หรือถ้าอยากให้พยาบาลอ่านได้ด้วย ก็ปรับ role ตรงนี้ครับ)
checkAuth(['it', 'admin']);

if (!isset($_GET['id'])) {
    header("Location: index.php"); // ถ้าไม่มีรหัสเด้งกลับหน้าตาราง
    exit();
}

$db = new Database();
$conn = $db->connect();
$article_id = $_GET['id'];

// ดึงข้อมูลคู่มือ พร้อมชื่อคนเขียน
$stmt = $conn->prepare("
    SELECT k.*, u.full_name as author_name 
    FROM knowledge_articles k 
    LEFT JOIN users u ON k.author_id = u.user_id 
    WHERE k.article_id = ?
");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    die("❌ ไม่พบข้อมูลคู่มือนี้");
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="javascript:history.back()" class="btn btn-outline-secondary shadow-sm fw-bold">
            <i class="bi bi-arrow-left me-2"></i> กลับไปหน้ารายการคู่มือ
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4 p-md-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill fs-6 me-3">
                            <i class="bi bi-tag-fill me-1"></i> <?php echo htmlspecialchars($article['category']); ?>
                        </span>
                        <span class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> เผยแพร่เมื่อ: <?php echo date('d M Y, H:i', strtotime($article['created_at'])); ?>
                        </span>
                    </div>
                    <h2 class="fw-bold text-dark lh-base mb-3"><?php echo htmlspecialchars($article['title']); ?></h2>
                    <div class="d-flex align-items-center text-muted">
                        <i class="bi bi-person-circle fs-4 me-2 text-secondary"></i>
                        <span>เขียนโดย: <strong><?php echo htmlspecialchars($article['author_name'] ?? 'ผู้ดูแลระบบ'); ?></strong></span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5" style="font-size: 1.1rem; line-height: 1.8;">
                    <?php 
                        // พิมพ์เนื้อหาออกมาตรงๆ เลย เพราะเราเก็บเป็น HTML ไว้ใน DB
                        echo $article['content']; 
                    ?>
                </div>
                
                <div class="card-footer bg-light p-4 text-center text-muted border-top-0">
                    <small><i class="bi bi-info-circle me-1"></i> คู่มือนี้จัดทำขึ้นเพื่อใช้ภายในแผนก IT Support โรงพยาบาล</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ปรับแต่งเนื้อหาที่มาจาก CKEditor ให้สวยงามขึ้น */
.card-body img { max-width: 100%; height: auto; border-radius: 8px; margin: 15px 0; }
.card-body h2, .card-body h3 { margin-top: 25px; margin-bottom: 15px; color: #0d6efd; font-weight: bold; }
.card-body ul, .card-body ol { margin-bottom: 20px; }
.card-body li { margin-bottom: 8px; }
.card-body p { margin-bottom: 15px; }
</style>

<?php require_once '../../includes/footer.php'; ?>