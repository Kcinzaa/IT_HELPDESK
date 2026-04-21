<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

session_start();
checkAuth(['it', 'admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->connect();

    $ticket_id = $_POST['ticket_id'];
    $item_id = $_POST['item_id'];
    $qty = (int)$_POST['quantity'];
    $admin_id = $_SESSION['user_id'];

    // 1. ตรวจสอบสต็อกปัจจุบัน
    $stmt = $conn->prepare("SELECT stock_quantity, item_name FROM inventory WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if ($item && $item['stock_quantity'] >= $qty) {
        // 2. หักสต็อก
        $update = $conn->prepare("UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE item_id = ?");
        $update->execute([$qty, $item_id]);

        // 3. บันทึกประวัติการเบิก (ผูกกับ Ticket ID)
        $insert = $conn->prepare("INSERT INTO inventory_withdrawals (item_id, ticket_id, admin_id, quantity) VALUES (?, ?, ?, ?)");
        $insert->execute([$item_id, $ticket_id, $admin_id, $qty]);

        // 4. บันทึกคอมเมนต์อัตโนมัติในหน้าแชท เพื่อแจ้งว่ามีการใช้อะไหล่
        $log_msg = "🛠️ บันทึกการใช้อะไหล่: " . $item['item_name'] . " จำนวน " . $qty . " หน่วย";
        $stmtLog = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmtLog->execute([$ticket_id, $admin_id, $log_msg]);

        header("Location: ticket_detail.php?id=$ticket_id&status=withdraw_success");
    } else {
        header("Location: ticket_detail.php?id=$ticket_id&status=stock_error");
    }
    exit();
}