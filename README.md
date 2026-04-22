# 🏥 IT Support Helpdesk System - โรงพยาบาลหาดใหญ่

ระบบจัดการงานแจ้งซ่อมและคลังอุปกรณ์สารสนเทศ (IT Asset & Helpdesk Management) พัฒนาขึ้นเพื่อช่วยลดขั้นตอนการทำงานของช่างคอมพิวเตอร์ และเพิ่มประสิทธิภาพในการให้บริการบุคลากรทางการแพทย์

## ✨ ฟีเจอร์หลัก (Key Features)
- 📊 **Interactive Dashboard:** ระบบวิเคราะห์ข้อมูลงานซ่อมแบบ Real-time ด้วย Chart.js
- 🎫 **Ticket Management:** ระบบแจ้งซ่อมและติดตามสถานะงาน
- 📱 **PWA Ready:** รองรับการติดตั้งเป็นแอปพลิเคชันบนสมาร์ทโฟน
- 📷 **QR Code Scanner:** สแกนรหัสทรัพย์สินผ่านกล้องมือถือเพื่อดูประวัติการซ่อม
- 📋 **Kanban Board:** กระดานจัดการสถานะงานแบบลากวาง (Drag & Drop)

## 🛠️ เทคโนโลยีที่ใช้ (Tech Stack)
* **Frontend:** HTML5, CSS3, Bootstrap 5, Vanilla JavaScript
* **Backend:** PHP 8 (PDO)
* **Database:** MySQL / MariaDB
* **Libraries:** Chart.js, SweetAlert2, HTML5-QRCode, DataTables

## 🚀 วิธีการติดตั้ง (Installation)
1. นำโฟลเดอร์โปรเจกต์ไปวางใน `C:\xampp\htdocs\`
2. นำเข้าไฟล์ฐานข้อมูล `database.sql` ผ่านทาง phpMyAdmin
3. ตั้งค่าการเชื่อมต่อฐานข้อมูลในไฟล์ `core/config.php`
4. เปิดเบราว์เซอร์และพิมพ์ `http://localhost/helpdesk`

---
*Developed by: Komkrit (Nick) Ponimdang*