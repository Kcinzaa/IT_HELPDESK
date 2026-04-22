<?php
try {
    $conn = new PDO("mysql:host=db;dbname=helpdesk_db", "root", "rootpassword");
    echo "<h1 style='color:green;'>✅ เชื่อมต่อฐานข้อมูล Docker สำเร็จแล้วคุณ Nick!</h1>";
} catch (PDOException $e) {
    echo "<h1 style='color:red;'>❌ เชื่อมต่อไม่ได้: " . $e->getMessage() . "</h1>";
}
?>