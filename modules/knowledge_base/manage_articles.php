<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();

// ระบบลบบทความ
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM knowledge_articles WHERE article_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_articles.php");
    exit();
}

// ดึงข้อมูลบทความพร้อมชื่อผู้เขียน
$stmt = $conn->query("SELECT k.*, u.full_name FROM knowledge_articles k JOIN users u ON k.author_id = u.user_id ORDER BY k.created_at DESC");
$articles = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-journal-text text-primary me-2"></i> จัดการคลังความรู้</h3>
            <p class="text-muted">จัดการคู่มือและบทความแก้ไขปัญหาสำหรับเจ้าหน้าที่</p>
        </div>
        <a href="create_article.php" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-pencil-square me-2"></i> เขียนบทความใหม่
        </a>
    </div>

    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="kbTable">
                <thead class="table-dark">
                    <tr>
                        <th class="border-0 rounded-start">ID</th>
                        <th class="border-0">หัวข้อบทความ</th>
                        <th class="border-0 text-center">หมวดหมู่</th>
                        <th class="border-0">ผู้เขียน</th>
                        <th class="border-0">วันที่เผยแพร่</th>
                        <th class="border-0 text-center rounded-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($articles as $a): ?>
                    <tr>
                        <td class="text-muted">#<?php echo $a['article_id']; ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($a['title']); ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                <?php echo htmlspecialchars($a['category']); ?>
                            </span>
                        </td>
                        <td>
                            <small class="fw-bold"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($a['full_name']); ?></small>
                        </td>
                        <td class="text-muted small">
                            <?php echo date('d M Y', strtotime($a['created_at'])); ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="view_article.php?id=<?php echo $a['article_id']; ?>" class="btn btn-sm btn-outline-primary" title="อ่านคู่มือ">
                                    <i class="bi bi-eye-fill"></i> อ่าน
                                </a>
                                
                                <button type="button" onclick="confirmDelete(<?php echo $a['article_id']; ?>)" class="btn btn-sm btn-outline-danger" title="ลบบทความ">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 💥 จัดการปัญหา DataTables Re-initialization
    if ($.fn.DataTable.isDataTable('#kbTable')) {
        $('#kbTable').DataTable().destroy();
    }

    $('#kbTable').DataTable({
        order: [[0, 'desc']],
        language: {
            "sSearch": "🔍 ค้นหาบทความ:",
            "sLengthMenu": "แสดง _MENU_ แถว",
            "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ บทความ",
            "oPaginate": {
                "sPrevious": "ก่อนหน้า",
                "sNext": "ถัดไป"
            }
        }
    });
});

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "เมื่อลบแล้ว บทความนี้จะหายไปจากระบบคลังความรู้ทันที!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'manage_articles.php?delete=' + id;
        }
    })
}
</script>

<?php require_once '../../includes/footer.php'; ?>