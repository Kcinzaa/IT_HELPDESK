<?php
// ประกาศว่าไฟล์นี้ส่งข้อมูลกลับเป็น JSON
header('Content-Type: application/json; charset=utf-8');
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';

session_start();

// เช็คความปลอดภัย (ถ้าไม่ได้ Login ให้เตะออก)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();
    $ticket = new Ticket($conn);

    // เรียกฟังก์ชันดึงสถิติจากคลาส Ticket
    $stats = $ticket->getStats();

    // ดึงข้อมูลเสริม: แผนกไหนแจ้งซ่อมเยอะสุด 3 อันดับแรก (เผื่อเอาไปทำกราฟ)
    $stmt = $conn->query("SELECT d.dept_name, COUNT(t.ticket_id) as total 
                          FROM tickets t 
                          JOIN users u ON t.user_id = u.user_id 
                          JOIN departments d ON u.dept_id = d.dept_id 
                          GROUP BY d.dept_id ORDER BY total DESC LIMIT 3");
    $top_departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ส่งข้อมูลกลับไปให้ JavaScript
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'pending' => $stats['pending'] ?? 0,
            'in_progress' => $stats['in_progress'] ?? 0,
            'resolved' => $stats['resolved'] ?? 0,
            'top_departments' => $top_departments
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>