<?php
require_once 'core/config.php';
require_once 'core/Notification.php';
session_start();

// 🕵️ เก็บ Log ว่ามีการกดออกจากระบบ (ถ้าเขาล็อกอินอยู่)
if (isset($_SESSION['full_name'])) {
    Notification::logActivity($_SESSION['full_name'], "ออกจากระบบ");
}

// ล้างค่าตัวแปร Session ทั้งหมด
$_SESSION = array();

// ทำลาย Session ทิ้ง
session_destroy();

// เด้งกลับไปหน้า Login และส่งข้อความไปบอกว่าออกสำเร็จแล้ว
header("Location: login.php");
exit();
?>