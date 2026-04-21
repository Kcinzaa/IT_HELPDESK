<?php
// เปิด Session เสมอถ้ายังไม่ได้เปิด
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🚦 ฟังก์ชันตรวจสอบการล็อกอินและสิทธิ์
function checkAuth($allowed_roles = []) {
    // 1. ถ้าไม่มี Session (ยังไม่ได้ล็อกอิน) ให้เตะไปหน้า Login
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }

    // 2. ถ้ามีการจำกัดสิทธิ์ (เช่น หน้านี้เข้าได้แค่ it)
    if (!empty($allowed_roles)) {
        // ถ้า Role ของคนล็อกอิน ไม่อยู่ในรายการที่อนุญาต
        if (!in_array($_SESSION['role'], $allowed_roles)) {
            // เตะกลับไปหน้าหลักของตัวเอง
            $redirect = ($_SESSION['role'] == 'staff') ? 'modules/tickets/view.php' : 'it_support/dashboard.php';
            header("Location: " . BASE_URL . $redirect . "?error=unauthorized");
            exit();
        }
    }
}
?>