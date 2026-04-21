// ฟังก์ชันสำหรับดึงสถิติ Dashboard แบบ Real-time
async function fetchDashboardStats() {
    try {
        // ยิงคำขอไปที่ API ของเรา
        const response = await fetch('../api/get_dashboard_stats.php');
        if (!response.ok) throw new Error('Network response was not ok');
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // อัปเดตตัวเลขบนหน้าจอ (ต้องไปใส่ id="stat-pending" ฯลฯ ไว้ที่ตัวเลขในการ์ดด้วย)
            const elPending = document.getElementById('stat-pending');
            const elProgress = document.getElementById('stat-in-progress');
            const elResolved = document.getElementById('stat-resolved');
            
            // ใส่แอนิเมชันเด้งนิดนึงเวลาตัวเลขเปลี่ยน
            if(elPending && elPending.innerText != result.data.pending) {
                elPending.innerText = result.data.pending;
                elPending.classList.add('text-danger');
            }
            if(elProgress) elProgress.innerText = result.data.in_progress;
            if(elResolved) elResolved.innerText = result.data.resolved;
        }
    } catch (error) {
        console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
    }
}

// ฟังก์ชันสำหรับกดรับงานแบบไม่โหลดหน้าใหม่ (AJAX)
async function updateTicketStatus(ticketId, newStatus, buttonElement) {
    // เปลี่ยนปุ่มเป็นสถานะกำลังโหลด
    const originalText = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="bi bi-arrow-repeat spin-anim"></i> กำลังประมวลผล...';
    buttonElement.disabled = true;

    try {
        // ส่งข้อมูลไปที่ API ผ่านวิธี POST
        const response = await fetch('../api/update_ticket_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ticket_id: ticketId,
                status: newStatus
            })
        });

        const result = await response.json();

        if (result.status === 'success') {
            // สำเร็จ! ให้รีเฟรชหน้าเพื่อโชว์ข้อมูลใหม่ (หรือจะเขียน JS อัปเดตตารางเฉยๆ ก็ได้)
            window.location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + result.message);
            buttonElement.innerHTML = originalText;
            buttonElement.disabled = false;
        }
    } catch (error) {
        alert('ระบบเครือข่ายขัดข้อง กรุณาลองใหม่');
        buttonElement.innerHTML = originalText;
        buttonElement.disabled = false;
    }
}

// ตั้งเวลาให้ดึงข้อมูล Dashboard ใหม่ทุกๆ 30 วินาที (Auto-Refresh)
document.addEventListener('DOMContentLoaded', () => {
    // เช็คว่าอยู่หน้า Dashboard ไหม
    if (window.location.pathname.includes('dashboard.php')) {
        setInterval(fetchDashboardStats, 30000); // 30000 ms = 30 วินาที
    }
});

// ฟังก์ชันตรวจจับงานด่วนที่ยังไม่รับเรื่อง (SLA Alert)
async function checkSLAAlerts() {
    // เช็คว่าอยู่หน้า Dashboard ของ IT หรือเปล่า
    if (!window.location.pathname.includes('it_support/dashboard.php')) return;

    try {
        const response = await fetch('../api/get_dashboard_stats.php');
        const result = await response.json();
        
        if (result.status === 'success' && result.data.pending > 0) {
            // แจ้งเตือนแบบ Toast (มุมขวาบน) ไม่กวนสายตา แต่ชัดเจน
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: `แจ้งเตือน! มีงานค้าง ${result.data.pending} งาน`,
                text: 'กรุณารีบตรวจสอบและกดรับงาน',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                background: '#fff3cd',
                color: '#856404'
            });

            // ใส่ CSS กระพริบสีแดงให้ปุ่ม "รอดำเนินการ"
            document.getElementById('stat-pending').classList.add('animate__animated', 'animate__flash', 'text-danger');
        }
    } catch (e) { console.error('SLA Check Error:', e); }
}

// ให้ระบบแอบเช็คงานใหม่ทุกๆ 15 วินาที
setInterval(checkSLAAlerts, 15000);