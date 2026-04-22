-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 09:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_helpdesk1`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int(11) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `asset_name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` enum('Active','Inactive','Repair') DEFAULT 'Active',
  `purchase_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`asset_id`, `asset_code`, `asset_name`, `category`, `status`, `purchase_date`, `location`, `created_at`) VALUES
(1, 'PC-OPD-01', 'HP ProDesk 400 G7', 'PC', 'Active', NULL, 'แผนกผู้ป่วยนอก (OPD)', '2026-04-20 16:29:50'),
(2, 'PRN-PHM-02', 'Epson L3250', 'Printer', 'Active', NULL, 'ห้องจ่ายยา', '2026-04-20 16:29:50'),
(3, 'NW-SW-05', 'Cisco Catalyst 2960', 'Network', 'Active', NULL, 'ห้องเซิร์ฟเวอร์', '2026-04-20 16:29:50'),
(4, 'PC-OPD-02', 'Dell OptiPlex 3080', 'PC', 'Active', '2023-01-15', 'แผนกผู้ป่วยนอก (OPD)', '2026-04-21 08:20:03'),
(5, 'PC-ER-01', 'HP ProDesk 400 G7', 'PC', 'Active', '2022-05-10', 'แผนกฉุกเฉิน (ER)', '2026-04-21 08:20:03'),
(6, 'NB-IT-01', 'Lenovo ThinkPad E14', 'Notebook', 'Active', '2024-02-20', 'แผนก IT', '2026-04-21 08:20:03'),
(7, 'PRN-FIN-01', 'Brother HL-L2320D', 'Printer', 'Active', '2021-11-05', 'แผนกการเงิน', '2026-04-21 08:20:03'),
(8, 'NW-AP-01', 'Aruba IAP-305 (Access Point)', 'Network', 'Active', '2022-08-12', 'หอผู้ป่วยใน (IPD) ชั้น 2', '2026-04-21 08:20:03'),
(9, 'UPS-SRV-01', 'APC Smart-UPS 1500', 'Other', 'Active', '2020-03-10', 'ห้องเซิร์ฟเวอร์', '2026-04-21 08:20:03'),
(10, 'SCN-MED-01', 'Fujitsu fi-7160 (เครื่องสแกน)', 'Other', 'Active', '2023-07-25', 'แผนกเวชระเบียน', '2026-04-21 08:20:03'),
(11, 'PC-DEN-01', 'Dell OptiPlex 3080', 'PC', 'Repair', '2023-01-15', 'คลินิกทันตกรรม', '2026-04-21 08:20:03'),
(12, 'PRN-ER-02', 'Epson L3250', 'Printer', 'Active', '2024-01-10', 'แผนกฉุกเฉิน (ER)', '2026-04-21 08:20:03'),
(13, 'PC-IPD-01', 'HP EliteDesk 800 G4', 'PC', 'Inactive', '2018-05-20', 'หอผู้ป่วยใน (IPD) ชั้น 3', '2026-04-21 08:20:03'),
(14, 'NW-SW-02', 'Cisco Catalyst 2960', 'Network', 'Active', '2019-09-30', 'ตึกอำนวยการ ชั้น 1', '2026-04-21 08:20:03'),
(15, 'NB-DR-05', 'Apple MacBook Air M1', 'Notebook', 'Active', '2022-12-15', 'ห้องพักแพทย์', '2026-04-21 08:20:03');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `dept_name`) VALUES
(1, 'อายุรกรรมชาย'),
(2, 'ห้องฉุกเฉิน (ER)'),
(3, 'ศูนย์คอมพิวเตอร์ (IT)'),
(4, 'ห้องจ่ายยา');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` enum('Hardware','Software','Network','Other') DEFAULT 'Hardware',
  `stock_quantity` int(11) DEFAULT 0,
  `unit` varchar(50) DEFAULT 'ชิ้น',
  `min_threshold` int(11) DEFAULT 5,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`item_id`, `item_name`, `item_type`, `stock_quantity`, `unit`, `min_threshold`, `updated_at`) VALUES
(1, 'เมาส์ไร้สาย Logitech', 'Hardware', 25, 'อัน', 5, '2026-04-21 17:54:03'),
(2, 'คีย์บอร์ด USB OKER', 'Hardware', 15, 'อัน', 5, '2026-04-21 08:07:36'),
(3, 'สาย LAN CAT6 (3 เมตร)', 'Network', 40, 'เส้น', 10, '2026-04-21 08:07:36'),
(4, 'หัว RJ45 (ถุงละ 100 หัว)', 'Network', 5, 'ถุง', 2, '2026-04-21 08:07:36'),
(5, 'หมึกพิมพ์ Epson 003 (สีดำ)', 'Hardware', 10, 'ขวด', 4, '2026-04-21 09:04:18'),
(6, 'หมึกพิมพ์ HP 85A Toner', 'Hardware', 3, 'กล่อง', 2, '2026-04-21 08:07:36'),
(7, 'กระดาษ A4 Double A (80 แกรม)', 'Other', 50, 'รีม', 10, '2026-04-21 08:07:36'),
(8, 'Flash Drive SanDisk 32GB', 'Hardware', 8, 'อัน', 3, '2026-04-21 08:07:36'),
(9, 'ถ่าน BIOS CR2032', 'Hardware', 30, 'ก้อน', 10, '2026-04-21 08:07:36'),
(10, 'Switch Hub 8 Port TP-Link', 'Network', 2, 'เครื่อง', 3, '2026-04-21 08:07:36');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_withdrawals`
--

CREATE TABLE `inventory_withdrawals` (
  `withdrawal_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `withdraw_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_withdrawals`
--

INSERT INTO `inventory_withdrawals` (`withdrawal_id`, `item_id`, `ticket_id`, `admin_id`, `quantity`, `withdraw_date`) VALUES
(1, 5, 8, 3, 2, '2026-04-21 09:04:18'),
(2, 1, 9, 3, 1, '2026-04-21 17:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_articles`
--

CREATE TABLE `knowledge_articles` (
  `article_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `author_id` int(11) NOT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `knowledge_articles`
--

INSERT INTO `knowledge_articles` (`article_id`, `title`, `content`, `category`, `author_id`, `views`, `created_at`) VALUES
(1, 'วิธีแก้ปัญหาเบื้องต้น เครื่องปริ้นเตอร์ไม่ออก (ไฟกระพริบ)', '1. ตรวจสอบกระดาษติด\n2. ปิดเครื่องแล้วเปิดใหม่ (รอ 10 วินาที)\n3. ถอดสาย USB แล้วเสียบใหม่\nถ้ายังไม่ได้ ให้ทำการเปิด Ticket แจ้งซ่อมครับ', 'Printer', 3, 0, '2026-04-20 16:32:04'),
(2, 'วิธีแก้ไขเครื่องพิมพ์กระดาษติด (Paper Jam)', '1.ปิดสวิตช์เครื่องพิมพ์ก่อนเพื่อความปลอดภัย\r\n2.ค่อยๆ ดึงกระดาษออกตามทิศทางที่กระดาษวิ่ง ห้ามดึงย้อนกลับ\r\n3.ตรวจสอบเศษกระดาษเล็กๆ ที่อาจค้างอยู่ภายใน\r\n4.เปิดเครื่องใหม่และทดสอบการพิมพ์', 'Printer', 3, 0, '2026-04-21 07:46:07'),
(3, 'การตั้งค่าอีเมลโรงพยาบาลบน Outlook', 'การตั้งค่าเบื้องต้น 1. เปิดโปรแกรม Outlook 2. ไปที่ File > Add Account 3. ระบุอีเมล @hatyai.go.th 4. เลือกประเภทเป็น IMAP และระบุ Server ตามคู่มือระบบไอที', 'Software', 3, 0, '2026-04-21 07:46:07'),
(4, 'วิธีแก้ไขอินเทอร์เน็ตหลุดบ่อย (Wi-Fi)', 'ตรวจสอบเบื้องต้น หากพบว่าสัญญาณ Wi-Fi ไม่เสถียร ให้ลอง ลืมเครือข่าย (Forget Network) แล้วเชื่อมต่อใหม่ หรือตรวจสอบว่าไม่ได้เปิดโหมดเครื่องบินค้างไว้', 'Network', 4, 0, '2026-04-21 07:46:07'),
(5, 'คอมพิวเตอร์ค้างบ่อย แก้ไขอย่างไร?', 'สาเหตุและวิธีแก้ ส่วนใหญ่อาจเกิดจาก RAM ไม่เพียงพอ หรือความร้อนสูงเกินไป แนะนำให้ปิดโปรแกรมที่ไม่ใช้งาน หรือแจ้งช่างไอทีเพื่อทำความสะอาดฝุ่นภายในเครื่อง', 'Hardware', 4, 0, '2026-04-21 07:46:07'),
(7, 'ขั้นตอนการสแกนเอกสารส่งเข้าห้องยา', 'วิธีสแกนวาง เอกสารที่เครื่องสแกน > เลือกคำสั่ง Scan to PDF > เลือกปลายทางเป็นโฟลเดอร์ของห้องยา > ตรวจสอบไฟล์ในคอมพิวเตอร์ก่อนกดส่ง', 'General', 4, 0, '2026-04-21 07:46:07'),
(8, 'เครื่อง UPS ส่งเสียงร้องเตือนตลอดเวลา', 'หากมีเสียงปิ๊บยาวๆ แสดงว่า แบตเตอรี่เสื่อม หรือมีการใช้ไฟเกินกำลัง ให้ลองถอดปลั๊กอุปกรณ์ที่ไม่จำเป็นออก หากเสียงไม่หายให้แจ้งช่างทันที', 'Hardware', 3, 0, '2026-04-21 07:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `it_support_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `problem_desc` text NOT NULL,
  `building` varchar(100) DEFAULT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `room_no` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `urgency` enum('Normal','High','Critical') DEFAULT 'Normal',
  `status` enum('Pending','In Progress','Resolved','Closed') DEFAULT 'Pending',
  `resolution_notes` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `user_id`, `it_support_id`, `title`, `category`, `problem_desc`, `building`, `floor`, `room_no`, `image_path`, `urgency`, `status`, `resolution_notes`, `rating`, `feedback`, `created_at`, `resolved_at`) VALUES
(1, 1, 3, 'เปิดคอมไม่ติด', 'Hardware', 'มันเปิดไม่ติดเลย', NULL, NULL, NULL, NULL, 'High', 'Resolved', NULL, 4, '', '2026-04-20 16:38:21', '2026-04-20 17:25:56'),
(2, 3, 3, 'ใช้งาน word ไม่ได้', 'Software', 'มันเปิดไม่ติด', NULL, NULL, NULL, NULL, 'Normal', 'Resolved', NULL, NULL, NULL, '2026-04-20 17:27:52', '2026-04-20 17:29:39'),
(3, 1, 3, 'เวิดใช้ไม่ได้', 'Software', 'ใช้งานไม่ได้', NULL, NULL, NULL, NULL, 'Normal', 'Resolved', 'เรียบร้อยครับ', 5, '', '2026-04-20 17:29:21', '2026-04-20 17:34:07'),
(4, 1, 3, 'เน้ตมีปัญหา', 'Network', 'ช้ามาก', NULL, NULL, NULL, NULL, 'Critical', 'Resolved', NULL, 4, '', '2026-04-20 17:34:38', '2026-04-21 03:13:42'),
(5, 1, 3, 'คอมเปิดม่ติด', 'Hardware', 'เปิดไม่ติดฮ้าบ', 'อาคารเฉลิมพระเกียรติ', '9', 'ห้อง 1', NULL, 'High', 'Resolved', NULL, NULL, NULL, '2026-04-21 03:18:11', '2026-04-21 03:39:20'),
(6, 1, 3, 'ปริ้น', 'Hardware', '', 'อาคารเฉลิมพระเกียรติ', '7', 'ห้อง 1', NULL, 'High', 'Resolved', NULL, 4, '', '2026-04-21 03:44:15', '2026-04-21 04:05:43'),
(7, 1, 3, 'ปริ้นเตอร์กระดาศติด', 'Printer', '', 'อาคารอุบัติเหตุ', '2', 'โซนเหลือง', NULL, 'Normal', 'Resolved', NULL, NULL, NULL, '2026-04-21 04:06:29', '2026-04-21 04:33:07'),
(8, 1, 3, 'คอมพังค้า', 'Hardware', 'มันพัง', 'อาคารอุบัติเหตุ', '7', 'โซนเหลือง', NULL, 'Normal', 'Resolved', NULL, NULL, NULL, '2026-04-21 09:02:58', '2026-04-21 17:49:16'),
(9, 1, 3, 'คอมร้าย', 'Hardware', '', 'อาคาร 50 ปี', '3', 'ห้อง 1', NULL, 'Normal', 'Resolved', NULL, NULL, NULL, '2026-04-21 17:53:40', '2026-04-21 17:54:18');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_comments`
--

CREATE TABLE `ticket_comments` (
  `comment_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_comments`
--

INSERT INTO `ticket_comments` (`comment_id`, `ticket_id`, `user_id`, `message`, `created_at`) VALUES
(1, 1, 3, 'เสร้จสิ้นครับ', '2026-04-20 17:25:30'),
(2, 2, 3, 'โอเคครับกำลังไป', '2026-04-20 17:28:31'),
(3, 3, 3, 'เดะไปครับ', '2026-04-20 17:29:51'),
(4, 3, 1, 'โอเคค่ะ', '2026-04-20 17:33:10'),
(5, 3, 3, 'เรียบร้อยครับ', '2026-04-20 17:34:07'),
(6, 4, 3, 'ใจเย้นนะ', '2026-04-20 17:35:23'),
(7, 5, 1, 'ดีดีดีดีดี', '2026-04-21 03:18:24'),
(8, 7, 1, 'มาตอนไหน', '2026-04-21 04:06:42'),
(9, 7, 3, 'เดะไป', '2026-04-21 04:12:54'),
(10, 7, 3, 'เรียบร้อย', '2026-04-21 04:32:50'),
(11, 8, 1, 'มาตอนหนาย', '2026-04-21 09:03:09'),
(12, 8, 3, 'เดะไป', '2026-04-21 09:03:45'),
(13, 8, 3, '🛠️ [ระบบ] บันทึกการใช้อะไหล่: หมึกพิมพ์ Epson 003 (สีดำ) จำนวน 2', '2026-04-21 09:04:18'),
(14, 9, 3, 'ดีค้าบ', '2026-04-21 17:53:56'),
(15, 9, 3, '🛠️ [ระบบ] บันทึกการใช้อะไหล่: เมาส์ไร้สาย Logitech จำนวน 1', '2026-04-21 17:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `role` enum('staff','it','admin') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `dept_id`, `role`, `created_at`) VALUES
(1, 'nurse01', '1234', 'พยาบาลวิชาชีพ เอ', 1, 'staff', '2026-04-21 03:42:22'),
(3, 'it_nick', '1234', 'ช่างไอที นิค', 3, 'it', '2026-04-21 03:42:22'),
(4, 'admin_kang', '1234', 'หัวหน้าศูนย์คอมฯ', 3, 'admin', '2026-04-21 03:42:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD UNIQUE KEY `asset_code` (`asset_code`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `inventory_withdrawals`
--
ALTER TABLE `inventory_withdrawals`
  ADD PRIMARY KEY (`withdrawal_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `knowledge_articles`
--
ALTER TABLE `knowledge_articles`
  ADD PRIMARY KEY (`article_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `it_support_id` (`it_support_id`);

--
-- Indexes for table `ticket_comments`
--
ALTER TABLE `ticket_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `dept_id` (`dept_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inventory_withdrawals`
--
ALTER TABLE `inventory_withdrawals`
  MODIFY `withdrawal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `knowledge_articles`
--
ALTER TABLE `knowledge_articles`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ticket_comments`
--
ALTER TABLE `ticket_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_withdrawals`
--
ALTER TABLE `inventory_withdrawals`
  ADD CONSTRAINT `inventory_withdrawals_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`),
  ADD CONSTRAINT `inventory_withdrawals_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`);

--
-- Constraints for table `knowledge_articles`
--
ALTER TABLE `knowledge_articles`
  ADD CONSTRAINT `knowledge_articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`it_support_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ticket_comments`
--
ALTER TABLE `ticket_comments`
  ADD CONSTRAINT `ticket_comments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
