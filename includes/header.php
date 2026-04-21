<?php
// โหลดไฟล์ config เพื่อให้รู้จักคำว่า BASE_URL
require_once __DIR__ . '/../core/config.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT  Support Helpdesk - รพ.หาดใหญ่</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/logo-hatyai.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <style>
        /* จัดโครงสร้างให้ Sidebar เต็มจอเสมอ */
        html, body { height: 100%; }
        .wrapper { min-height: 100vh; }
        .main-content { background-color: var(--secondary-bg); }
    </style>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="d-flex wrapper">