<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';

session_start();

// อนุญาตเฉพาะฝั่ง IT เท่านั้น
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'it' && $_SESSION['role'] !== 'admin')) {
    http_response_code(403); // 403 Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    exit();
}

// รับข้อมูล JSON ที่ส่งมาจาก JavaScript
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['ticket_id']) || !isset($data['status'])) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();
    $ticket = new Ticket($conn);

    $ticket_id = (int)$data['ticket_id'];
    $new_status = $data['status'];
    $it_id = $_SESSION['user_id'];

    // ทำการอัปเดตสถานะ
    if ($ticket->updateStatus($ticket_id, $new_status, $it_id)) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success', 
            'message' => 'Ticket updated successfully',
            'ticket_id' => $ticket_id,
            'new_status' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update ticket']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
?>