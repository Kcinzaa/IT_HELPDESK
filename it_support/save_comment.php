<?php
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';
require_once '../core/Notification.php';

// เปิดใช้งาน Session
session_start();

// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST และผู้ใช้ล็อกอินอยู่หรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $db = new Database();
    $conn = $db->connect();
    $ticketObj = new Ticket($conn);

    // รับค่าจากฟอร์ม
    $ticket_id = $_POST['ticket_id'];
    $status = $_POST['status'];
    $message = trim($_POST['message']); // trim ช่วยตัดช่องว่างซ้ายขวา ป้องกันคนพิมพ์เคาะ Spacebar ส่งมารัวๆ
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['full_name'];

    // ป้องกันการส่งข้อความเปล่า
    if (empty($message)) {
        echo "<script>alert('กรุณาพิมพ์ข้อความก่อนกดส่ง'); window.history.back();</script>";
        exit();
    }

    try {
        // 🔒 เริ่มต้น Transaction (เพื่อความปลอดภัยของข้อมูลสูงสุด)
        $conn->beginTransaction();

        // 1. บันทึกข้อความแชทลงตาราง ticket_comments
        $sql_comment = "INSERT INTO ticket_comments (ticket_id, user_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_comment);
        $stmt->execute([$ticket_id, $user_id, $message]);

        // 2. อัปเดตสถานะของ Ticket 
        // ถ้าสถานะเป็น 'Resolved' (ปิดงาน) เราจะเนียนเอาข้อความแชทนี้ ไปบันทึกเป็น 'วิธีแก้ปัญหา (Resolution Notes)' ด้วยเลย
        $resolution_notes = ($status === 'Resolved') ? $message : null;
        
        // เรียกใช้ฟังก์ชันอัปเดตสถานะจากคลาส Ticket
        $ticketObj->updateStatus($ticket_id, $status, $user_id, $resolution_notes);

        // ✅ ยืนยันการบันทึกข้อมูลทั้ง 2 ส่วน (Commit)
        $conn->commit();

        // 🕵️ เก็บบันทึกประวัติการทำงาน (Audit Log)
        Notification::logActivity($user_name, "ตอบกลับและอัปเดตสถานะ [{$status}] ใน Ticket #TK-{$ticket_id}");

        // กลับไปหน้าดูรายละเอียดงานทันที (เพื่อให้เห็นข้อความที่เพิ่งพิมพ์ไป)
        header("Location: ticket_detail.php?id=" . $ticket_id);
        exit();

    } catch (Exception $e) {
        // ❌ หากมี Database Error (เช่น เน็ตหลุด ดาต้าเบสล่ม) ให้ยกเลิกการบันทึกทั้งหมด
        $conn->rollBack();
        
        // เก็บ Error Log ไว้ให้ IT ดูเบื้องหลัง
        error_log("Comment Save Error: " . $e->getMessage(), 3, __DIR__ . '/../logs/error_log.txt');
        
        echo "<script>
                alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง'); 
                window.history.back();
              </script>";
        exit();
    }

} else {
    // ถ้ามีคนแอบพิมพ์ URL เข้ามาหน้านี้ตรงๆ ให้เตะกลับไปหน้าหลัก
    header("Location: ../index.php");
    exit();
}
?>