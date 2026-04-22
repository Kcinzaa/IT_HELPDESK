</div> </div> <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

<script>
$(document).ready(function() {
    // ป้องกัน Error กรณีที่หน้าเว็บนั้นไม่มีตาราง
    if ($('.table').length > 0) {
        $('.table').DataTable({
            language: {
                "sProcessing":   "กำลังดำเนินการ...",
                "sLengthMenu":   "แสดง _MENU_ แถว",
                "sZeroRecords":  "ไม่พบข้อมูล",
                "sInfo":         "แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว",
                "sInfoEmpty":    "แสดง 0 ถึง 0 จาก 0 แถว",
                "sInfoFiltered": "(กรองข้อมูล _MAX_ ทุกแถว)",
                "sSearch":       "🔍 ค้นหาด่วน:",
                "oPaginate": {
                    "sFirst":    "หน้าแรก",
                    "sPrevious": "ก่อนหน้า",
                    "sNext":     "ถัดไป",
                    "sLast":     "หน้าสุดท้าย"
                }
            },
            pageLength: 10,
            order: [[1, 'desc']], // เรียงตามคอลัมน์ที่ 2 จากใหม่ไปเก่า
            stateSave: true // จำหน้าล่าสุดไว้เวลาโดนรีเฟรช
        });
    }
});
</script>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === 'unauthorized') {
        Swal.fire({
            icon: 'error',
            title: 'หยุดก่อน!',
            text: 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ครับ',
            confirmButtonColor: '#0d6efd'
        });
        // ลบ parameter ออกจาก URL เพื่อไม่ให้มันเด้งซ้ำเวลารีเฟรช
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>

<?php if(isset($_SESSION['role']) && in_array($_SESSION['role'], ['it', 'admin'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // กำหนด URL ของ API ที่เราเพิ่งสร้าง (แก้ path ให้ตรงกับโฟลเดอร์โปรเจกต์ของคุณ Nick)
    const apiUrl = '<?php echo BASE_URL; ?>api/check_new_ticket.php';
    
    // ฟังก์ชันสำหรับเช็คงานซ่อมใหม่
    async function checkNewTickets() {
        try {
            // 1. ใช้ Fetch API แอบดึงข้อมูลหลังบ้านแบบเงียบๆ
            let response = await fetch(apiUrl);
            let data = await response.json();
            
            if (data.status === 'success') {
                let currentMaxId = data.max_id;
                
                // 2. ใช้ LocalStorage ของเบราว์เซอร์ เพื่อจำว่าช่างเห็นตั๋วใบที่เท่าไหร่แล้ว
                let savedMaxId = localStorage.getItem('last_ticket_id');
                
                if (savedMaxId === null) {
                    // ถ้าเพิ่งเปิดเว็บครั้งแรก ให้จำค่าล่าสุดไว้ก่อน
                    localStorage.setItem('last_ticket_id', currentMaxId);
                } 
                else if (currentMaxId > parseInt(savedMaxId)) {
                    // 🚨 โป๊ะเชะ! รหัสตั๋วล่าสุด มากกว่ารหัสที่จำไว้ แปลว่า "มีงานเข้าใหม่!" 🚨
                    
                    // อัปเดตความจำ
                    localStorage.setItem('last_ticket_id', currentMaxId);
                    
                    // เล่นเสียงแจ้งเตือน (เตือนสติช่างไอที)
                    let alertSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                    alertSound.play().catch(e => console.log("เบราว์เซอร์บล็อกการเล่นเสียงอัตโนมัติ"));
                    
                    // เด้งแจ้งเตือนมุมขวาบนด้วย SweetAlert2 (Toast Mode)
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: '🔔 งานแจ้งซ่อมด่วนเข้าใหม่!',
                        html: `<b>หัวข้อ:</b> ${data.title}<br><b>จาก:</b> แผนก${data.dept}`,
                        showConfirmButton: false,
                        timer: 8000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                            // กดที่แจ้งเตือนเพื่อพาทะลุไปดูงานได้เลย
                            toast.addEventListener('click', () => {
                                window.location.href = '<?php echo BASE_URL; ?>modules/tickets/ticket_detail.php?id=' + currentMaxId;
                            })
                        }
                    });
                }
            }
        } catch (error) {
            console.error('ระบบเรดาร์ขัดข้อง:', error);
        }
    }

    // 3. สั่งให้ฟังก์ชันนี้ทำงานอัตโนมัติ ทุกๆ 10 วินาที (10,000 มิลลิวินาที)
    setInterval(checkNewTickets, 10000);
});
</script>
<?php endif; ?>
</body>
</html>