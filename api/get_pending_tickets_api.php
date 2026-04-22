<?php
// ไฟล์ get_pending_tickets_api.php
require_once '../core/config.php';
require_once '../core/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    
    // ดึงเฉพาะงานที่รอดำเนินการ (ปรับคำสั่ง SQL ให้ตรงกับโครงสร้างตารางจริงของคุณ Nick นะครับ)
    $stmt = $conn->prepare("SELECT ticket_id, title, category, building as dept_name FROM tickets WHERE status = 'Pending' ORDER BY ticket_id DESC");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}