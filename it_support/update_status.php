<?php
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';
require_once '../core/Notification.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->connect();
    $ticket = new Ticket($conn);

    $ticket_id = $_POST['ticket_id'];
    $status = $_POST['status'];
    $notes = $_POST['resolution_notes'];
    $it_id = $_SESSION['user_id'];
    $it_name = $_SESSION['full_name'];

    // อัปเดตลงฐานข้อมูล
    if ($ticket->updateStatus($ticket_id, $status, $it_id, $notes)) {
        
        // 🕵️ เก็บประวัติ Log ว่าใครเป็นคนเปลี่ยนสถานะ
        Notification::logActivity($it_name, "เปลี่ยนสถานะ Ticket #TK-{$ticket_id} เป็น [{$status}]");
        
        // ถ้าอยากให้เด้งเข้า LINE เวลาซ่อมเสร็จ ให้เอา // ออกครับ
        // if($status == 'Resolved') {
        //     Notification::sendLine("✅ ปิดงานซ่อม #TK-{$ticket_id} เรียบร้อยแล้ว โดย {$it_name}");
        // }

        echo "<script>
                alert('อัปเดตสถานะงานสำเร็จ!');
                window.location.href = 'ticket_detail.php?id={$ticket_id}';
              </script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึก'); window.history.back();</script>";
    }
}
?>