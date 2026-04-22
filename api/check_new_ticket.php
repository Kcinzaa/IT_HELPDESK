<?php
// ไฟล์: api/check_new_ticket.php
require_once '../core/config.php';
require_once '../core/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->connect();

// ดึงข้อมูลงานซ่อม "ล่าสุด" แค่ 1 งาน
$stmt = $conn->query("SELECT ticket_id, title, reporter_name, dept_name FROM tickets ORDER BY ticket_id DESC LIMIT 1");
$latest_ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if ($latest_ticket) {
    echo json_encode([
        'status' => 'success',
        'max_id' => (int)$latest_ticket['ticket_id'],
        'title' => $latest_ticket['title'],
        'dept' => $latest_ticket['dept_name']
    ]);
} else {
    echo json_encode(['status' => 'empty', 'max_id' => 0]);
}
?>