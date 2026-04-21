<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->connect();
    
    $ticket_id = $_POST['ticket_id'];
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];
    
    // อัปเดตคะแนนดาวลงฐานข้อมูล
    $sql = "UPDATE tickets SET rating = ?, feedback = ? WHERE ticket_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if($stmt->execute([$rating, $feedback, $ticket_id, $_SESSION['user_id']])) {
        echo "<script>alert('ขอบคุณสำหรับการประเมินครับ!'); window.location.href='view.php';</script>";
    }
}
?>