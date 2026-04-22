<?php
// ดึงไฟล์เชื่อมต่อฐานข้อมูล
require_once '../core/config.php'; 
require_once '../core/connect.php'; 

// ตั้งค่าให้ส่งข้อมูลกลับเป็น JSON
header('Content-Type: application/json');

try {
    // สมมติว่าดึงงานที่ยังไม่ได้รอดำเนินการ (status = 'pending') ล่าสุด 10 รายการ
    $stmt = $conn->prepare("SELECT id, title, department, created_at FROM tickets WHERE status = 'pending' ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ส่งข้อมูลกลับไปให้ JavaScript
    echo json_encode([
        'status' => 'success',
        'data' => $tickets
    ]);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>