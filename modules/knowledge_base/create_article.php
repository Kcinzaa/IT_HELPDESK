<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// อนุญาตเฉพาะช่าง IT และ Admin เท่านั้นที่เขียนคู่มือได้!
checkAuth(['it', 'admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->connect();
    
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $content = $_POST['content']; // โค้ด HTML ที่ส่งมาจาก CKEditor
    $author_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO knowledge_articles (title, category, content, author_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$title, $category, $content, $author_id])) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('เผยแพร่สำเร็จ!', 'คู่มือถูกเพิ่มเข้าสู่คลังความรู้แล้ว', 'success')
                    .then(() => { window.location.href = 'manage_articles.php'; });
                });
              </script>";
    }
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-journal-plus text-primary me-2"></i> เขียนบทความ / คู่มือใหม่</h3>
            <p class="text-muted">ระบบจัดการคลังความรู้ (Knowledge Base CMS)</p>
        </div>
        <a href="manage_articles.php" class="btn btn-secondary shadow-sm"><i class="bi bi-arrow-left me-2"></i>กลับหน้ารายการ</a>
    </div>

    <div class="card card-custom border-0 shadow-sm p-4">
        <form method="POST" action="">
            <div class="row mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-bold">หัวข้อบทความ <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg" placeholder="เช่น วิธีแก้ไขปริ้นเตอร์ไม่ออกเบื้องต้น" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">หมวดหมู่ <span class="text-danger">*</span></label>
                    <select name="category" class="form-select form-select-lg" required>
                        <option value="Hardware">ฮาร์ดแวร์ (Hardware)</option>
                        <option value="Software">ซอฟต์แวร์ (Software)</option>
                        <option value="Network">เครือข่าย (Network)</option>
                        <option value="Printer">เครื่องพิมพ์ (Printer)</option>
                        <option value="General">ทั่วไป (General)</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">เนื้อหาคู่มือ (พิมพ์ตกแต่ง หรือก๊อปวางรูปภาพได้เลย) <span class="text-danger">*</span></label>
                <textarea name="content" id="editor"></textarea>
            </div>

            <hr class="text-secondary mb-4">
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i> เผยแพร่บทความทันที
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo' ]
        })
        .catch(error => {
            console.error(error);
        });
</script>

<style>
/* ปรับความสูงของกล่องพิมพ์ข้อความให้พิมพ์ถนัดๆ */
.ck-editor__editable_inline {
    min-height: 400px;
}
</style>

<?php require_once '../../includes/footer.php'; ?>