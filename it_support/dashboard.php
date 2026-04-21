<?php
require_once '../core/config.php';
require_once '../core/database.php';
require_once '../core/Ticket.php';
require_once '../includes/auth.php';

// เช็คสิทธิ์ อนุญาตเฉพาะ it และ admin
checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();
$ticketObj = new Ticket($conn);

// 1. ดึงข้อมูลสถิติตัวเลข
$stats = $ticketObj->getStats();

// 2. ดึงข้อมูลสำหรับทำกราฟแยกตามหมวดหมู่ (โดนัท)
$cat_stmt = $conn->query("SELECT category, COUNT(*) as count FROM tickets GROUP BY category");
$category_data = $cat_stmt->fetchAll();

// 3. ดึงรายการแจ้งซ่อมทั้งหมด (ลงตาราง)
$tickets = $ticketObj->getAllTickets(); 

// 4. ดึงข้อมูลย้อนหลัง 7 วัน สำหรับกราฟเส้น (Trend)
$trend_stmt = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM tickets 
    WHERE created_at >= DATE(NOW()) - INTERVAL 7 DAY 
    GROUP BY DATE(created_at) 
    ORDER BY date ASC
");
$trend_data = $trend_stmt->fetchAll();

// โหลด Header และ Sidebar มาประกอบ
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="text-dark fw-bold mb-1">🚀 ศูนย์บัญชาการ IT (Dashboard)</h3>
        <p class="text-muted small">ภาพรวมระบบแจ้งซ่อมแบบเรียลไทม์</p>
    </div>
    <a href="export_data.php" class="btn btn-dark shadow-sm"><i class="bi bi-bar-chart-fill me-2"></i>วิเคราะห์ใน Power BI</a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-custom stat-card bg-gradient-primary p-4 h-100 shadow">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 text-uppercase fw-bold mb-1">รอดำเนินการ</h6>
                    <h1 class="display-5 fw-bold text-white mb-0"><?php echo $stats['pending'] ?? 0; ?></h1>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-hourglass-split text-white fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom stat-card bg-gradient-warning p-4 h-100 shadow">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 text-uppercase fw-bold mb-1">กำลังแก้ไข</h6>
                    <h1 class="display-5 fw-bold text-white mb-0"><?php echo $stats['in_progress'] ?? 0; ?></h1>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-tools text-white fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom stat-card bg-gradient-success p-4 h-100 shadow">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 text-uppercase fw-bold mb-1">ปิดงานแล้ว</h6>
                    <h1 class="display-5 fw-bold text-white mb-0"><?php echo $stats['resolved'] ?? 0; ?></h1>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-check2-circle text-white fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom p-4 shadow-sm">
            <h6 class="fw-bold mb-3"><i class="bi bi-graph-up text-success me-2"></i> แนวโน้มการแจ้งซ่อมย้อนหลัง 7 วัน (Ticket Trend)</h6>
            <div style="position: relative; height:250px; width:100%">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-4">
        <div class="card card-custom p-4 h-100 shadow-sm">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i> สัดส่วนปัญหา (แยกตามหมวดหมู่)</h6>
            <div style="position: relative; height:250px; width:100%">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom p-4 shadow-sm">
            <h6 class="fw-bold mb-3"><i class="bi bi-list-nested text-primary me-2"></i> คิวงานแจ้งซ่อม (Tickets)</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th>รหัส</th>
                            <th>วอร์ด</th>
                            <th>ปัญหา</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tickets)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">ยังไม่มีข้อมูล</td></tr>
                        <?php else: ?>
                            <?php foreach($tickets as $t): ?>
                            <tr>
                                <td class="fw-bold text-primary">#TK-<?php echo $t['ticket_id']; ?></td>
                                <td><?php echo htmlspecialchars($t['dept_name']); ?></td>
                                <td><?php echo htmlspecialchars($t['title']); ?></td>
                                <td>
                                    <?php 
                                        if($t['status'] == 'Pending') echo '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger">รอรับงาน</span>';
                                        elseif($t['status'] == 'In Progress') echo '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning">กำลังซ่อม</span>';
                                        else echo '<span class="badge bg-success bg-opacity-10 text-success border border-success">ปิดงาน</span>';
                                    ?>
                                </td>
                                <td>
                                    <a href="ticket_detail.php?id=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-light border shadow-sm"><i class="bi bi-eye"></i> ดู</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // กราฟที่ 1: โดนัท (สัดส่วนปัญหา)
    // ==========================================
    const catLabels = [<?php foreach($category_data as $c) echo "'".$c['category']."', "; ?>];
    const catCounts = [<?php foreach($category_data as $c) echo $c['count'].", "; ?>];
    
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catCounts,
                backgroundColor: ['#4361ee', '#f72585', '#4cc9f0', '#f8961e', '#2a9d8f'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
        }
    });

    // ==========================================
    // กราฟที่ 2: เส้น (แนวโน้ม 7 วัน)
    // ==========================================
    const trendLabels = [<?php foreach($trend_data as $d) echo "'".date('d/m', strtotime($d['date']))."', "; ?>];
    const trendCounts = [<?php foreach($trend_data as $d) echo $d['count'].", "; ?>];
    
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'จำนวนการแจ้งซ่อม',
                data: trendCounts,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4361ee',
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4 // เส้นโค้งสมูท
            }]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1 }, // ให้แกน Y โชว์เลขจำนวนเต็ม
                    grid: { borderDash: [5, 5], color: '#e2e8f0' } 
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>