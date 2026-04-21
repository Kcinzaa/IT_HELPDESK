<?php
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';
session_start();

// อนุญาตเฉพาะ IT/Admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'it' && $_SESSION['role'] !== 'admin')) {
    die("Unauthorized access");
}

$db = new Database();
$conn = $db->connect();
$ticket = new Ticket($conn);
$data = $ticket->getAllTickets(); // ดึงข้อมูลทั้งหมด

// ตั้งค่า Header ให้ดาวน์โหลดเป็นไฟล์ CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Helpdesk_Report_'.date('Y-m-d').'.csv');

// สร้าง Output Stream
$output = fopen('php://output', 'w');

// ใส่ BOM (Byte Order Mark) เพื่อให้ MS Excel อ่านภาษาไทย (UTF-8) ได้ถูกต้อง 100%
fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

// เขียนหัวตาราง (Headers)
fputcsv($output, ['Ticket ID', 'วันที่แจ้ง', 'แผนก', 'ผู้แจ้ง', 'หมวดหมู่', 'หัวข้อปัญหา', 'ความด่วน', 'สถานะ', 'ช่างผู้รับผิดชอบ', 'บันทึกการแก้ไข']);

// วนลูปเขียนข้อมูลทีละบรรทัด
foreach ($data as $row) {
    fputcsv($output, [
        'TK-'.$row['ticket_id'],
        $row['created_at'],
        $row['dept_name'],
        $row['reporter_name'],
        $row['category'],
        $row['title'],
        $row['urgency'],
        $row['status'],
        $row['it_name'] ?? 'ยังไม่มีผู้รับงาน',
        $row['resolution_notes'] ?? ''
    ]);
}
fclose($output);
exit();
?>