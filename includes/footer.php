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

</body>
</html>