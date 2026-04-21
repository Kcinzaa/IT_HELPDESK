<?php
require_once 'config.php';

class Notification {
    
    // 📱 1. ส่งข้อความเข้า LINE Notify กลุ่มช่าง IT
    public static function sendLine($message) {
        if (empty(LINE_TOKEN) || LINE_TOKEN === 'ใส่_TOKEN_ของ_LINE_NOTIFY_ที่นี่') return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "message=" . $message);
        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . LINE_TOKEN);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // 🕵️ 2. ระบบเก็บบันทึกประวัติการใช้งาน (Audit Log)
    // การเรียกใช้: Notification::logActivity("ช่างไอที นิค", "กดรับงานแจ้งซ่อม #TK-05");
    public static function logActivity($user_name, $action_detail) {
        // หาที่อยู่ของไฟล์ log
        $logFile = __DIR__ . '/../logs/activity_log.txt';
        
        // รูปแบบข้อความ: [2026-04-20 14:30:00] [IP: 192.168.1.10] ช่างไอที นิค -> กดรับงานแจ้งซ่อม #TK-05
        $time = date("Y-m-d H:i:s");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
        
        $logMessage = "[{$time}] [IP: {$ip}] {$user_name} -> {$action_detail}" . PHP_EOL;
        
        // เขียนไฟล์ต่อท้ายไปเรื่อยๆ (FILE_APPEND)
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
?>