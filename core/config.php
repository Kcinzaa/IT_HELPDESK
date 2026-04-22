<?php
// ==========================================
// ⚙️ SYSTEM CONFIGURATION (ไฟล์ตั้งค่าระบบ)
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 1. ตั้งค่าโซนเวลา (สำคัญมากสำหรับระบบโรงพยาบาลที่ต้องบันทึกเวลาเป๊ะๆ)
date_default_timezone_set('Asia/Bangkok');

// 2. ตั้งค่า Database
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'rootpassword');
define('DB_NAME', 'helpdesk_db'); // ใช้ฐานข้อมูลใหม่ที่คุณ Nick สร้าง

// 3. ตั้งค่า URL พื้นฐาน (Base URL)
// ใช้สำหรับอ้างอิง path รูปภาพหรือไฟล์ CSS ให้ถูกต้องเสมอ
// ถ้าของเดิมเป็น http://localhost/helpdesk/
define('BASE_URL', 'http://localhost/');

// 4. ตั้งค่า LINE Notify Token (เอาไว้แจ้งเตือนช่าง IT)
define('LINE_TOKEN', 'ใส่_TOKEN_ของ_LINE_NOTIFY_ที่นี่');

// 5. เปิดโหมดแสดง Error (สำหรับโหมดนักพัฒนา)
// ถ้าเอาไปใช้จริง (Production) ให้เปลี่ยน 1 เป็น 0 เพื่อความปลอดภัย
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 6. กำหนด Path สำหรับอัปโหลดไฟล์/รูปภาพ
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
?>