<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../core/Ticket.php';
require_once '../../core/Notification.php';
require_once '../../includes/auth.php';

// อนุญาตให้ทุกคนแจ้งซ่อมได้
checkAuth(['staff', 'it', 'admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->connect();
    $ticket = new Ticket($conn);

    $title = $_POST['title'];
    $category = $_POST['category'];
    $desc = $_POST['problem_desc'];
    $urgency = $_POST['urgency'];
    $user_id = $_SESSION['user_id'];
    
    // รับค่าพิกัดสถานที่ใหม่
    $building = $_POST['building'];
    $floor = $_POST['floor'];
    $room_no = $_POST['room_no'];
    
    // 📁 ระบบจัดการอัปโหลดรูปภาพ
    $image_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_name = $_FILES['attachment']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_ext)) {
            $new_name = 'TK_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
            $destination = UPLOAD_DIR . $new_name;
            if (move_uploaded_file($file_tmp, $destination)) {
                $image_path = $new_name;
            }
        }
    }

    // บันทึกลงฐานข้อมูล (อย่าลืมเพิ่มคอลัมน์ building, floor, room_no ในตาราง tickets นะครับ)
    $sql = "INSERT INTO tickets (user_id, title, category, problem_desc, building, floor, room_no, urgency, image_path, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$user_id, $title, $category, $desc, $building, $floor, $room_no, $urgency, $image_path])) {
        Notification::sendLine("🚨 แจ้งซ่อมใหม่!\nจาก: {$_SESSION['full_name']}\nตึก: {$building} ชั้น: {$floor}\nอาการ: {$title}");
        
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งเรื่องแจ้งซ่อมสำเร็จ!',
                        confirmButtonColor: '#0d6efd'
                    }).then((result) => {
                        window.location.href = '../../index.php';
                    });
                });
              </script>";
    }
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> สร้างใบแจ้งซ่อมใหม่</h3>
        <a href="../../index.php" class="btn btn-secondary text-white shadow-sm border-0 px-3">
            <i class="bi bi-house-door me-1"></i> กลับหน้าหลัก
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-custom p-4 shadow-sm border-top border-primary border-4">
                <form method="POST" action="" enctype="multipart/form-data">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">หัวข้อปัญหา <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="issueTitle" class="form-control form-control-lg" required placeholder="เช่น เปิดคอมไม่ติด, ปริ้นเตอร์กระดาษติด">
                        
                        <div id="smartSuggestBox" class="mt-2 d-none">
                            <div class="alert alert-info border-0 shadow-sm py-2 mb-0">
                                <small class="fw-bold"><i class="bi bi-lightbulb text-warning me-1"></i> วิธีแก้ไขเบื้องต้น:</small>
                                <ul id="suggestList" class="mb-0 mt-1 ps-3 small text-muted"></ul>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-12"><small class="text-primary fw-bold text-uppercase">ระบุสถานที่ติดตั้งอุปกรณ์</small></div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">อาคาร / ตึก <span class="text-danger">*</span></label>
                            <select name="building" class="form-select" required>
                                <option value="">เลือกอาคาร...</option>
                                <option value="อาคารผู้ป่วยนอก (OPD)">อาคารผู้ป่วยนอก (OPD)</option>
                                <option value="อาคารอุบัติเหตุและฉุกเฉิน 10 ชั้น (ER)">อาคารอุบัติเหตุและฉุกเฉิน 10 ชั้น (ER)</option>
                                <option value="อาคารเฉลิมพระเกียรติ">อาคารเฉลิมพระเกียรติ</option>
                                <option value="อาคารบริการและสนับสนุนทางการแพทย์">อาคารบริการและสนับสนุนทางการแพทย์</option>
                                <option value="อาคารศูนย์แพทยศาสตร์ศึกษาชั้นคลินิก">อาคารศูนย์แพทยศาสตร์ศึกษาชั้นคลินิก</option>
                                <option value="อาคารผู้ป่วยหนักและผู้ป่วยใน">อาคารผู้ป่วยหนักและผู้ป่วยใน</option>
                                <option value="อาคาร 50 ปี">อาคาร 50 ปี</option>
                                <option value="อาคารรังสีรักษาและเคมีบำบัด">อาคารรังสีรักษาและเคมีบำบัด</option>
                                <option value="ตึกอำนวยการ">ตึกอำนวยการ</option>
                                <option value="บ้านพักเจ้าหน้าที่">บ้านพักเจ้าหน้าที่</option>
                                <option value="อื่นๆ">อื่นๆ (ระบุในช่องรายละเอียด)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">ชั้น <span class="text-danger">*</span></label>
                            <select name="floor" class="form-select" required>
                                <option value="">เลือกชั้น...</option>
                                <option value="G">ชั้น G</option>
                                <option value="1">ชั้น 1</option>
                                <option value="2">ชั้น 2</option>
                                <option value="3">ชั้น 3</option>
                                <option value="4">ชั้น 4</option>
                                <option value="5">ชั้น 5</option>
                                <option value="6">ชั้น 6</option>
                                <option value="7">ชั้น 7</option>
                                <option value="8">ชั้น 8</option>
                                <option value="9">ชั้น 9</option>
                                <option value="10">ชั้น 10</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">ห้อง / จุดติดตั้ง <span class="text-danger">*</span></label>
                            <input type="text" name="room_no" class="form-control" placeholder="เช่น ห้องตรวจ 1, เคาน์เตอร์พยาบาล" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">หมวดหมู่</label>
                            <select name="category" class="form-select">
                                <option value="Hardware">Hardware (คอมพิวเตอร์, จอ)</option>
                                <option value="Printer">Printer (เครื่องพิมพ์)</option>
                                <option value="Network">Network (Internet, Wi-Fi)</option>
                                <option value="Software">Software (Windows, HIS)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ความเร่งด่วน</label>
                            <select name="urgency" class="form-select">
                                <option value="Normal">ปกติ (รอได้)</option>
                                <option value="High">ด่วน (มีผลกระทบต่อแผนก)</option>
                                <option value="Critical">ด่วนที่สุด (หยุดทำงานทันที)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">รายละเอียดอาการที่พบ</label>
                        <textarea name="problem_desc" class="form-control" rows="3" placeholder="อธิบายรายละเอียดเพิ่มเติม..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">แนบรูปภาพประกอบ</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                    <hr class="text-secondary mb-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i> ส่งเรื่องแจ้งซ่อมทันที
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Smart Deflection Script
document.getElementById('issueTitle').addEventListener('input', async function() {
    let query = this.value;
    let suggestBox = document.getElementById('smartSuggestBox');
    let suggestList = document.getElementById('suggestList');
    
    if(query.length > 2) {
        try {
            let res = await fetch(`../../api/suggest_kb.php?q=${encodeURIComponent(query)}`);
            let data = await res.json();
            if(data.status === 'success' && data.data.length > 0) {
                suggestList.innerHTML = '';
                data.data.forEach(item => {
                    suggestList.innerHTML += `<li><a href="../knowledge_base/articles.php?search=${encodeURIComponent(item.title)}" target="_blank" class="text-decoration-none fw-bold">${item.title}</a></li>`;
                });
                suggestBox.classList.remove('d-none');
            } else { suggestBox.classList.add('d-none'); }
        } catch(e) {}
    } else { suggestBox.classList.add('d-none'); }
});
</script>

<?php require_once '../../includes/footer.php'; ?>