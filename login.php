<?php
require_once 'core/config.php';
require_once 'core/database.php';
require_once 'core/User.php';
require_once 'core/Notification.php';

session_start();

// ถ้าล็อกอินค้างไว้อยู่แล้ว ให้วิ่งไปที่หน้า index.php เลย ไม่ต้องล็อกอินซ้ำ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// เมื่อมีการกดปุ่ม "เข้าสู่ระบบ"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->connect();
    $userObj = new User($conn);

    $username = $_POST['username'];
    $password = $_POST['password'];

    // ส่งรหัสไปเช็คในฐานข้อมูล
    $loggedInUser = $userObj->login($username, $password);

    if ($loggedInUser) {
        // หากถูกต้อง ให้สร้างบัตรประจำตัว (Session)
        $_SESSION['user_id'] = $loggedInUser['user_id'];
        $_SESSION['username'] = $loggedInUser['username'];
        $_SESSION['full_name'] = $loggedInUser['full_name'];
        $_SESSION['role'] = $loggedInUser['role'];
        $_SESSION['dept_id'] = $loggedInUser['dept_id'];

        // 🕵️ เก็บ Log ว่าคนนี้เข้าสู่ระบบ
        Notification::logActivity($loggedInUser['full_name'], "เข้าสู่ระบบสำเร็จ");

        // ส่งไปให้ index.php จัดการแยกทางให้
        header("Location: index.php");
        exit();
    } else {
        $error = "รหัสพนักงาน หรือ รหัสผ่านไม่ถูกต้อง!";
        // 🕵️ เก็บ Log แจ้งเตือนคนพยายามแฮกหรือพิมพ์รหัสผิด
        Notification::logActivity($username, "พยายามเข้าสู่ระบบแต่รหัสผ่านผิด");
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Enterprise IT Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f0f2f5; 
            background-image: radial-gradient(circle at 50% 0%, #ffffff 0%, #f0f2f5 70%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .login-header {
            background-color: #0d6efd;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card">
                <div class="login-header">
                    <i class="bi bi-hospital fs-1 mb-2 d-block"></i>
                    <h4 class="fw-bold mb-0">ระบบแจ้งซ่อมศูนย์คอมพิวเตอร์</h4>
                    <small class="text-white-50">โรงพยาบาลหาดใหญ่</small>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger text-center py-2 shadow-sm">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">รหัสพนักงาน</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" class="form-control form-control-lg bg-light" required placeholder="เช่น nurse01 หรือ it_nick">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">รหัสผ่าน</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control form-control-lg bg-light" required placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                            เข้าสู่ระบบ <i class="bi bi-box-arrow-in-right ms-2"></i>
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <small class="text-muted">&copy; 2026 IT Helpdesk System. All rights reserved.</small>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>