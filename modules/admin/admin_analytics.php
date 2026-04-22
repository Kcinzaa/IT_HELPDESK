<?php
require_once '../../core/config.php';
require_once '../../core/database.php';
require_once '../../includes/auth.php';

// บังคับให้เข้าได้เฉพาะ Admin (หัวหน้าศูนย์)
checkAuth(['admin']);

$db = new Database();
$conn = $db->connect();

// ==========================================
// 🚀 ดึงข้อมูลจากฐานข้อมูลเตรียมไว้ให้ JavaScript
// ==========================================
// 3. ดึงรายการงานทั้งหมด (เพิ่มการ JOIN ตาราง users เพื่อเอาชื่อช่าง)
$stmtAll = $conn->query("
    SELECT t.ticket_id as id, t.title, t.building as dept, t.category, t.status, u.full_name as tech_name 
    FROM tickets t 
    LEFT JOIN users u ON t.it_support_id = u.user_id 
    ORDER BY t.created_at DESC
");
$allTickets = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark"><i class="bi bi-graph-up-arrow text-primary me-2"></i> ศูนย์วิเคราะห์ข้อมูล IT Support</h3>
            <p class="text-muted">ระบบ Interactive Analytics สำหรับผู้บริหาร (Real-time Filtering)</p>
        </div>
        
        <div class="d-flex gap-2">
            <button onclick="exportToExcel()" class="btn btn-success shadow-sm fw-bold">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> ส่งออก Excel
            </button>
            <button onclick="exportToPDF()" class="btn btn-danger shadow-sm fw-bold">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> ดาวน์โหลด PDF
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">เลือกแผนก</label>
                    <select id="filterDept" class="form-select border-primary">
                        <option value="all">ทุกแผนกรวมกัน</option>
                        <option value="OPD">ผู้ป่วยนอก (OPD)</option>
                        <option value="ER">อุบัติเหตุและฉุกเฉิน (ER)</option>
                        <option value="Wards">ผู้ป่วยใน (Wards)</option>
                        <option value="BackOffice">สายสนับสนุน (การเงิน/บริหาร)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">หมวดหมู่ปัญหา</label>
                    <select id="filterCategory" class="form-select border-primary">
                        <option value="all">ทุกหมวดหมู่</option>
                        <option value="Hardware">Hardware</option>
                        <option value="Software">Software</option>
                        <option value="Network">Network</option>
                        <option value="Printer">Printer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">สถานะงาน</label>
                    <select id="filterStatus" class="form-select border-primary">
                        <option value="all">ทุกสถานะ</option>
                        <option value="Pending">รอดำเนินการ</option>
                        <option value="In Progress">กำลังซ่อม</option>
                        <option value="Resolved">ปิดงานแล้ว</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-danger w-100 fw-bold">
                        <i class="bi bi-arrow-counterclockwise"></i> ล้างตัวกรอง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill text-warning me-2"></i> สัดส่วนสถานะงาน</h6>
                </div>
                <div class="card-body d-flex justify-content-center p-4">
                    <canvas id="statusChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-info me-2"></i> สถิติแยกตามหมวดหมู่</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="categoryChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-list-columns text-success me-2"></i> รายการแจ้งซ่อมตามเงื่อนไข (<span id="ticketCount" class="text-primary">0</span> รายการ)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="analyticsTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="border-0 rounded-start">รหัสงาน</th>
                            <th class="border-0">หัวข้อปัญหา</th>
                            <th class="border-0">แผนก</th>
                            <th class="border-0">หมวดหมู่</th>
                            <th class="border-0">ช่างผู้ดูแล</th>
                            <th class="border-0 text-center rounded-end">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // 💡 1. รับข้อมูลจริงจาก PHP (แปลงเป็น JSON)
    const realData = <?php echo json_encode($allTickets); ?>;

    // ตัวแปรกราฟ
    let statusChartInstance = null;
    let categoryChartInstance = null;

    // 💡 2. ฟังก์ชันอัปเดต Dashboard
    function updateDashboard() {
        const fDept = document.getElementById('filterDept').value;
        const fCat = document.getElementById('filterCategory').value;
        const fStat = document.getElementById('filterStatus').value;

        // กรองข้อมูล
        const filteredData = realData.filter(item => {
            return (fDept === 'all' || item.dept === fDept) &&
                   (fCat === 'all' || item.category === fCat) &&
                   (fStat === 'all' || item.status === fStat);
        });

        document.getElementById('ticketCount').innerText = filteredData.length;
        renderTable(filteredData);
        renderCharts(filteredData);
    }

    // 💡 3. ฟังก์ชันวาดตาราง
    function renderTable(data) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = ''; 

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">ไม่มีข้อมูลที่ตรงกับเงื่อนไข</td></tr>';
            return;
        }

        data.forEach(item => {
            let badgeColor = item.status === 'Resolved' ? 'bg-success' : (item.status === 'In Progress' ? 'bg-warning' : 'bg-danger');
    
            // 💡 เช็คว่ามีช่างรับงานหรือยัง ถ้ายังให้ขึ้นว่ารอรับงาน
            let technician = item.tech_name ? `<i class="bi bi-person-workspace text-primary me-1"></i>${item.tech_name}` : '<span class="text-muted fst-italic">ยังไม่รับงาน</span>';
    
            // 💡 เช็คว่าแผนกเป็น null ไหม (สำหรับข้อมูลเก่า) ให้เปลี่ยนเป็นขีด -
            let deptName = item.dept ? item.dept : '-';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-muted fw-bold">#${item.id}</td>
                <td class="fw-bold">${item.title}</td>
                <td>${deptName}</td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">${item.category}</span></td>
                <td><small class="fw-bold">${technician}</small></td>
                <td class="text-center"><span class="badge ${badgeColor}">${item.status}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    // 💡 4. ฟังก์ชันวาดกราฟ
    function renderCharts(data) {
        const countsStat = { 'Pending': 0, 'In Progress': 0, 'Resolved': 0 };
        const countsCat = { 'Hardware': 0, 'Software': 0, 'Network': 0, 'Printer': 0 };

        // นับจำนวนจากข้อมูลที่กรองแล้ว
        data.forEach(item => {
            if(countsStat[item.status] !== undefined) countsStat[item.status]++;
            if(countsCat[item.category] !== undefined) countsCat[item.category]++;
        });

        if (statusChartInstance) statusChartInstance.destroy();
        if (categoryChartInstance) categoryChartInstance.destroy();

        // วาดกราฟวงกลม
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        statusChartInstance = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Resolved'],
                datasets: [{
                    data: [countsStat['Pending'], countsStat['In Progress'], countsStat['Resolved']],
                    backgroundColor: ['#dc3545', '#ffc107', '#198754']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // วาดกราฟแท่ง
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        categoryChartInstance = new Chart(ctxCat, {
            type: 'bar',
            data: {
                labels: ['Hardware', 'Software', 'Network', 'Printer'],
                datasets: [{
                    label: 'จำนวนงานแจ้งซ่อม',
                    data: [countsCat['Hardware'], countsCat['Software'], countsCat['Network'], countsCat['Printer']],
                    backgroundColor: '#0d6efd',
                    borderRadius: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // 💡 5. ผูก Event Listener กับปุ่มและ Dropdown
    document.getElementById('filterDept').addEventListener('change', updateDashboard);
    document.getElementById('filterCategory').addEventListener('change', updateDashboard);
    document.getElementById('filterStatus').addEventListener('change', updateDashboard);

    document.getElementById('resetFilters').addEventListener('click', function() {
        document.getElementById('filterDept').value = 'all';
        document.getElementById('filterCategory').value = 'all';
        document.getElementById('filterStatus').value = 'all';
        updateDashboard(); 
    });

    // เริ่มทำงานครั้งแรก!
    updateDashboard();
});

// ==========================================
// 📊 ฟังก์ชันส่งออกเป็น Excel (ดึงจากตาราง)
// ==========================================
function exportToExcel() {
    // 1. ดึงตาราง HTML ของเรามา
    let table = document.getElementById("analyticsTable");
    
    // 2. แปลงตารางเป็นสมุดงาน Excel
    let wb = XLSX.utils.table_to_book(table, {sheet: "รายงานแจ้งซ่อม"});
    
    // 3. สร้างชื่อไฟล์ใส่วันที่ปัจจุบัน
    let date = new Date().toISOString().split('T')[0];
    
    // 4. สั่งดาวน์โหลดทันที!
    XLSX.writeFile(wb, `IT_Report_${date}.xlsx`);
    
    Swal.fire({
        icon: 'success', title: 'ดาวน์โหลด Excel สำเร็จ!', timer: 1500, showConfirmButton: false
    });
}

// ==========================================
// 📄 ฟังก์ชันส่งออกเป็น PDF (ถ่ายรูปทั้งหน้าเว็บ)
// ==========================================
// ==========================================
// 📊 ฟังก์ชันส่งออกเป็น Excel (ดึงจากตาราง)
// ==========================================
function exportToExcel() {
    // 1. ดึงตาราง HTML ของเรามา
    let table = document.getElementById("analyticsTable");
    
    // 2. แปลงตารางเป็นสมุดงาน Excel
    let wb = XLSX.utils.table_to_book(table, {sheet: "รายงานแจ้งซ่อม"});
    
    // 3. สร้างชื่อไฟล์ใส่วันที่ปัจจุบัน
    let date = new Date().toISOString().split('T')[0];
    
    // 4. สั่งดาวน์โหลดทันที!
    XLSX.writeFile(wb, `IT_Report_${date}.xlsx`);
    
    Swal.fire({
        icon: 'success', title: 'ดาวน์โหลด Excel สำเร็จ!', timer: 1500, showConfirmButton: false
    });
}

// ==========================================
// 📄 ฟังก์ชันส่งออกเป็น PDF (ถ่ายรูปทั้งหน้าเว็บ)
// ==========================================
function exportToPDF() {
    // ซ่อนปุ่มต่างๆ ก่อนถ่ายรูป (จะได้ไม่ติดไปใน PDF)
    document.getElementById("resetFilters").style.display = "none";

    // เลือกส่วนของหน้าเว็บที่จะเอาไปทำ PDF (ในที่นี้คือ container หลัก)
    let element = document.querySelector('.container-fluid');
    let date = new Date().toISOString().split('T')[0];

    // ตั้งค่าหน้ากระดาษ PDF
    let opt = {
        margin:       0.5,
        filename:     `IT_Dashboard_${date}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true }, // scale 2 ทำให้ภาพชัดขึ้น
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' } // แนวนอน
    };

    // โชว์ Loading สวยๆ
    Swal.fire({
        title: 'กำลังสร้างไฟล์ PDF...',
        text: 'กรุณารอสักครู่ (อาจใช้เวลา 3-5 วินาที)',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    // สั่งสร้าง PDF
    html2pdf().set(opt).from(element).save().then(() => {
        // คืนค่าปุ่มที่ซ่อนไว้กลับมา
        document.getElementById("resetFilters").style.display = "block";
        Swal.close();
    });
}
</script>



<?php require_once '../../includes/footer.php'; ?>