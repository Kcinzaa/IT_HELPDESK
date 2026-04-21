<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;

    // ฟังก์ชันเชื่อมต่อฐานข้อมูล
    public function connect() {
        $this->conn = null;

        try {
            // สร้าง DSN (Data Source Name)
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            
            // ตั้งค่า PDO Options เพื่อประสิทธิภาพสูงสุด
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // แจ้ง Error เป็น Exception
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // ดึงข้อมูลเป็น Array แบบมีชื่อ Column
                PDO::ATTR_EMULATE_PREPARES   => false,                  // ป้องกัน SQL Injection ขั้นสุด
            ];

            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);

        } catch(PDOException $e) {
            // บันทึก Error ลงไฟล์ (ไม่แสดงหน้าเว็บเพื่อความปลอดภัย)
            error_log("Database Connection Error: " . $e->getMessage(), 3, __DIR__ . '/../logs/error_log.txt');
            die("❌ ระบบฐานข้อมูลขัดข้อง กรุณาติดต่อผู้ดูแลระบบ");
        }

        return $this->conn;
    }
}
?>