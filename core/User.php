<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $sql = "SELECT u.user_id, u.username, u.full_name, u.role, u.password, d.dept_name, d.dept_id 
                FROM " . $this->table . " u
                LEFT JOIN departments d ON u.dept_id = d.dept_id
                WHERE u.username = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // 🟢 เปลี่ยนบรรทัดนี้: ให้ใช้เครื่องหมาย === เช็ครหัสตรงๆ แทน
        if ($user && $user['password'] === $password) {
            unset($user['password']); 
            return $user;
        }
        return false;
    }

    // 🔍 ฟังก์ชันดึงข้อมูลพนักงาน 1 คน (เอาไว้ทำหน้า Profile)
    public function getUserById($id) {
        $sql = "SELECT user_id, username, full_name, role, dept_id FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // 📋 ฟังก์ชันดึงรายชื่อช่าง IT ทั้งหมด (เอาไว้ให้ Assign งาน)
    public function getITStaff() {
        $sql = "SELECT user_id, full_name FROM " . $this->table . " WHERE role IN ('it', 'admin')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>