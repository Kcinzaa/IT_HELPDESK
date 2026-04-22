<?php
// ไฟล์ core/connect.php
$host = "db";             // ชื่อ Service ใน docker-compose
$dbname = "helpdesk_db";  // ชื่อฐานข้อมูลที่คุณ Nick เหลือไว้
$username = "root";
$password = "rootpassword";

try {
    // เชื่อมต่อแบบ PDO (ที่ใช้ในโปรเจกต์คุณ Nick)
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // ตั้งค่าให้แจ้งเตือน Error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    echo "การเชื่อมต่อขัดข้อง: " . $e->getMessage();
    exit();
}
?>