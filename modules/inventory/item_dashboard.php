<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// อนุญาตเฉพาะ IT และ Admin
checkAuth(['it', 'admin']);

$db = new Database();
$conn = $db->connect();

// ==========================================
// 📊 1. ดึงข้อมูลตัวเลขสรุป (KPI Cards)
// ==========================================
// 1.1 จำนวนพัสดุคงเหลือทั้งหมดรวมกัน
$stmt = $conn->query("SELECT SUM(stock_quantity) as total_items FROM inventory");
$kpi_total_stock = $stmt->fetch()['total_items'] ?? 0;

// 1.2 จำนวนรายการที่ "ของใกล้หมด" (ต่ำกว่า min_threshold)
$stmt = $conn->query("SELECT COUNT(item_id) as low_stock_count FROM inventory WHERE stock_quantity <= min_threshold");
$kpi_low_stock = $stmt->fetch()['low_stock_count'] ?? 0;

// 1.3 จำนวนพัสดุที่ถูกเบิกไป "ในเดือนนี้"
$current_month = date('Y-m');
$stmt = $conn->prepare("SELECT SUM(quantity) as month_withdraw FROM inventory_withdrawals WHERE DATE_FORMAT(withdraw_date, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$kpi_month_withdraw = $stmt->fetch()['month_withdraw'] ?? 0;

// ==========================================
// 📈 2. ดึงข้อมูลสำหรับทำกราฟ (Chart.js)
// ==========================================
// 2.1 สัดส่วนพัสดุแยกตามหมวดหมู่ (Pie Chart)
$stmt = $conn->query("SELECT item_type, SUM(stock_quantity) as total FROM inventory GROUP BY item_type");
$chart_categories = $stmt->fetchAll();

// 2.2 สถิติการเบิกย้อนหลัง 7 วัน (Bar Chart)
$stmt = $conn->query("
    SELECT DATE(withdraw_date) as w_date, SUM(quantity) as total_qty 
    FROM inventory_withdrawals 
    WHERE withdraw_date >= DATE(NOW()) - INTERVAL 7 DAY 
    GROUP BY DATE(withdraw_date) 
    ORDER BY w_date ASC
");
$chart_trend = $stmt->fetchAll();

// ==========================================
// 📋 3. ดึงข้อมูลตารางแสดงผล
// ==========================================
// 3.1 ตารางแจ้งเตือนของใกล้หมดสต็อก
$stmt = $conn->query("SELECT item_name, stock_quantity, min_threshold, unit FROM inventory WHERE stock_quantity <= min_threshold ORDER BY stock_quantity ASC LIMIT 5");
$low_stock_items = $stmt->fetchAll();

// 3.2 ตารางประวัติการเบิกล่าสุด
$stmt = $conn->query("
    SELECT w.quantity, w.withdraw_date, i.item_name, i.unit, u.full_name as admin_name, t.ticket_id 
    FROM inventory_withdrawals w
    JOIN inventory i ON w.item_id = i.item_id
    JOIN users u ON w.admin_id = u.user_id
    LEFT JOIN tickets t ON w.ticket_id = t.ticket_id
    ORDER BY w.withdraw_date DESC LIMIT 5
");
$recent_withdrawals = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i> แดชบอร์ดคลังอะไหล่ IT</h3>
            <p class="text-muted">ภาพรวมสถานะอุปกรณ์และอะไหล่ในสต็อก</p>
        </div>
        <a href="item.php" class="btn btn-primary shadow-sm fw-bold"><i class="bi bi-plus-circle me-2"></i> จัดการรายการอะไหล่</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100 hover-lift border-start border-primary border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold mb-2">อะไหล่คงเหลือรวม</h6>
                        <h2 class="fw-black mb-0 text-dark"><?php echo number_format($kpi_total_stock); ?> <span class="fs-6 text-muted fw-normal">ชิ้น</span></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="bi bi-boxes fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100 hover-lift border-start border-danger border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold mb-2">ต้องสั่งซื้อด่วน (ใกล้หมด)</h6>
                        <h2 class="fw-black mb-0 text-danger"><?php echo number_format($kpi_low_stock); ?> <span class="fs-6 text-muted fw-normal">รายการ</span></h2>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100 hover-lift border-start border-success border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-bold mb-2">เบิกใช้งานเดือนนี้</h6>
                        <h2 class="fw-black mb-0 text-success"><?php echo number_format($kpi_month_withdraw); ?> <span class="fs-6 text-muted fw-normal">ชิ้น</span></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                        <i class="bi bi-cart-check-fill fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-4">สัดส่วนพัสดุตามหมวดหมู่</h6>
                <div style="height: 250px; display: flex; justify-content: center;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-4">แนวโน้มการใช้อะไหล่ (7 วันย้อนหลัง)</h6>
                <div style="height: 250px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold text-danger"><i class="bi bi-bell-fill me-2"></i> แจ้งเตือนพัสดุใกล้หมดสต็อก</h6>
                </div>
                <div class="card-body p-4">
                    <?php if(empty($low_stock_items)): ?>
                        <div class="text-center text-muted py-4"><i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i> สต็อกปลอดภัย ไม่มีของใกล้หมด</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($low_stock_items as $item): 
                                // คำนวณเปอร์เซ็นต์ความวิกฤต
                                $percent = ($item['min_threshold'] > 0) ? ($item['stock_quantity'] / $item['min_threshold']) * 100 : 0;
                                $bar_color = ($percent <= 50) ? 'bg-danger' : 'bg-warning';
                            ?>
                            <li class="list-group-item px-0 py-3 border-bottom-dashed">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                    <span class="badge bg-danger rounded-pill">เหลือ <?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $bar_color; ?>" style="width: <?php echo max(5, $percent); ?>%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">จุดสั่งซื้อ (Min): <?php echo $item['min_threshold']; ?></small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> ประวัติการเบิกล่าสุด</h6>
                    <a href="withdraw_history.php" class="btn btn-sm btn-outline-secondary">ดูทั้งหมด</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle w-100 mb-0" id="recentTable">
                            <thead class="table-light">
                                <tr>
                                    <th>รายการพัสดุ</th>
                                    <th>จำนวน</th>
                                    <th>อ้างอิงงาน</th>
                                    <th>เวลา</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_withdrawals as $w): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($w['item_name']); ?></div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($w['admin_name']); ?></small>
                                    </td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary border">-<?php echo $w['quantity']; ?> <?php echo htmlspecialchars($w['unit']); ?></span></td>
                                    <td>
                                        <?php if($w['ticket_id']): ?>
                                            <a href="../tickets/ticket_detail.php?id=<?php echo $w['ticket_id']; ?>" class="text-decoration-none">#TK-<?php echo $w['ticket_id']; ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo date('d/m H:i', strtotime($w['withdraw_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. กราฟโดนัท (สัดส่วนหมวดหมู่)
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach($chart_categories as $c) echo "'".$c['item_type']."',"; ?>],
            datasets: [{
                data: [<?php foreach($chart_categories as $c) echo $c['total'].","; ?>],
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6c757d', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // 2. กราฟแท่ง (แนวโน้มการเบิก 7 วัน)
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: [<?php foreach($chart_trend as $t) echo "'".date('d M', strtotime($t['w_date']))."',"; ?>],
            datasets: [{
                label: 'จำนวนที่เบิก (ชิ้น)',
                data: [<?php foreach($chart_trend as $t) echo $t['total_qty'].","; ?>],
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: '#0d6efd',
                borderWidth: 2,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });

    // 💡 เพิ่มส่วนนี้เข้าไปใน $(document).ready(...)
    $(document).ready(function() {
    
    // ตั้งค่าตารางประวัติการเบิก (แก้บั๊ก DataTables)
        $('#recentTable').DataTable({
            paging: false,      // ปิดแบ่งหน้า
            searching: false,   // ปิดช่องค้นหา
            info: false,        // ปิดข้อความ Showing 1 to 5
            ordering: false,    // ปิดการเรียงลำดับหัวตาราง
            language: {
                emptyTable: "<div class='text-center text-muted py-3'>ยังไม่มีประวัติการเบิกพัสดุ</div>"
            }
        });

    });
</script>

<style>
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.border-bottom-dashed { border-bottom: 1px dashed #dee2e6; }
.list-group-item:last-child { border-bottom: none; }
</style>

<?php require_once '../../includes/footer.php'; ?>