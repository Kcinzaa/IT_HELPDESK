<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../core/config.php';
require_once '../core/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ticket ID is required']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    // ดึงข้อมูล 1 แถวแบบ Join เพื่อเอาชื่อคนแจ้งมาด้วย
    $query = "SELECT t.*, u.full_name as reporter_name, d.dept_name 
              FROM tickets t 
              JOIN users u ON t.user_id = u.user_id 
              JOIN departments d ON u.dept_id = d.dept_id 
              WHERE t.ticket_id = ? LIMIT 1";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['id']]);
    $ticketData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ticketData) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $ticketData]);
    } else {
        http_response_code(404); // 404 Not Found
        echo json_encode(['status' => 'error', 'message' => 'Ticket not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
?>