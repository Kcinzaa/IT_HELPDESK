<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

checkAuth(['it', 'admin']); // เฉพาะ IT และ Admin ที่ดูรายงานได้

$db = new Database();
$conn = $db->connect();

// รับค่าเดือนและปีจาก Form (ค่าเริ่มต้นคือเดือนปัจจุบัน)
$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');

// 📊 1. ดึงสถิติรวมของเดือนนั้นๆ
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as closed,
    SUM(CASE WHEN status != 'Resolved' THEN 1 ELSE 0 END) as pending
    FROM tickets 
    WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
$stmt->execute([$selected_month, $selected_year]);
$summary = $stmt->fetch();

// 📂 2. ดึงสถิติแยกตามหมวดหมู่ (เอาไปทำกราฟ)
$stmt = $conn->prepare("SELECT category, COUNT(*) as count 
                        FROM tickets 
                        WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? 
                        GROUP BY category");
$stmt->execute([$selected_month, $selected_year]);
$categories = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-graph-up-arrow text-primary me-2"></i> รายงานสรุปประจำเดือน</h3>
            <p class="text-muted small">ข้อมูลประจำเดือน <?php echo date('F', mktime(0, 0, 0, $selected_month, 10)); ?> ปี <?php echo $selected_year; ?></p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-dark me-2"><i class="bi bi-printer me-2"></i>พิมพ์รายงาน</button>
            <a href="export_excel.php" class="btn btn-success"><i class="bi bi-file-earmark-excel me-2"></i>Excel</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-4 no-print">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="small fw-bold">เลือกเดือน</label>
                <select name="month" class="form-select">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo sprintf('%02d', $m); ?>" <?php if($selected_month == $m) echo 'selected'; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">เลือกปี</label>
                <select name="year" class="form-select">
                    <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php if($selected_year == $y) echo 'selected'; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 mt-auto">
                <button type="submit" class="btn btn-primary w-100">ดึงข้อมูล</button>
            </div>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center border-start border-primary border-5">
                <h6 class="text-muted">งานแจ้งซ่อมทั้งหมด</h6>
                <h1 class="fw-bold mb-0"><?php echo $summary['total'] ?? 0; ?></h1>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center border-start border-success border-5">
                <h6 class="text-muted">ปิดงานสำเร็จ (Resolved)</h6>
                <h1 class="fw-bold text-success mb-0"><?php echo $summary['closed'] ?? 0; ?></h1>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center border-start border-danger border-5">
                <h6 class="text-muted">งานค้าง/กำลังทำ</h6>
                <h1 class="fw-bold text-danger mb-0"><?php echo $summary['pending'] ?? 0; ?></h1>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4">สัดส่วนปัญหาตามหมวดหมู่</h5>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4">วิเคราะห์ข้อมูลเบื้องต้น</h5>
                <div class="alert alert-info">
                    <?php 
                        $rate = ($summary['total'] > 0) ? ($summary['closed'] / $summary['total']) * 100 : 0;
                    ?>
                    ในเดือนนี้ทีม IT สามารถปิดงานได้คิดเป็น <strong><?php echo number_format($rate, 1); ?>%</strong> ของงานทั้งหมด
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach($categories as $cat): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo $cat['category']; ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $cat['count']; ?> งาน</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach($categories as $cat) echo "'".$cat['category']."',"; ?>],
            datasets: [{
                data: [<?php foreach($categories as $cat) echo $cat['count'].","; ?>],
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6610f2', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

<style>
@media print {
    .no-print, .sidebar { display: none !important; }
    .main-content { margin-left: 0 !important; width: 100% !important; }
    .card { shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php require_once '../../includes/footer.php'; ?>