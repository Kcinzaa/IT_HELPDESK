<?php
class Ticket {
    private $conn;
    private $table = "tickets";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ➕ สร้างใบแจ้งซ่อมใหม่
    public function createTicket($user_id, $title, $category, $desc, $urgency, $image_path = null) {
        $sql = "INSERT INTO " . $this->table . " 
                (user_id, title, category, problem_desc, urgency, image_path, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $title, $category, $desc, $urgency, $image_path]);
    }

    // 📋 ดึงรายการแจ้งซ่อมทั้งหมด (พร้อมชื่อคนแจ้งและแผนก) สำหรับ IT
    public function getAllTickets() {
        $sql = "SELECT t.*, u.full_name as reporter_name, d.dept_name, 
                       it.full_name as it_name
                FROM " . $this->table . " t
                LEFT JOIN users u ON t.user_id = u.user_id
                LEFT JOIN departments d ON u.dept_id = d.dept_id
                LEFT JOIN users it ON t.it_support_id = it.user_id
                ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 🧑‍⚕️ ดึงรายการแจ้งซ่อมของพยาบาลคนนั้นๆ (หน้าจอ User)
    public function getMyTickets($user_id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    // 🔍 ดึงรายละเอียดงาน 1 งานแบบเจาะลึก
    public function getTicketById($ticket_id) {
        $sql = "SELECT t.*, u.full_name as reporter_name, d.dept_name 
                FROM " . $this->table . " t
                JOIN users u ON t.user_id = u.user_id
                JOIN departments d ON u.dept_id = d.dept_id
                WHERE t.ticket_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ticket_id]);
        return $stmt->fetch();
    }

    // 🔄 อัปเดตสถานะงาน (เช่น รับงาน, ปิดงาน)
    public function updateStatus($ticket_id, $status, $it_id, $resolution_notes = null) {
        // ถ้าสถานะเป็น Resolved ให้บันทึกเวลาปิดงานด้วย
        $resolved_query = ($status === 'Resolved') ? ", resolved_at = NOW(), resolution_notes = ?" : "";
        
        $sql = "UPDATE " . $this->table . " 
                SET status = ?, it_support_id = ?" . $resolved_query . " 
                WHERE ticket_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($status === 'Resolved') {
            return $stmt->execute([$status, $it_id, $resolution_notes, $ticket_id]);
        } else {
            return $stmt->execute([$status, $it_id, $ticket_id]);
        }
    }

    // 📊 ดึงสถิติทำ Dashboard
    public function getStats() {
        $sql = "SELECT 
                  SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                  SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                  SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
                FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>