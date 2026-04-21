<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../core/Ticket.php';
require_once '../../includes/auth.php';

// 🛡️ ป้องกันความปลอดภัย: อนุญาตให้เฉพาะทีม IT และ Admin โหลดข้อมูล
checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();
$ticketObj = new Ticket($conn);

// 🔍 ดึงข้อมูลการแจ้งซ่อมทั้งหมด (อ้างอิงจากคลาส Ticket)
$tickets = $ticketObj->getAllTickets();

// 🏷️ ตั้งค่าชื่อไฟล์ที่จะดาวน์โหลด (ใส่วันที่และเวลาให้ดูโปร)
$filename = "Helpdesk_Report_" . date('Y-m-d_H-i') . ".csv";

// ⚙️ กำหนด Header ให้เบราว์เซอร์รู้ว่าต้องดาวน์โหลดไฟล์นี้
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// เปิดช่องทางการเขียนไฟล์ออกไปที่หน้าจอ (Output Stream)
$output = fopen('php://output', 'w');

// ⭐ สำคัญมาก: เติม BOM ลงไปที่ต้นไฟล์ เพื่อบังคับให้ MS Excel อ่านเป็น UTF-8 (ภาษาไทยไม่เพี้ยน)
fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

// 📝 เขียนหัวตาราง (Header Row)
fputcsv($output, [
    'รหัส Ticket', 
    'เวลาแจ้ง', 
    'แผนกที่แจ้ง', 
    'ชื่อผู้แจ้ง', 
    'หมวดหมู่', 
    'ปัญหาที่พบ', 
    'ความเร่งด่วน', 
    'สถานะปัจจุบัน', 
    'ช่างผู้รับผิดชอบ', 
    'วิธีแก้ไข (Resolution Notes)', 
    'เวลาปิดงาน'
]);

// 🔄 วนลูปข้อมูลจากฐานข้อมูลมาใส่ทีละแถว
if (!empty($tickets)) {
    foreach ($tickets as $row) {
        fputcsv($output, [
            'TK-' . $row['ticket_id'],
            $row['created_at'],
            $row['dept_name'],
            $row['reporter_name'],
            $row['category'],
            $row['title'],
            $row['urgency'],
            $row['status'],
            $row['it_name'] ?? 'ยังไม่มีผู้รับงาน',
            $row['resolution_notes'] ?? '-',
            $row['resolved_at'] ?? '-'
        ]);
    }
} else {
    // ถ้าไม่มีข้อมูลเลย ให้โชว์แจ้งเตือนในบรรทัดแรก
    fputcsv($output, ['ไม่มีข้อมูลการแจ้งซ่อมในระบบ']);
}

// ปิดการเขียนไฟล์
fclose($output);
exit();
?>